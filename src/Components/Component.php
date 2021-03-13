<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component as ViewComponent;

class Component extends ViewComponent
{
    public bool $inline = false;
    public $value;
    public $validation = 'required';

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($inline, $value, $validation)
    {
        $this->inline = $inline;
        $this->value = $value;
        $this->validation = $validation;
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
