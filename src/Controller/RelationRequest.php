<?php

namespace devsrv\inplace\Controller;

use devsrv\inplace\Draw;
use devsrv\inplace\Helper;
use Illuminate\Http\Request;
use devsrv\inplace\Authorize;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use devsrv\inplace\Traits\ModelResolver;
use Illuminate\Support\Facades\Validator;
use devsrv\inplace\Fields\Relation\Relation;
use devsrv\inplace\Exceptions\CustomEditableException;

class RelationRequest extends Controller {
    use ModelResolver;

    public $id = null;
    public $model = null;
    public $relationName = null;
    public $relationColumn = null;
    public $renderTemplate = null;
    public $renderUsing = null;

    public $authorizeUsing = null;
    public $bypassAuthorize = false;
    public $middlewares = [];

    public $rules = ['array'];
    public $eachRules = null;
    public $saveUsing = null;

    public $values = [];

    public function __construct() {
        if(!App::runningInConsole()) {
            $this->hydrate();
        }

        $this->middleware($this->middlewares);
    }

    private function resolveFromID() {
        $this->id = Helper::decrypt(request('id'));

        $config = (new Relation($this->model, $this->id ))->resolveFromFieldMaker()->getConfigs();

        $this->relationName = $config['relation_name'];
        $this->relationColumn = $config['relation_column'];

        $this->authorizeUsing = $config['authorize_using'];
        $this->bypassAuthorize = $config['bypass_authorize'];
        $this->rules = $config['rules'] ?? ['array'];
        $this->eachRules = $config['eachRules'];

        if($config['middlewares']) {
            $suppliedMiddlewares = $config['middlewares'];

            $this->middlewares = is_array($suppliedMiddlewares) ? 
                                array_merge($this->middlewares, $suppliedMiddlewares) : 
                                array_merge($this->middlewares, [$suppliedMiddlewares]);
        }

        $this->saveUsing = $config['save_using'];

        $this->renderUsing = $config['render_using'];
        $this->renderTemplate = $config['render_template'];
    }

    private function resolveFromAttributes() {
        $this->relationName = Helper::decrypt(request('relationName'));
        $this->relationColumn = Helper::decrypt(request('relColumn'));
        $this->renderTemplate = request()->filled('renderTemplate') ? Helper::decrypt(request('renderTemplate')) : null;
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

        $this->resolveFromAttributes();
    }

    private function hydrateRules($rules, $eachRules) {
        if($rules) {
            $rulesDecrypted = Helper::decrypt($rules);

            try {
                $this->rules = unserialize($rulesDecrypted);
            } catch(\Exception $e) {
                $this->rules = ['array'];
            }
        }

        if($eachRules) {
            $rulesDecrypted = Helper::decrypt($eachRules);

            try {
                $this->eachRules = unserialize($rulesDecrypted);
            } catch(\Exception $e) {
                $this->eachRules = null;
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

    private function render()
    {
        return Draw::using(
            $this->model->{$this->relationName}, 
            $this->relationColumn, 
            $this->renderUsing, 
            $this->renderTemplate)->getRendered();
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
                'message' => "saved !",
                'redner' => $this->render()
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
            'message' => (bool) $status['success'] === false ? (isset($status['message'])? $status['message'] : 'Error saving data!') : '',
            'redner' => $status['success'] ? $this->render() : ''
        ];
    }
}