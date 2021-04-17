<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Crypt;
use devsrv\inplace\Traits\ResolveModel;

class Inline extends Component
{
    use ResolveModel;

    public $model;
    public $column;
    public $value;
    public $before;
    public $after;
    public $renderAs;
    public $saveusing;
    public $validation;
    public $authorize;

    public $csrf_token;
    public $save_route;

    public function __construct($model = null, $column = null, $value = null, $before = null, $after = null, $renderAs = null, $authorize = null, $saveusing = null, $validation = 'required') {
        $this->model = $model ? Crypt::encryptString($this->resolveModel($model)) : null;
        $this->column = $column ? Crypt::encryptString($column) : null;

        $this->value = $value;
        $this->validation = $validation;
        $this->authorize = $authorize;
        $this->saveusing = $saveusing ? Crypt::encryptString($saveusing) : null;

        $this->renderAs = $renderAs ?? 'inplace-inline-basic-common';

        $this->before = $before;
        $this->after = $after;

        $this->csrf_token = csrf_token();
        $this->save_route = route('inplace.save');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('inplace::components.inline');
    }
}
