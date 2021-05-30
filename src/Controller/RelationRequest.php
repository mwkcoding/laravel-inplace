<?php

namespace devsrv\inplace\Controller;

use Illuminate\Http\Request;
use devsrv\inplace\Authorize;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;
use devsrv\inplace\Traits\ModelResolver;
use Illuminate\Support\Facades\Validator;
use devsrv\inplace\Fields\Relation\Relation;
use Illuminate\Contracts\Encryption\DecryptException;
use devsrv\inplace\Exceptions\CustomEditableException;

class RelationRequest extends Controller {
    use ModelResolver;

    public $id = null;
    public $model = null;
    public $relationName = null;
    public $authorizeUsing = null;
    public $bypassAuthorize = false;
    public $middlewares = [];

    public $rules = ['array'];
    public $eachRules = null;
    public $saveUsing = null;

    public $values = [];

    public function __construct() {
        $this->hydrate();

        $this->middleware($this->middlewares);
    }

    private function resolveFromID() {
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

        if($config['middlewares']) {
            $this->middlewares = is_array($config['middlewares']) ? 
                                array_merge($this->middlewares, $config['middlewares']) : 
                                array_merge($this->middlewares, [$config['middlewares']]);
        }

        $this->saveUsing = $config['save_using'];
    }

    private function hydrate()
    {
        $this->model = $this->decryptModel(request('model'));

        $global_middlewares = config('inplace.middleware');
        if($global_middlewares !== null) $this->middlewares = $global_middlewares;

        if(request()->filled('id')) {
            $this->resolveFromID();

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

        if($this->saveUsing) {
            return $this->customSave($request);
        }

        if($this->model->{$this->relationName}()->sync($request->input('values'))) {
            return [
                'success' => 1,
                'message' => "saved !"
            ];
        }
        
        return [
            'success' => 0,
            'message' => "couldn't save data"
        ];
    }

    protected function customSave(Request $request) {
        $saveUsing = $this->saveUsing;
        
        throw_if(
            ! is_callable($this->saveUsing), CustomEditableException::notCallable(), 'needs to be invokable'
        );
        
        $status = $saveUsing($this->model, $this->relationName, $request->input('values'));

        if(! is_array($status) || ! isset($status['success'])) {
            throw CustomEditableException::badFormat();
        }

        return [
            'success' => $status['success'],
            'message' => (bool) $status['success'] === false ? (isset($status['message'])? $status['message'] : 'Error saving data!') : ''
        ];
    }
}