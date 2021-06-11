@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/resources/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

@php $value ??= $slot->toHtml(); @endphp

<div class="inplace-container" x-cloak x-data="{
    ...inlineTextEditable(), 
    editedContent: `{{ $value }}`, 
    content: `{{ $value }}`,
    id: '{{ $id }}',
    model: '{{ $model }}',
    column: '{{ $column }}',
    saveusing: '{{ $saveUsing }}',
    rules: {!! str_replace('"', '\'', e(json_encode($validation))) !!}
}" x-init="onBoot($watch)">
    <div class="inplace-editable">
        <div class="inplace-content" @dblclick="initEdit">
            <x-dynamic-component
                :component="$renderAs"
                @input="trackEdit($event)"
                @keydown.escape="handleCancel"
                @keydown.enter.stop.prevent="editedContent && content !== editedContent ? handleSave() : null"
                x-ref="field"
                ::contenteditable="editing"
                x-text="editedContent"
                class="edit-target"
                :prepend="isset($before)? htmlentities(serialize($before)) : null"
                :append="isset($after)? htmlentities(serialize($after)) : null"
                value="$value"
            />
        </div>

        <div class="edit-control" x-show="!saving && !animatingNotify">
            <button @click="initEdit" class="scale-on-hover" x-show="!editing" type="button">
                @if($icons && isset($icons['edit'])) {!! $icons['edit'] !!} @else
                <svg class="inplace-control-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                @endif 
            </button>

            <template x-if="editing">
                <div>
                    <button x-show="editedContent && content !== editedContent" @click="handleSave" type="button">
                        @if($icons && isset($icons['save'])) {!! $icons['save'] !!} @else
                        <svg class="inplace-control-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        @endif 
                    </button>
                    <button @click="handleCancel" type="button">
                        @if($icons && isset($icons['cancel'])) {!! $icons['cancel'] !!} @else
                        <svg class="inplace-control-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif 
                    </button>
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

            <ul x-show="errorMessage.length" class="inplace-error-messages">
                <template x-for="msg in validationErrors" :key="msg">
                    <li x-text="msg"></li>
                </template>
            </ul>
        </div>
    </template>
</div>

@push('inplace.component.script')
@once
<script>
    window._inplace = Object.assign(window._inplace || {}, {
        text: { route: '{{ $save_route }}' },
        csrf_token: '{{ $csrf_token }}'
    });
</script>
<script src="{{ asset('vendor/inplace/resources/assets/js/text/bundle.js') }}"></script>
@endonce
@endpush
