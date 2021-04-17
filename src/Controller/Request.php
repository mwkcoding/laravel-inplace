<?php

namespace devsrv\inplace\Controller;

use Illuminate\Http\Request as HTTPRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use devsrv\inplace\Exceptions\{ ModelException, CustomEditableException };
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Routing\Controller;

class Request extends Controller{
    use AuthorizesRequests;

    public $model = null;
    public $column = null;

    public $rules = 'required';
    public $shouldAuthorize = null;
    public $saveusing = null;

    public $content;

    public function __construct() {
        $middlewares = config('inplace.middleware');
        if($middlewares !== null) $this->middleware($middlewares);
    }

    public function save(HTTPRequest $request) {
        $this->content = $request->content;
        $this->shouldAuthorize = is_null($request->authorize) ? null : (bool) $request->authorize;

        $this->hydrateSaveUsing($request->saveusing);

        $this->resolveModel($request->model, $request->column);
        $this->isAuthorized();

        $this->hydrateRules($request->rules);
        $this->validate();

        if($this->saveusing) { return $this->customSave($this->model, $this->column, $this->content); }
        
        if(! $this->model || ! $this->column) throw ModelException::missing();

        // db save success
        $this->model->{$this->column} = $this->content;
        if($this->model->save()) {
            return [
                'success' => 1
            ];
        }

        // db perform fail
        return [
            'success' => 0,
            'message' => "couldn't save data"
        ];
    }

    private function resolveModel($model_encrypted, $column_encrypted) {
        if($model_encrypted) {
            try {
                $model = Crypt::decryptString($model_encrypted);
            } catch (DecryptException $e) {
                throw $e;
            }

            try {
                [$modelClass, $primaryKeyValue] = explode(':', $model);
            } catch (\Exception $th) {
                throw ModelException::badFormat('namespace\Model:key');
            }
            
            if(! class_exists($modelClass)) throw ModelException::notFound($modelClass);

            $this->model = $modelClass::findOrFail($primaryKeyValue);
        }

        if($column_encrypted) {
            try {
                $this->column = Crypt::decryptString($column_encrypted);
            } catch (DecryptException $e) {
                throw $e;
            }
        }
    }

    private function hydrateRules($rules) {
        if($rules) {
            try {
                $this->rules = unserialize($rules);
            } catch(\Exception $e) {
                // dump($e->getMessage());

                $this->rules = $rules;
            }
        }
    }

    private function validate() {
        Validator::make(['content' => $this->content], [
            'content' => $this->rules,
        ])->validate();
    }

    protected function isAuthorized() {
        if($this->shouldAuthorize !== null) {
            if($this->shouldAuthorize && $this->model) { $this->authorize('update', $this->model); }
            else return true;
        }

        $globalAuthorize = config('inplace.authorize');

        if($globalAuthorize !== null && $globalAuthorize && $this->model) $this->authorize('update', $this->model);
    }

    private function hydrateSaveUsing($saveusing) {
        if($saveusing) {
            try {
                $this->saveusing = Crypt::decryptString($saveusing);
            } catch (DecryptException $e) {
                throw $e;
            }
        }
    }

    protected function customSave($model, $column, $editedValue) {
        if(! class_exists($this->saveusing)) { throw CustomEditableException::notFound($this->saveusing); }

        $saveAs = new $this->saveusing;
        
        if(! is_callable([$saveAs, 'save'])) throw CustomEditableException::missing();

        $status = ($saveAs)->save($model, $column, $editedValue);

        if(! is_array($status) || !isset($status['success'])) {
            throw CustomEditableException::badFormat();
        }

        if($status['success']) {
            $this->value = $editedValue;
            return [
                'success' => 1
            ];
        }

        return [
            'success' => 0,
            'message' => isset($status['message'])? $status['message'] : 'Error saving data!'
        ];
    }
}