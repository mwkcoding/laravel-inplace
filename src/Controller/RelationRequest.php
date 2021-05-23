<?php

namespace devsrv\inplace\Controller;

use Illuminate\Http\Request as HTTPRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use devsrv\inplace\Exceptions\{ ModelException, CustomEditableException };
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Routing\Controller;
use devsrv\inplace\Traits\{ ConfigResolver, ModelResolver };

class RelationRequest extends Controller {
    use AuthorizesRequests, ConfigResolver, ModelResolver;

    public $id = null;
    public $model = null;
    public $relationName = null;

    public $rules = 'required';
    public $saveusing = null;

    public $values = [];

    public function __construct() {
        
    }

    public function save(HTTPRequest $request) {

        // db perform fail
        return [
            'success' => 0,
            'message' => "couldn't save data"
        ];
    }
}