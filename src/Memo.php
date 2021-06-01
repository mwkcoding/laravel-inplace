<?php

namespace devsrv\inplace;

class Memo {
    public static $store = [];
    private static $memokey;
    
    public static function key($key) {
        self::$memokey = $key;

        return new self;
    }

    public function getMemoized($item, $global = false) {
        if($global) {
            if(! isset(static::$store['global'][$item])) return false;
            return static::$store['global'][$item];
        }

        if(! isset(static::$store[$item][self::$memokey])) {
            return false;
        }

        return static::$store[$item][self::$memokey];
    }

    public function memoize($key, $value, $global = false) {
        if($global) {
            static::$store['global'][$key] = $value;
            return;
        }

        static::$store[$key][self::$memokey] = $value;
    }
}