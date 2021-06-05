<?php

namespace devsrv\inplace;

class Draw {
    public static $builder;
    public static $columnName;

    public static $template;
    public static $mergeQuery;

    public static $renderUsing;

    public static function using($builder, $columnName, $renderUsing = null, $template = null, $mergeQuery = null) {
        self::$builder = $builder;
        self::$columnName = $columnName;
        self::$renderUsing = $renderUsing;
        self::$template = $template;
        self::$mergeQuery = $mergeQuery;

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
        $query = is_callable(self::$mergeQuery) ? call_user_func(self::$mergeQuery, clone self::$builder) : self::$builder;
        $renderQueryResult = $query->get();

        return view(self::$template, ['items' => $renderQueryResult])->render();
    }

    private function renderUsingClosure() {
        return call_user_func(self::$renderUsing, clone self::$builder);
    }

    private function renderDefault() {
        return self::$builder->pluck(self::$columnName)->implode(', ');
    }
}