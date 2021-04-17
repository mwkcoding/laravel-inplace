<?php

namespace devsrv\inplace;

class InlineEdit {
    public $id;
    public $column;
    public $rules;
    public $authorize = null;
    public $middlewares = null;
    public $applyMiddleware = true;
    public $renderUsingComponent = null;
    public $saveUsingClass = null;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function make(string $id) {
        return new static($id);
    }

    public function column($column) {
        $this->column = $column;
        return $this;
    }

    public function validation($rules) {
        $this->rules = $rules;
        return $this;
    }

    public function authorize($authorize) {
        $this->authorize = $authorize;
        return $this;
    }

    public function middleware($middlewares) {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function withoutMiddleware() {
        $this->applyMiddleware = false;
        return $this;
    }

    public function renderComponent($component) {
        $this->renderUsingComponent = $component;
        return $this;
    }

    public function saveUsing($class) {
        $this->saveUsingClass = $class;
        return $this;
    }
}
