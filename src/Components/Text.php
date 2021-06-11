<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Crypt;
use devsrv\inplace\Traits\{ ModelResolver, ConfigResolver };

class Text extends Component
{
    use ModelResolver, ConfigResolver;

    public $id;
    public $model;
    public $column;
    public $value;
    public $renderAs;
    public $saveusing;
    public $validation;
    public $authorize;

    public $csrf_token;
    public $save_route;
    public $icons;

    public function __construct($model, $id = null, $column = null, $value = null, $renderAs = null, $authorize = null, $saveusing = null, $validation = 'required') {
        if($id) {
            $this->id = Crypt::encryptString($id);

            $this->resolveConfigUsingID($id, $model);
        }
        else {
            $this->resolveConfigUsingAttributes($column, $renderAs, $authorize, $saveusing, $validation);
        }
        
        $this->model = Crypt::encryptString($this->resolveModel($model));
        $this->value = $value;

        $this->csrf_token = csrf_token();
        $this->save_route = route('inplace.save');

        $this->icons = config('inplace.icons');
    }

    private function resolveConfigUsingID(string $id, $model) {
        $inlineEditor = self::getConfig('inline', $id);

        $this->renderAs = $inlineEditor->renderUsingComponent ?? 'inplace-inline-basic-common';
    }

    private function resolveConfigUsingAttributes($column, $renderAs, $authorize, $saveusing, $validation) {
        $this->column = $column ? Crypt::encryptString($column) : null;
        $this->validation = $validation;
        $this->authorize = $authorize;
        $this->saveusing = $saveusing ? Crypt::encryptString($saveusing) : null;
        $this->renderAs = $renderAs ?? 'inplace-inline-basic-common';
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('inplace::components.text');
    }
}
