<?php

namespace devsrv\inplace;
use devsrv\inplace\{ RelationManager, InlineText };

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

    public static function getInlineTextEditor($id) {
        return collect(self::$config['text'])->first(function (InlineText $inlineEditor, $key) use($id) {
            return $inlineEditor->id === $id;
        });
    }
}
