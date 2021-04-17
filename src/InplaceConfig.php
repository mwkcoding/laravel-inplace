<?php

namespace devsrv\inplace;
use devsrv\inplace\{ RelationManager, InlineEdit };

class InplaceConfig {
    public static $config;

    public function __construct($config)
    {
        self::$config = $config;
    }

    public static function getAll() {
        return self::$config;
    }

    public static function getRelationEditor($id) {
        return collect(self::$config['relation'])->first(function (RelationManager $relationManager, $key) use($id) {
            return $relationManager->id === $id;
        });
    }

    public static function getInlineEditor($id) {
        return collect(self::$config['inline'])->first(function (InlineEdit $inlineEditor, $key) use($id) {
            return $inlineEditor->id === $id;
        });
    }
}
