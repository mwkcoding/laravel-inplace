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

class Request extends Controller{
    use AuthorizesRequests, ConfigResolver, ModelResolver;

    public $id = null;
    public $model = null;
    public $column = null;
    public $inlineEditor = null;

    public $rules = 'required';
    public $allowed = null;
    public $saveusing = null;

    public $content;

    public function __construct() {
        $this->hydrate();

        $this->applyMiddleware();
    }

    private function hydrate()
    {
        $this->content = request('content');
        $this->resolveModelColumn(request('model'), request('column'));

        if(request()->filled('id')) {
            try {
                $id = Crypt::decryptString(request('id'));
            } catch (DecryptException $e) {
                throw $e;
            }

            $this->inlineEditor = self::getConfig('inline', $id);
            $this->column = $this->inlineEditor->column;
            $this->saveusing = $this->inlineEditor->saveUsingClass;
            $this->rules = $this->inlineEditor->rules;
            $this->allowed = $this->inlineEditor->authorize;

            return;
        }

        $this->allowed = request()->filled('authorize') ? (bool) request('authorize') : null;
        $this->hydrateSaveUsing(request('saveusing'));
        $this->hydrateRules(request('rules'));
    }

    private function applyMiddleware() 
    {
        // if has field config
        if($this->inlineEditor) {
            if(! $this->inlineEditor->applyMiddleware) return;

            if($this->inlineEditor->middlewares) {
                $this->middleware($this->inlineEditor->middlewares);
                return;
            }
        }

        // fallback to default i.e. apply any global config middlewares
        $middlewares = config('inplace.middleware');
        if($middlewares !== null) $this->middleware($middlewares);
    }

    public function save(HTTPRequest $request) {
        $this->isAuthorized();

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

    private function resolveModelColumn($model_encrypted, $column_encrypted) {
        if($model_encrypted) {
            $this->model = $this->decryptModel($model_encrypted);
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
        if($this->allowed !== null) {
            abort_unless($this->allowed, 403, 'unauthorized');
            return;
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