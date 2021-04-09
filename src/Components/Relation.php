<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component as ViewComponent;
use Illuminate\Support\Facades\Crypt;
use devsrv\inplace\Exceptions\{ ModelException, RelationException };

class Relation extends ViewComponent
{
    public $authorize;
    public $model;
    public $relationName;
    public $relationColumn;
    public $relationPrimaryKey;
    public $validation;

    const SUPPORTED_RELATIONS = [
        'BelongsToMany'
    ];

    public $options = [];
    public $currentValues = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($model, $relationName, $relationColumn, $authorize = null, $validation = null, $withQuery = null)
    {
        [$relation, $relatedModel] = $this->validate($model, $relationName);

        $this->model = Crypt::encryptString($model);
        $this->relationName = Crypt::encryptString($relationName);
        $this->relationColumn = $relationColumn;
        $this->relationPrimaryKey = $relatedModel->getKeyName();
        $this->validation = $validation;
        $this->authorize = $authorize;

        $this->options = $this->deriveOptions($relatedModel, $relationColumn, $withQuery);
        $this->currentValues = $relation->get()->pluck($this->relationPrimaryKey)->all();
    }

    private function validate($model, $relationName)
    {
        try {
            [$modelClass, $primaryKey] = explode(':', $model);
        } catch (\Exception $th) {
            throw ModelException::badFormat('namespace\Model:key');
        }
        
        if(! class_exists($modelClass)) throw ModelException::notFound($modelClass);

        $parentModel = $modelClass::findOrFail($primaryKey);

        try {
            $relation = $parentModel->{$relationName}();
            $relatedModel = $relation->getRelated();
        } catch (\BadMethodCallException $e) {
            throw RelationException::notFound($modelClass, $relationName);
        }

        throw_unless(in_array(class_basename($relation), self::SUPPORTED_RELATIONS), RelationException::notSupported($relationName));

        return [$relation, $relatedModel];
    }

    private function deriveOptions($relatedModel, $relationColumn, $withQuery) {
        $selectFields = [$relatedModel->getTable() .'.'. $this->relationPrimaryKey, $relatedModel->getTable() .'.'. $relationColumn];

        $relatedModel = $relatedModel->select($selectFields);

        if($withQuery) {
            $relatedModel = $withQuery($relatedModel);
        }

        return $relatedModel->get();
    }
    

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('inplace::components.relation');
    }
}
