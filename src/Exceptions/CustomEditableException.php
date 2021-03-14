<?php

namespace devsrv\inplace\Exceptions;

use InvalidArgumentException;

class CustomEditableException extends InvalidArgumentException
{
    public static function badFormat()
    {
        return new static("Invalid response, expected array with required parameter - (bool) success");
    }

    public static function notFound(string $path)
    {
        return new static("Class `{$path}` not found");
    }

    public static function missing()
    {
        return new static("Custom editable `save` method not callable or missing");
    }
}
