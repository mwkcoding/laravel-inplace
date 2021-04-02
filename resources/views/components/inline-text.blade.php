@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

<div class="editable" x-data="{
    ...inlineEditable(), 
    editedContent: `{{ $value }}`, 
    content: `{{ $value }}`,
    authorize: '{{ $shouldAuthorize === null ? null : ((bool) $shouldAuthorize === true ? 1 : 0) }}',
    model: '{{ $model }}',
    saveusing: '{{ $saveusing }}',
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
        <p x-show="saving" class="spinner-light"></p>

        <span x-ref="lottie-anim"></span>
        <p x-show.transition.out.duration.1000ms="success" class="ml-1">saved</p>
        <p x-show.transition.out.duration.1000ms="error" class="ml-1">failed</p>
    </div>
</div>

@push('inplace.component.script')
@once
<script src="{{ asset('vendor/inplace/resources/js/inline/bundle.js') }}"></script>
@endonce
@endpush
