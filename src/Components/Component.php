<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component as ViewComponent;

class Component extends ViewComponent
{
    public bool $inline = false;
    public $validation;
    public $value;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($inline, $validation = 'required', $value = null)
    {
        $this->inline = $inline;
        $this->validation = $validation;
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('inplace::components.main');
    }
}
