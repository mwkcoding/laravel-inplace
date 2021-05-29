<?php

namespace devsrv\inplace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Authorize {
    public static function allowed(Model $model, $authorizeUsing = null) {
        abort_unless(auth()->check(), 403, 'require to be logged in');

        if(is_callable($authorizeUsing)) {
            abort_unless($authorizeUsing(), 403, 'unauthorized');
            return;
        }

        $response = Gate::inspect('update', $model);
        abort_unless($response->allowed(), 403, $response->message());
    }
}