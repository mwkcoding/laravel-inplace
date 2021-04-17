@push('inplace.component.style')
@once
<link rel="stylesheet" href="{{ asset('vendor/inplace/resources/assets/css/loader/loader.min.css') }}" />
@endonce
@endpush

{!! $renderValue !!}

@if(isset($before)) {!! $before->toHtml() !!} @endif
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
@if(isset($after)) {!! $after->toHtml() !!} @endif

@push('inplace.component.script')
@once

@endonce
@endpush
