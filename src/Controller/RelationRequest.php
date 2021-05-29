<?php

namespace devsrv\inplace\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use devsrv\inplace\Traits\ModelResolver;
use devsrv\inplace\Fields\Relation\Relation;
use devsrv\inplace\Authorize;

class RelationRequest {
    use ModelResolver;

    public $id = null;
    public $model = null;
    public $relationName = null;
    public $authorizeUsing = null;
    public $bypassAuthorize = false;

    public $rules = ['array'];
    public $eachRules = null;
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
            $this->rules = $config['rules'] ?? ['array'];
            $this->eachRules = $config['eachRules'];

            return;
        }

        $this->hydrateRules(request('rules'), request('eachRules'));
        $this->relationName = Crypt::decryptString(request('relationName'));
    }

    private function hydrateRules($rules, $eachRules) {
        if($rules) {
            try {
                $rulesDecrypted = Crypt::decryptString($rules);

                try {
                    $this->rules = unserialize($rulesDecrypted);
                } catch(\Exception $e) {
                    $this->rules = ['array'];
                }
            } catch (DecryptException $e) {
                throw $e;
            }
        }

        if($eachRules) {
            try {
                $rulesDecrypted = Crypt::decryptString($eachRules);

                try {
                    $this->eachRules = unserialize($rulesDecrypted);
                } catch(\Exception $e) {
                    $this->eachRules = null;
                }
            } catch (DecryptException $e) {
                throw $e;
            }
        }
    }

    private function authorize() {
        if(! $this->bypassAuthorize) {
            Authorize::allowed($this->model, $this->authorizeUsing);
        }
    }

    private function validate() {
        $rules = ['values' => $this->rules];

        if($this->eachRules) { $rules['values.*'] = $this->eachRules; }

        Validator::make(['values' => request()->input('values')], $rules)->validate();
    }

    public function save(Request $request) {
        $this->authorize();

        $this->validate();
        
        // db perform fail
        return [
            'success' => 1,
            'message' => "couldn't save data"
        ];
    }
}