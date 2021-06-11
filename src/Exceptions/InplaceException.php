<?php

namespace devsrv\inplace\Exceptions;

use InvalidArgumentException;

class InplaceException extends InvalidArgumentException
{
    public static function badFormat($msg = 'bad format')
    {
        return new static($msg);
    }

    public static function missing($msg = 'missing')
    {
        return new static($msg);
    }
}
