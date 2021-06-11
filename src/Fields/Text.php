<?php

namespace devsrv\inplace\Fields;
use devsrv\inplace\Contracts\Assemble;
use devsrv\inplace\Traits\{ ModelResolver, ConfigResolver };

class Text implements Assemble {
    use ModelResolver, ConfigResolver;

    public $id;
    public $model;
    public $column;
    public $value;
    public $renderAs;
    public $rules;
    public $saveUsing = null;

    private $modelFormatted;
    private $authorizeUsing = null;
    private $bypassAuthorize = false;
    private $middlewares = null;

    public function __construct(
        $model,
        $id = null,
        $column = null,
        $value = null,
        $renderAs = null,
        $saveUsing = null,
        $validation = 'required'
    )
    {
        $this->model = $model;
        $this->id = $id;
        $this->column = $column;
        $this->value = $value;
        $this->renderAs = $renderAs ?? 'inplace-inline-basic-common';
        $this->saveUsing = $saveUsing;
        $this->rules = $validation;
    }

    public function resolveFromComponentAttribute() 
    {
        $this->modelFormatted = $this->resolveModel($this->model);

        return $this;
    }

    public function resolveFromFieldMaker() 
    {
        $inlineText = self::getConfig('inline', $this->id);
        
        $this->modelFormatted = $this->resolveModel($this->model);
        $this->column = $inlineText->column;
        $this->rules = $inlineText->rules;

        $this->renderAs = $inlineText->renderUsingComponent ?? 'inplace-inline-basic-common';

        $this->authorizeUsing = $inlineText->authorizeUsing;
        $this->bypassAuthorize = $inlineText->bypassAuthorize;
        $this->middlewares = $inlineText->middlewares;

        $this->saveUsing = $inlineText->saveUsingInvokable;

        return $this;
    }

    public function getConfigs() {
        return [
            'model' => $this->modelFormatted,
            'column' => $this->column,
            'rules' => $this->rules, 
            'render_using' => $this->renderAs,
            'authorize_using' => $this->authorizeUsing, 
            'bypass_authorize' => $this->bypassAuthorize, 
            'middlewares' => $this->middlewares, 
            'save_using' => $this->saveUsing, 
        ];
    }
}