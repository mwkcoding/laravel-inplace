<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Crypt;
use devsrv\inplace\Traits\{ ModelResolver, ConfigResolver };
use devsrv\inplace\Fields\Text as TextField;
use devsrv\inplace\Exceptions\InplaceException;

class Text extends Component
{
    use ModelResolver, ConfigResolver;

    public $id;
    public $model;
    public $column;
    public $value;
    public $renderAs;
    public $saveUsing;
    public $validation;

    public $csrf_token;
    public $save_route;
    public $icons;

    public $field_sign;

    public function __construct($model, $id = null, $column = null, $value = null, $renderAs = null, $saveUsing = null, $validation = 'required') {
        $field = new TextField(
            $model,
            $id,
            $column, 
            $value,
            $renderAs,
            $saveUsing,
            $validation
        );

        if($id) {
            $this->id = Crypt::encryptString($id);

            $optionsResolver = $field->resolveFromFieldMaker();
            $config = $optionsResolver->getConfigs();
        }
        else {
            throw_if(is_null($column), InplaceException::missing('column name required'));

            $optionsResolver = $field->resolveFromComponentAttribute();
            $config = $optionsResolver->getConfigs();
        }
        
        $this->model = Crypt::encryptString($config['model']);
        $this->value = $value;
        $this->renderAs = $config['render_using'];
        $this->column = Crypt::encryptString($config['column']);
        $this->validation = $config['rules'] ? Crypt::encryptString(serialize($config['rules'])) : null;
        $this->saveUsing = $config['save_using'] ? Crypt::encryptString(serialize($config['save_using'])) : null;

        $this->csrf_token = csrf_token();
        $this->save_route = route('inplace.save');
        $this->icons = config('inplace.icons');

        $this->field_sign = md5($config['model'] . ':' . $config['column']);
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
