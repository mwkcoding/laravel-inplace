<?php

namespace devsrv\inplace\Components\Misc;

use Illuminate\View\Component;

class InlineBasicCommon extends Component
{
    public $value;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('inplace::components.misc.inline-basic-common');
    }
}
