<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component as ViewComponent;

class Component extends ViewComponent
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
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
