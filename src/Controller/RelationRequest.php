<?php

namespace devsrv\inplace\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Routing\Controller;
use devsrv\inplace\Traits\ModelResolver;
use devsrv\inplace\Fields\Relation\Relation;
use devsrv\inplace\Authorize;

class RelationRequest extends Controller {
    use ModelResolver;

    public $id = null;
    public $model = null;
    public $relationName = null;
    public $authorizeUsing = null;
    public $bypassAuthorize = false;

    // public $rules = 'required';
    // public $saveusing = null;

    public $values = [];

    public function __construct() {
        $this->hydrate();
    }

    private function hydrate()
    {
        $this->model = $this->decryptModel(request('model'));

        if(request()->filled('id')) {
            try {
                $id = Crypt::decryptString(request('id'));
            } catch (DecryptException $e) {
                throw $e;
            }

            $config = (new Relation($this->model, $id))->resolveFromFieldMaker()->getValues();

            $this->relationName = $config['relation_name'];

            $this->authorizeUsing = $config['authorize_using'];
            $this->bypassAuthorize = $config['bypass_authorize'];

            return;
        }

        try {
            $this->relationName = Crypt::decryptString(request('relationName'));
        } catch (DecryptException $e) {
            throw $e;
        }
    }

    public function save(Request $request) {
        if(! $this->bypassAuthorize) {
            Authorize::allowed($this->model, $this->authorizeUsing);
        }

        // db perform fail
        return [
            'success' => 1,
            'message' => "couldn't save data"
        ];
    }
}