<?php

namespace devsrv\inplace\Fields\Relation;
use devsrv\inplace\Contracts\Assemble;
use devsrv\inplace\Exceptions\RelationException;
use devsrv\inplace\Traits\{ ModelResolver, ConfigResolver };
use devsrv\inplace\Memo;
use devsrv\inplace\Draw;
use Illuminate\Database\Eloquent\Model;

class Relation implements Assemble {
    use ModelResolver, ConfigResolver;

    public $id;
    public $model;
    public $relationName;
    public $relationColumn;
    public $rules;
    public $eachItemRules;
    public $thumbnailed;
    public $thumbnailWidth;
    public $multiple;
    public $renderTemplate;
    public $renderUsing = null;
    public $filterOptionsQuery;

    public $relationPrimaryKey;
    public $resolveThumbnail = null;

    private $authorizeUsing = null;
    private $bypassAuthorize = false;
    private $middlewares = null;
    private $saveUsing = null;

    private $modelFormatted;
    private $relation;
    private $relatedModel;
    
    private $memo;

    const SUPPORTED_RELATIONS = [
        'BelongsToMany'
    ];

    public function __construct(
        $model, 
        $id = null, 
        $relationName = null, 
        $relationColumn = null, 
        $validation = null, 
        $validateEach = null, 
        $thumbnailed = false, 
        $thumbnailWidth = 30, 
        $multiple = true, 
        $renderTemplate = null,
        $filterOptionsQuery = null,
    )
    {
        $this->model = $model;
        $this->id = $id;
        $this->relationName = $relationName;
        $this->relationColumn = $relationColumn;
        $this->rules = $validation;
        $this->eachItemRules = $validateEach;
        $this->thumbnailed = $thumbnailed;
        $this->thumbnailWidth = $thumbnailWidth;
        $this->multiple = $multiple;
        $this->renderTemplate = $renderTemplate;
        $this->filterOptionsQuery = $filterOptionsQuery;
    }

    public function resolveFromComponentAttribute() 
    {
        throw_if(is_null($this->relationName), RelationException::missing('relation name required'));
        throw_unless($this->relationColumn, RelationException::missing('relation column required'));

        [$modelFormatted, $relation, $relatedModel] = $this->validate($this->model, $this->relationName);

        $this->modelFormatted = $modelFormatted;
        $this->relation = $relation;
        $this->relatedModel = $relatedModel;
        $this->relationPrimaryKey = $relatedModel->getKeyName();

        return $this;
    }

    public function resolveFromFieldMaker() 
    {
        $relationManager = self::getConfig('relation', $this->id);
        
        [$modelFormatted, $relation, $relatedModel] = $this->validate($this->model, $relationManager->relationName);

        $this->modelFormatted = $modelFormatted;
        $this->relatedModel = $relatedModel;
        $this->relation = $relation;
        $this->relationName = $relationManager->relationName;
        $this->relationPrimaryKey = $relatedModel->getKeyName();
        $this->relationColumn = $relationManager->column;
        $this->filterOptionsQuery = $relationManager->filterOptionsQuery;

        $this->thumbnailed = $relationManager->thumbnail;
        $this->thumbnailWidth = $relationManager->thumbnailWidth;
        $this->resolveThumbnail = $relationManager->resolveThumbnailUsing;
        $this->multiple = $relationManager->multiple;

        $this->authorizeUsing = $relationManager->authorizeUsing;
        $this->bypassAuthorize = $relationManager->bypassAuthorize;

        $this->rules = $relationManager->rules;
        $this->eachItemRules = $relationManager->eachItemRules;
        $this->middlewares = $relationManager->middlewares;
        $this->saveUsing = $relationManager->saveUsingInvokable;
        $this->renderTemplate = $relationManager->renderPartial;
        $this->renderUsing = $relationManager->renderUsing;

        return $this;
    }

    private function initMemo($modelFormatted, $relationName) {
        if($this->id) {
            $this->memo = Memo::key( $this->id );
            return;
        }

        [$modelClassName] = explode(':', $modelFormatted);
        $this->memo = Memo::key( $modelClassName . ':' . $relationName );
    }

    private function validate($model, $relationName)
    {
        $modelString = $this->resolveModel($model);

        $this->initMemo($modelString, $relationName);

        [$modelClass, $primaryKeyValue] = explode(':', $modelString);

        if(! $parentModel = $this->memo->getMemoized($modelString, true)) {
            $parentModel = $modelClass::select('id')->findOrFail($primaryKeyValue);
            $this->memo->memoize($modelString, $parentModel, true);
        }

        try {
            $relation = $parentModel->{$relationName}();
            $relatedModel = $relation->getRelated();
        } catch (\BadMethodCallException $e) {
            throw RelationException::notFound($modelClass, $relationName);
        }

        throw_unless(in_array(class_basename($relation), self::SUPPORTED_RELATIONS), RelationException::notSupported($relationName));

        return [$modelString, $relation, $relatedModel];
    }

    private function getOptions() {
        if(! $options = $this->memo->getMemoized('options')) {
            $options = $this->deriveOptions($this->relatedModel, $this->relationColumn, $this->filterOptionsQuery);
            $this->memo->memoize('options', $options);
        }

        return $options;
    }

    private function deriveOptions($relatedModel, $relationColumn, $withQuery) {
        $selectFields = [$relatedModel->getTable() .'.'. $this->relationPrimaryKey, $relatedModel->getTable() .'.'. $relationColumn];

        $relatedModel = $relatedModel->select($selectFields);

        if(is_callable($withQuery)) {
            $relatedModel = $withQuery($relatedModel);
        }

        $relatedList = $relatedModel->orderBy($relationColumn)->get();

        $allOptions = [];

        foreach ($relatedList as $item) {
            $allOptions[] = [
                'thumbnail' => $this->thumbnailed ? $this->resolveThumbnail($item) : '',
                'value' => $item->getAttributeValue($this->relationPrimaryKey),
                'label' => $item->getAttributeValue($this->relationColumn)
            ];
        }

        return $allOptions;
    }

    private function resolveThumbnail($model)
    {
        if(! $this->thumbnailed) return null;

        if($this->resolveThumbnail && is_callable($this->resolveThumbnail)) {
            return call_user_func($this->resolveThumbnail, $model);
        }

        return $model->inplaceThumb ?? null;
    }

    public function getValues() {
        if($this->model instanceof Model) {
            $relationValues = $this->model->relationLoaded($this->relationName) ? 
                            $this->model->{$this->relationName} : $this->relation->get();
        } else {
            $relationValues = $this->relation->get();
        }

        return [
            'options' => $this->getOptions(),
            'current_values' => $relationValues->pluck($this->relationPrimaryKey)->all(),
            'render' => Draw::using(
                $relationValues, 
                $this->relationColumn, 
                $this->renderUsing, 
                $this->renderTemplate)->getRendered()
        ];
    }

    public function getConfigs() {
        return [
            'model' => $this->modelFormatted,
            'relation_name' => $this->relationName,
            'relation_column' => $this->relationColumn,
            'render_using' => $this->renderUsing,
            'render_template' => $this->renderTemplate,
            'multiple' => $this->multiple,
            'thumbnailed' => $this->thumbnailed, 
            'thumbnail_width' => $this->thumbnailWidth, 
            'authorize_using' => $this->authorizeUsing, 
            'bypass_authorize' => $this->bypassAuthorize, 
            'rules' => $this->rules, 
            'eachRules' => $this->eachItemRules,
            'middlewares' => $this->middlewares, 
            'save_using' => $this->saveUsing, 
        ];
    }
}