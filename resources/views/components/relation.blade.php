@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/resources/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

<ul>
    @foreach ($options as $option)
        <li>
            @if($thumbnailed && method_exists($option, 'inplaceThumb'))
                <img src="{{ $option->inplaceThumb() }}" width="{{ $attributes->has('thumbnail-width') ? $attributes->get('thumbnail-width') : '30' }}" alt="avatar" />
            @endif

            <input type="checkbox" name="" value="{{ $option->getAttributeValue($relationPrimaryKey) }}" id="" {{ isset($currentValues) && in_array($option->getAttributeValue($relationPrimaryKey), $currentValues) ? 'checked' : '' }} />
            {{ $option->getAttributeValue($relationColumn) }}
        </li>
    @endforeach
</ul>

@push('inplace.component.script')
@once

@endonce
@endpush
