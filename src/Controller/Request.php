<?php

namespace devsrv\inplace\Controller;

use Illuminate\Http\Request as HTTPRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use devsrv\inplace\Exceptions\{ ModelException, CustomEditableException };

class Request {
    use AuthorizesRequests;

    public $model = null;
    public $column = null;

    public $rules = 'required';
    public $shouldAuthorize = null;
    public $saveusing = null;

    public $content;

    public function save(HTTPRequest $request) {
        $this->content = $request->content;
        $this->shouldAuthorize = is_null($request->authorize) ? null : (bool) $request->authorize;
        $this->saveusing = $request->saveusing ?? null;

        $this->resolveModel($request->model, $request->column);
        $this->isAuthorized();

        $this->hydrateRules($request->rules);
        $this->validate();

        if($this->saveusing) { return $this->customSave($this->model, $this->column, $this->content); }
        
        if(! $this->model) throw ModelException::missing();

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

    private function resolveModel($model, $column) {
        if($model) {
            try {
                [$modelClass, $colWithKey] = explode(':', $model);
                [$column, $primaryKey] = explode(',', $colWithKey);
            } catch (\Exception $th) {
                throw ModelException::badFormat();
            }
            
            if(! class_exists($modelClass)) throw ModelException::notFound($modelClass);

            $this->model = $modelClass::findOrFail($primaryKey);
            $this->column = $column;
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