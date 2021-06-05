<?php

namespace devsrv\inplace;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class Draw {
    public static $valuesCollection;
    public static $columnName;

    public static $template;

    public static $renderUsing;

    public static function using(EloquentCollection $valuesCollection, $columnName, $renderUsing = null, $template = null) {
        self::$valuesCollection = $valuesCollection;
        self::$columnName = $columnName;
        self::$renderUsing = $renderUsing;
        self::$template = $template;

        return new self;
    }

    public static function usingPayload($modelsCollection, $columnName, $template = null) {
        if($template) {
            return view($template, ['items' => $modelsCollection])->render();
        }

        return $modelsCollection->pluck($columnName)->implode(', ');
    }

    public function getRendered() {
        if(self::$template) return $this->renderUsingPartial();

        if(self::$renderUsing && is_callable(self::$renderUsing)) return $this->renderUsingClosure();

        return $this->renderDefault();
    }

    private function renderUsingPartial() {
        return view(self::$template, ['items' => self::$valuesCollection])->render();
    }

    private function renderUsingClosure() {
        return call_user_func(self::$renderUsing, clone self::$valuesCollection);
    }

    private function renderDefault() {
        return self::$valuesCollection->pluck(self::$columnName)->implode(', ');
    }
}