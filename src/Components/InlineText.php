<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component;
use devsrv\inplace\Exceptions\ModelException;

class InlineText extends Component
{
    public $model;
    public $value;
    public $prepend;
    public $append;
    public $editedValue = '';
    public $renderAs;
    public $saveusing;
    public $validation;
    public $shouldAuthorize;

    public $csrf_token;
    public $save_route;

    public function __construct($value = null, $model = null, $prepend = null, $append = null, $renderAs = null, $shouldAuthorize = null, $saveusing = null, $validation = 'required') {
        $this->value = $value;
        $this->validation = $validation;
        $this->shouldAuthorize = $shouldAuthorize;
        $this->model = addslashes($model);
        $this->saveusing = addslashes($saveusing);

        // $this->renderAs = $renderAs ?? ( $this->inline ? 'inplace-inline-basic-common' : 'inplace-editable-renderas-common' );
        $this->renderAs = $renderAs ?? 'inplace-inline-basic-common';

        $this->prepend = $prepend ? htmlentities($prepend) : null;
        $this->append = $append ? htmlentities($append) : null;

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
        return view('inplace::components.inline-text');
    }
}
