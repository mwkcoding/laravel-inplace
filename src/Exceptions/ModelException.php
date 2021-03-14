<?php

namespace devsrv\inplace\Exceptions;

use InvalidArgumentException;

class ModelException extends InvalidArgumentException
{
    public static function badFormat()
    {
        return new static("Incorrect model attribute format, expected namespace\Model:column,key");
    }

    public static function notFound(string $path)
    {
        return new static("Model `{$path}` not found");
    }

    public static function missing()
    {
        return new static("No model to update");
    }
}
