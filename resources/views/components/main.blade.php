@if($attributes->get('saveusing'))
@php 
    $customEditable = <<<'blade'
    <livewire:%component%
        model=""
        :inline="$inline"
        :value="!empty($slot->toHtml()) ? $slot->toHtml() : $value"
        :prepend="isset($before) ? serialize($before) : null"
        :append="isset($after) ? serialize($after) : null"
        :validation="$validation"
        :render-as="$attributes->get('render-as') ?? null"
    />
blade;

    $data = [
        'attributes' => $attributes,
        'inline' => $inline,
        'slot' => $slot,
        'value' => $value ?? null,
        'before' => $before ?? null,
        'after' => $after ?? null,
        'validation' => $validation ?? null,
    ];

    echo (new \devsrv\inplace\RenderBlade(app('view')))
        ->resolveComponent($customEditable, $attributes->get('saveusing'), $data);
@endphp
@else 

<livewire:editable
    model=""
    :inline="$inline"
    :value="!empty($slot->toHtml()) ? $slot->toHtml() : $value"
    :prepend="isset($before) ? serialize($before) : null"
    :append="isset($after) ? serialize($after) : null"
    :validation="$validation"
    :render-as="$attributes->get('render-as') ?? null"
/>

@endif