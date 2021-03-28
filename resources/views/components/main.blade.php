@if ($inline)
    <x-inplace-inline-text
        :model="$model"
        :validation="$validation"
        :shouldAuthorize="$authorize"
        :value="!empty($slot->toHtml()) ? $slot->toHtml() : $value"
        :prepend="isset($before) ? serialize($before) : null"
        :append="isset($after) ? serialize($after) : null"
        :render-as="$attributes->get('render-as') ?? null"
        :saveusing="$attributes->get('saveusing') ?? null"
    />
@else
    non-inline
@endif