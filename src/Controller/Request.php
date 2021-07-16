<?php

namespace devsrv\inplace\Controller;

use devsrv\inplace\Helper;
use devsrv\inplace\Authorize;
use devsrv\inplace\Fields\Text;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request as HTTPRequest;
use devsrv\inplace\Traits\{ ModelResolver };
use devsrv\inplace\Exceptions\{ ModelException, CustomEditableException };

class Request extends Controller{
    use ModelResolver;

    public $id = null;
    public $model = null;
    public $column = null;

    public $saveUsing = null;

    public $content;

    public $authorizeUsing = null;
    public $bypassAuthorize = false;
    public $middlewares = [];

    public $rules = ['required'];

    public function __construct() {
        if(!App::runningInConsole()) {
            $this->hydrate();
        }

        $this->middleware($this->middlewares);
    }

    private function hydrate()
    {
        $this->model = $this->decryptModel(request('model'));
        $this->content = request('content');

        $global_middlewares = config('inplace.middleware');
        if($global_middlewares !== null) $this->middlewares = $global_middlewares;

        if(request()->filled('id')) {
            $this->resolveFromID();

            return;
        }

        $this->resolveFromAttributes();
    }

    private function resolveFromID() {
        $this->id = Helper::decrypt(request('id'));

        $config = (new Text($this->model, $this->id ))->resolveFromFieldMaker()->getConfigs();

        $this->column = $config['column'];

        $this->authorizeUsing = $config['authorize_using'];
        $this->bypassAuthorize = $config['bypass_authorize'];
        $this->rules = $config['rules'] ?? ['required'];

        if($config['middlewares']) {
            $suppliedMiddlewares = $config['middlewares'];

            $this->middlewares = is_array($suppliedMiddlewares) ? 
                                array_merge($this->middlewares, $suppliedMiddlewares) : 
                                array_merge($this->middlewares, [$suppliedMiddlewares]);
        }

        $this->saveUsing = $config['save_using'];
    }

    private function resolveFromAttributes() {
        if(request()->filled('rules')) {
            $rulesDecrypted = Helper::decrypt(request('rules'));

            try {
                $this->rules = unserialize($rulesDecrypted);
            } catch(\Exception $e) {
                $this->rules = ['required'];
            }
        }

        $this->column = Helper::decrypt(request('column'));
        $this->saveUsing = request()->filled('saveusing') ? unserialize(Helper::decrypt(request('saveusing'))) : null;
    }

    private function authorize() {
        if(! $this->bypassAuthorize) {
            Authorize::allowed($this->model, $this->authorizeUsing);
        }
    }

    public function save(HTTPRequest $request) {
        $this->authorize();

        $this->validate();

        if($this->saveUsing) { return $this->customSave(); }
        
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

    private function validate() {
        Validator::make(['content' => $this->content], [
            'content' => $this->rules,
        ])->validate();
    }

    protected function customSave() {
        $saveUsing = $this->saveUsing;
        
        throw_if(
            ! is_callable($this->saveUsing), CustomEditableException::notCallable(), 'needs to be invokable'
        );
        
        $status = $saveUsing($this->model, $this->column, $this->content);

        if(! is_array($status) || ! isset($status['success'])) {
            throw CustomEditableException::badFormat();
        }

        $success = (bool) $status['success'];

        if($success) $this->value = $this->content;

        return [
            'success' => $success,
            'message' => ! $success ? (isset($status['message'])? $status['message'] : 'Error saving data!') : '',
        ];
    }
}