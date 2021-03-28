<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component as ViewComponent;

class Component extends ViewComponent
{
    public bool $inline = false;
    public $authorize;
    public $model;
    public $validation;
    public $value;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($inline, $model = null, $authorize = null, $validation = null, $value = null)
    {
        $this->inline = $inline;
        $this->model = $model;
        $this->validation = $validation;
        $this->value = $value;
        $this->authorize = $authorize;
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
