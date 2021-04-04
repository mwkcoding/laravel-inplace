@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/resources/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

<div class="inplace-container" x-cloak x-data="{
    ...inlineEditable(), 
    editedContent: `{{ $value }}`, 
    content: `{{ $value }}`,
    authorize: '{{ $shouldAuthorize === null ? null : ((bool) $shouldAuthorize === true ? 1 : 0) }}',
    model: '{{ $model }}',
    saveusing: '{{ $saveusing }}',
    rules: {!! str_replace('"', '\'', e(json_encode($validation))) !!}
}" x-init="onBoot($watch)">
    <div class="editable">
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
        </div>

        <div class="edit-control" x-show="!saving && !animatingNotify">
            <button @click="initEdit" x-show="!editing" type="button">edit</button>

            <template x-if="editing">
                <div>
                    <button x-show="editedContent && content !== editedContent" @click="handleSave" type="button">save</button>
                    <button @click="handleCancel" type="button">close</button>
                </div>
            </template>
        </div>

        <div class="status">
            <div x-show="saving" class="spinner-light"></div>

            <div x-ref="lottie-anim" class="lottie-box"></div>
            {{-- <span x-show.transition.out.duration.1000ms="success" class="ml-1">saved</span> --}}
            {{-- <span x-show.transition.out.duration.1000ms="error" class="ml-1">failed</span> --}}
        </div>
    </div>

    <template x-if="errorMessage">
        <div x-ref="this" class="inplace-errors-area">
            <p class="inplace-error-main">
                <span x-text="errorMessage"></span> <button type="button" @click="if($refs.this) $refs.this.remove()">X</button>
            </p>

            <ul x-show="errorMessage.length" class="error-messages">
                <template x-for="msg in validationErrors" :key="msg">
                    <li x-text="msg"></li>
                </template>
            </ul>
        </div>
    </template>
</div>

@push('inplace.component.script')
@once
<script>window._inplace = window._inplace || {};_inplace = {route: '{{ $save_route }}', csrf_token: '{{ $csrf_token }}'};</script>
<script src="{{ asset('vendor/inplace/resources/assets/js/inline/bundle.js') }}"></script>
@endonce
@endpush
