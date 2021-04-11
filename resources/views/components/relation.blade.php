@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/resources/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

{!! $print !!}

<ul>
    @foreach ($options as $option)
        <li>
            @if($thumbnailed)
                <img src="{{ $resolveThumbnail($option) }}" width="{{ $thumbnailWidth }}" alt="avatar" />
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
