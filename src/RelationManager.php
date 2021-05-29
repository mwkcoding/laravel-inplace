<?php

namespace devsrv\inplace;

class RelationManager {
    public $id;
    public $relationName;
    public $column;
    public $bypassAuthorize = false;
    public $authorizeUsing = null;
    public $renderPartial;
    public $renderQuery;
    public $rules;
    public $eachItemRules;
    public $middlewares = null;
    public $renderUsing = null;
    public $thumbnail = false;
    public $thumbnailWidth = 30;
    public $multiple = true;
    public $resolveThumbnailUsing = null;
    public $filterOptionsQuery;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function make(string $id) {
        return new static($id);
    }

    public function relation($relationName, $column) {
        $this->relationName = $relationName;
        $this->column = $column;
        return $this;
    }

    public function filterOptions(callable $filter) {
        $this->filterOptionsQuery = $filter;
        return $this;
    }

    public function renderTemplate(string $partial, $withQuery = null) {
        $this->renderPartial = $partial;
        $this->renderQuery = $withQuery;
        return $this;
    }

    public function renderUsing($renderUsing) {
        $this->renderUsing = $renderUsing;
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

    public function validation($rules) {
        $this->rules = $rules;
        return $this;
    }

    public function validateEach($rules) {
        $this->eachItemRules = $rules;
        return $this;
    }

    public function middleware($middlewares) {
        $this->middlewares = $middlewares;
        return $this;
    }

    public function multiple($multiple = true) {
        $this->multiple = $multiple;
        return $this;
    }

    public function thumbnailed($width = 30, $reolveUsing = null) {
        $this->thumbnail = true;
        $this->thumbnailWidth = $width;
        $this->resolveThumbnailUsing = $reolveUsing;
        return $this;
    }
}
