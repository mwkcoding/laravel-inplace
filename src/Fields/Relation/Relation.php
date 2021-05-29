<?php

namespace devsrv\inplace\Fields\Relation;
use devsrv\inplace\Contracts\Assemble;
use devsrv\inplace\Exceptions\RelationException;
use devsrv\inplace\Traits\{ ModelResolver, ConfigResolver };

class Relation implements Assemble {
    use ModelResolver, ConfigResolver;

    public $id;
    public $model;
    public $relationName;
    public $relationColumn;
    public $validation;
    public $thumbnailed;
    public $thumbnailWidth;
    public $multiple;
    public $renderTemplate;
    public $filterOptionsQuery;

    public $relationPrimaryKey;
    public $resolveThumbnail = null;
    public $renderValue;

    private $authorizeUsing = null;
    private $bypassAuthorize = false;
    private $middlewares = null;

    private $modelFormatted;
    private $relation;
    private $relatedModel;
    

    const SUPPORTED_RELATIONS = [
        'BelongsToMany'
    ];

    public function __construct(
        $model, 
        $id = null, 
        $relationName = null, 
        $relationColumn = null, 
        $validation = null, 
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
        $this->validation = $validation;
        $this->thumbnailed = $thumbnailed;
        $this->thumbnailWidth = $thumbnailWidth;
        $this->multiple = $multiple;
        $this->renderTemplate = $renderTemplate;
        $this->filterOptionsQuery = $filterOptionsQuery;
    }

    public function resolveFromComponentAttribute() 
    {
        throw_if(is_null($this->relationName), RelationException::missing('relation name required'));
        throw_if(is_null($this->relationColumn), RelationException::missing('relation column required'));

        [$modelFormatted, $relation, $relatedModel] = $this->validate($this->model, $this->relationName);

        $this->modelFormatted = $modelFormatted;
        $this->relation = $relation;
        $this->relatedModel = $relatedModel;
        $this->relationPrimaryKey = $relatedModel->getKeyName();

        $this->renderValue = $this->renderTemplate? $this->renderUsingPartial($relation, $this->renderTemplate) : $this->renderDefault($relation, $this->relationColumn);

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

        $this->validation = $relationManager->rules;
        $this->middlewares = $relationManager->middlewares;

        if($relationManager->renderPartial) {
            $this->renderValue = $this->renderUsingPartial($relation, $relationManager->renderPartial, $relationManager->renderQuery);
        }
        else if($relationManager->renderUsing) {
            $this->renderValue = $this->renderUsingClosure($relation, $relationManager->renderUsing);
        }
        else {
            $this->renderValue = $this->renderDefault($relation, $relationManager->column);
        }

        return $this;
    }

    private function validate($model, $relationName)
    {
        $modelString = $this->resolveModel($model);

        [$modelClass, $primaryKeyValue] = explode(':', $modelString);

        $parentModel = $modelClass::findOrFail($primaryKeyValue);

        try {
            $relation = $parentModel->{$relationName}();
            $relatedModel = $relation->getRelated();
        } catch (\BadMethodCallException $e) {
            throw RelationException::notFound($modelClass, $relationName);
        }

        throw_unless(in_array(class_basename($relation), self::SUPPORTED_RELATIONS), RelationException::notSupported($relationName));

        return [$modelString, $relation, $relatedModel];
    }

    private function renderUsingPartial($relation, $partial, $mergeQuery = null) {
        $query = is_callable($mergeQuery) ? $mergeQuery(clone $relation) : $relation;
        return view($partial, ['items' => $query->get()]);
    }

    private function renderUsingClosure($relation, callable $mergeQuery) {
        return $mergeQuery(clone $relation);
    }

    private function renderDefault($relation, $relationColumn) {
        return $relation->pluck($relationColumn)->implode(', ');
    }

    private function getOptions() {
        return $this->deriveOptions($this->relatedModel, $this->relationColumn, $this->filterOptionsQuery);
    }

    private function deriveOptions($relatedModel, $relationColumn, $withQuery) {
        $selectFields = [$relatedModel->getTable() .'.'. $this->relationPrimaryKey, $relatedModel->getTable() .'.'. $relationColumn];

        $relatedModel = $relatedModel->select($selectFields);

        if(is_callable($withQuery)) {
            $relatedModel = $withQuery($relatedModel);
        }

        $relatedList = $relatedModel->get();

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
        return [
            'model' => $this->modelFormatted,
            'relation_name' => $this->relationName,
            'options' => $this->getOptions(),
            'current_values' => $this->relation->get()->pluck($this->relationPrimaryKey)->all(),
            'render_current' => $this->renderValue,
            'multiple' => $this->multiple,
            'thumbnailed' => $this->thumbnailed, 
            'thumbnail_width' => $this->thumbnailWidth, 
            'authorize_using' => $this->authorizeUsing, 
            'bypass_authorize' => $this->bypassAuthorize, 
            'validation' => $this->validation, 
            'middlewares' => $this->middlewares, 
        ];
    }
}