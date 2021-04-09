<?php

namespace devsrv\inplace\Exceptions;

use InvalidArgumentException;

class RelationException extends InvalidArgumentException
{
    public static function notFound(string $model, string $name)
    {
        return new static("Relation `{$name}` not found on Model `{$model}`");
    }

    public static function notSupported(string $name)
    {
        return new static("Relation `{$name}` not supported for inplace");
    }
}
