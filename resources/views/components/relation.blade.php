@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/resources/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

<div>
    <div class="_inplace-field-control"
    data-inplace-field-conf='{
        "contentId": "_inplace-content:{{ $field_id }}",
        "hash": "{{ $hash }}",
        "signature": "{{ $field_sign }}",
        "id": "{{ $id }}",
        "model": "{{ $model }}",
        "relationName": "{{ $relationName }}",
        "relColumn": "{{ $relationColumn }}",
        "renderTemplate": "{{ $renderTemplate }}",
        "renderField": "relation.BasicCheckbox",
        "rules": "{{ $validation }}",
        "eachRules": "{{ $validateEach }}",
        "thumbnailed": {{ (bool) $thumbnailed ? 'true' : 'false' }},
        "thumbnailWidth": "{{ $thumbnailWidth }}",
        "currentValues": @json($currentValues),
        "multiple": {{ (bool) $multiple ? 'true' : 'false' }}
    }'>
        {!! $renderValue !!}
    </div>
</div>

@push('inplace.component.script')
@once
<script>
    window._inplace = Object.assign(window._inplace || {}, {
        relation: { route: '{{ $save_route }}' },
        csrf_token: '{{ $csrf_token }}',
        options: {relation: []}
    });
</script>
<script src="{{ asset('vendor/inplace/resources/assets/js/relation/bundle.js') }}"></script>
@endonce

<script>
    if(window._inplace.options.relation.findIndex(opt => opt.id === '{{ $hash }}') === -1) {
        window._inplace.options = {
            ...window._inplace.options,
            relation: [
                ...window._inplace.options.relation, 
                { id: '{{ $hash }}', options: @json($options) }
            ]
        };
    }
</script>
@endpush
