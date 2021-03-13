@if($attributes->get('prepend')){!! unserialize( html_entity_decode($attributes->get('prepend')) )->toHtml() !!}@endif

<span {{ $attributes->merge(['class' => 'editable-inline-default']) }} {{ $attributes }}>{{ $value }}</span>

@if($attributes->get('append')){!! unserialize( html_entity_decode($attributes->get('append')) )->toHtml() !!}@endif
