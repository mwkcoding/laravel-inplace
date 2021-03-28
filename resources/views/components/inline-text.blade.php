<div class="editable" x-data="{
    ...inlineEditable(), 
    editedContent: `{{ $value }}`, 
    content: `{{ $value }}`,
    authorize: '{{ $shouldAuthorize === null ? null : ((bool) $shouldAuthorize === true ? 1 : 0) }}',
    model: '{{ $model }}',
    rules: {!! str_replace('"', '\'', e(json_encode($validation))) !!}
}" x-init="onBoot($watch)">
    <div class="content">
        <x-dynamic-component
            :component="$renderAs"
            @input="trackEdit($event)"
            @keydown.escape="handleCancel"
            @keydown.enter.stop.prevent="editedContent && content !== editedContent ? handleSave() : null"
            x-ref="field"
            ::contenteditable="editing"
            x-text="editedContent"
            class="edit-target"
            :prepend="$prepend"
            :append="$append"
            value="$value"
        />

        {{-- @error('editedValue')
            <span class="error" x-data="{open: true}" x-show="open" x-ref="this">
                {{ $message }} <button type="button" @click="if($refs.this) $refs.this.remove()">X</button>
            </span>
        @enderror --}}
    </div>

    <div class="edit-control" x-show="!saving">
        <button @click="initEdit" x-show="!editing" type="button">edit</button>

        <template x-if="editing">
            <div>
                <button x-show="editedContent && content !== editedContent" @click="handleSave" type="button">save</button>
                <button @click="handleCancel" type="button">close</button>
            </div>
        </template>
    </div>

    <div class="status">
        <p wire:loading wire:target="save">saving . . .</p>

        <p x-show.transition.out.duration.1000ms="success">success</p>
        <p x-show.transition.out.duration.1000ms="error">failed</p>
    </div>
</div>

@push('inplace.component.script')
@once
<script>
    function inlineEditable() {
        return {
            editing: false,
            saving: false,
            error: false,
            success: false,
            onBoot(watch) {
                watch('error', has => {
                    if(has) {
                        window.dispatchEvent(new CustomEvent("inline-editable-finish", {
                            detail: { hasError: true }
                        }));

                        setTimeout(() => this.error = false, 1500);
                    }
                });

                watch('success', has => {
                    if(has) {
                        window.dispatchEvent(new CustomEvent("inline-editable-finish", {
                            detail: { hasSuccess: true }
                        }));

                        setTimeout(() => this.success = false, 1500);
                    }
                });
            },
            initEdit() {
                this.editing = true;

                this.$nextTick(() => {
                    this.$refs.field.focus();
                    const range = document.createRange();
                    const sel = window.getSelection();

                    range.setStart(this.$refs.field, 1);
                    range.collapse(true);

                    sel.removeAllRanges();
                    sel.addRange(range);
                });
            },
            handleSave() {
                this.editing = false;
                this.saving = true;

                fetch("{{ $save_route }}", {
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-Token": "{{ $csrf_token }}"
                    },
                    method: 'POST',
                    credentials: "same-origin",
                    body: JSON.stringify({
                        content: this.editedContent,
                        authorize: this.authorize,
                        model: this.model,
                        rules: this.rules,
                    })
                })
                .then(res => res.json())
                .then(result => {
                    this.saving = false;
                    this.editing = false;

                    if(Object.prototype.hasOwnProperty.call(result, 'success') && Number(result.success) === 1) {
                        this.success = true;
                        return;
                    }

                    this.error = true;
                    this.editedContent = this.content;
                    return;

                    console.log(result);
                })
                .catch(err => {
                    this.saving = false;
                    this.editing = false;
                    this.error = true;
                    this.editedContent = this.content;
                });
            },
            handleCancel() {
                this.editing = false;
                this.editedContent = this.content;
            },
            trackEdit(ev) {
                this.editedContent = ev.target.innerText;
            }
        }
    }
</script>
@endonce
@endpush
