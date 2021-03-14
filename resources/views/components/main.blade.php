<livewire:editable
    model=""
    :inline="$inline"
    :value="!empty($slot->toHtml()) ? $slot->toHtml() : $value"
    :prepend="isset($before) ? serialize($before) : null"
    :append="isset($after) ? serialize($after) : null"
    :validation="$validation"
    :render-as="$attributes->get('render-as') ?? null"
    :saveusing="$attributes->get('saveusing') ?? null"
/>