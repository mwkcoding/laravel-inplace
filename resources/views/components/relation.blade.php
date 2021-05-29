@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/resources/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

<div>

    {!! $renderValue !!}

    @if(isset($before)) {!! $before->toHtml() !!} @endif

    <div id="{{ $field_id }}"></div>
        
    @if(isset($after)) {!! $after->toHtml() !!} @endif

</div>

@push('inplace.component.script')
@once
<script>
    window._inplace = Object.assign(window._inplace || {}, {
        relation: { route: '{{ $save_route }}' },
        csrf_token: '{{ $csrf_token }}'
    });
</script>
<script src="{{ asset('vendor/inplace/resources/assets/js/relation/bundle.js') }}"></script>
@endonce

<script>
    (function() {
        drawRelationEditable('{{ $field_id }}', {
            id: '{{ $id }}',
            model: '{{ $model }}',
            relationName: '{{ $relationName }}',
            rules: '{{ $validation }}',
            options: @json($options),
            thumbnailed: '{{ (bool) $thumbnailed }}',
            thumbnailWidth: '{{ $thumbnailWidth }}',
            currentValues: @json($currentValues),
            multiple: '{{ (bool) $multiple }}'
        });
    })();
</script>
@endpush
