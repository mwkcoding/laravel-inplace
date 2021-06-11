<?php

namespace devsrv\inplace;

class InlineText {
    public $id;
    public $column;
    public $rules;

    public $bypassAuthorize = false;
    public $authorizeUsing = null;

    public $middlewares = null;
    public $renderUsingComponent = null;
    public $saveUsingInvokable = null;

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

    public function authorizeUsing($callback) {
        $this->authorizeUsing = $callback;
        return $this;
    }

    public function bypassAuthorize() {
        $this->bypassAuthorize = true;
        return $this;
    }

    public function middleware($middlewares) {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function renderComponent($component) {
        $this->renderUsingComponent = $component;
        return $this;
    }

    public function saveUsing($class) {
        $this->saveUsingInvokable = $class;
        return $this;
    }
}
