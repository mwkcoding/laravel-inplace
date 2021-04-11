<?php

namespace devsrv\inplace\Exceptions;

use InvalidArgumentException;

class ConfigException extends InvalidArgumentException
{
    public static function missing(string $id)
    {
        return new static("Config missing for `{$id}`");
    }

    public static function notFound()
    {
        return new static("inplace config provider missing");
    }
}
