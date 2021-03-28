<?php

namespace devsrv\inplace\Controller;

use Illuminate\Http\Request as HTTPRequest;
use devsrv\inplace\Exceptions\ModelException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;

class Request {
    use AuthorizesRequests;

    public $model = null;
    public $column = null;

    public $rules = 'required';
    public $shouldAuthorize = null;

    public $content;

    public function save(HTTPRequest $request) {
        $this->content = $request->content;
        $this->shouldAuthorize = is_null($request->authorize) ? null : (bool) $request->authorize;

        $this->resolveModel($request->model, $request->column);
        $this->isAuthorized();

        $this->hydrateRules($request->rules);
        $this->validate();

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
}