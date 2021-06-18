@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/resources/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

<div>

    <div class="d-flex justify-content-between">
        <div id="_inplace-content:{{ $field_id }}">{!! $renderValue !!}</div>

            <div>
                <button 
            type="button" 
            onclick="drawRelationEditable(this)"
            data-inplace-field-conf='{
                "fieldId" : "{{ $field_id }}",
                "contentId": "_inplace-content:{{ $field_id }}",
                "hash": "{{ $hash }}",
                "id": "{{ $id }}",
                "model": "{{ $model }}",
                "relationName": "{{ $relationName }}",
                "relColumn": "{{ $relationColumn }}",
                "renderTemplate": "{{ $renderTemplate }}",
                "rules": "{{ $validation }}",
                "eachRules": "{{ $validateEach }}",
                "thumbnailed": "{{ (bool) $thumbnailed }}",
                "thumbnailWidth": "{{ $thumbnailWidth }}",
                "currentValues": @json($currentValues),
                "multiple": "{{ (bool) $multiple }}"
            }'
        >edit</button>

                <button type="button" onclick="eraseRelationEditable('{{ $field_id }}')">cancel</button>
        </div>
    </div>

    @if(isset($before)) {!! $before->toHtml() !!} @endif

    <div id="{{ $field_id }}"></div>
        
    @if(isset($after)) {!! $after->toHtml() !!} @endif

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
