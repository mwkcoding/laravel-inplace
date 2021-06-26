<?php

namespace devsrv\inplace;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;

class RateLimiter {
    public $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function __call($name, $arguments)
    {
        RateLimiterFacade::for($this->key, function (Request $request) use ($name, $arguments) {
            return Limit::{$name}(...$arguments)->by(
                $request->filled('inplace_field_sign') ?
                    $request->inplace_field_sign : (
                        optional($request->user())->id ?: $request->ip()
                    )
            )->response(function ($rqst, $rateLimitedHeaders) {
                return response(['message' => 'too many requests for this field'], 429)
                        ->withHeaders($rateLimitedHeaders)
                        ->header('Content-Type', 'application/json');
            });
        });
    }

    public static function for(string $key) {
        return new static($key);
    }
}
