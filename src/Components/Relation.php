<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component as ViewComponent;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Relations\Relation as EloquentRelation;
use Illuminate\Contracts\Container\BindingResolutionException;
use devsrv\inplace\Exceptions\{ ModelException, RelationException, ConfigException };
use devsrv\inplace\InplaceConfig;

class Relation extends ViewComponent
{
    public $id;
    public $authorize;
    public $model;
    public $relationName;
    public $relationColumn;
    public $relationPrimaryKey;
    public $validation;
    public $thumbnailed;
    public $thumbnailWidth;
    public $resolveThumbnail = null;
    public $print;
    public $renderTemplate;

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
    public function __construct($model, $relationName = null, $relationColumn = null, $id = null, $authorize = null, $validation = null, $filterOptions = null, $thumbnailed = false, $thumbnailWidth = 30, $renderTemplate = null)
    {
        if($id) {
            $this->resolveConfigUsingID($id, $model);
        }
        else {
            $this->resolveConfigUsingAttributes($model, $relationName, $relationColumn, $authorize, $validation, $filterOptions, $thumbnailed, $thumbnailWidth, $renderTemplate);
        }
    }

    private function resolveConfigUsingID(string $id, $model) {
        try {
            $config = app()->make(InplaceConfig::class);
        } catch (BindingResolutionException $th) {
            throw ConfigException::notFound();
        }

        $relationManager = $config::getRelationEditor($id);
        throw_if(is_null($relationManager), ConfigException::missing($id));
        
        [$relation, $relatedModel] = $this->validate($model, $relationManager->relationName);
        $this->model = Crypt::encryptString($model);
        $this->relationPrimaryKey = $relatedModel->getKeyName();
        $this->relationColumn = $relationManager->column;

        $this->options = $this->deriveOptions($relatedModel, $relationManager->column, $relationManager->filterOptionsQuery);
        $this->currentValues = $relation->get()->pluck($this->relationPrimaryKey)->all();
        $this->thumbnailed = $relationManager->thumbnail;
        $this->thumbnailWidth = $relationManager->thumbnailWidth;
        $this->resolveThumbnail = $relationManager->resolveThumbnailUsing;

        if($relationManager->renderPartial) {
            $this->print = $this->renderUsingPartial($relation, $relationManager->renderPartial, $relationManager->renderQuery);
        }
        else if($relationManager->renderUsing) {
            $this->print = $this->renderUsingClosure($relation, $relationManager->renderUsing);
        }
        else {
            $this->print = $this->renderDefault($relation, $relationManager->column);
        }
    }

    private function resolveConfigUsingAttributes($model, $relationName, $relationColumn, $authorize, $validation, $filterOptions, $thumbnailed, $thumbnailWidth, $renderTemplate) {
        throw_if(is_null($relationName), RelationException::missing('relation name required'));
        throw_if(is_null($relationColumn), RelationException::missing('relation column required'));

        [$relation, $relatedModel] = $this->validate($model, $relationName);

        $this->model = Crypt::encryptString($model);
        $this->relationName = Crypt::encryptString($relationName);
        $this->relationColumn = $relationColumn;
        $this->relationPrimaryKey = $relatedModel->getKeyName();
        $this->validation = $validation;
        $this->authorize = $authorize;
        $this->thumbnailed = $thumbnailed;
        $this->thumbnailWidth = $thumbnailWidth;

        $this->print = $renderTemplate? $this->renderUsingPartial($relation, $renderTemplate) : $this->renderDefault($relation, $relationColumn);
        $this->options = $this->deriveOptions($relatedModel, $relationColumn, $filterOptions);
        $this->currentValues = $relation->get()->pluck($this->relationPrimaryKey)->all();
    }

    private function renderUsingPartial($relation, $partial, $mergeQuery = null) {
        $query = is_callable($mergeQuery) ? $mergeQuery($relation) : $relation;
        return view($partial, ['items' => $query->get()]);
    }

    private function renderUsingClosure($relation, callable $mergeQuery) {
        return $mergeQuery($relation);
    }

    private function renderDefault($relation, $relationColumn) {
        return $relation->pluck($relationColumn)->implode(', ');
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

        if(is_callable($withQuery)) {
            $relatedModel = $withQuery($relatedModel);
        }

        return $relatedModel->get();
    }

    public function resolveThumbnail($model)
    {
        if(! $this->thumbnailed) return null;

        if($this->resolveThumbnail && is_callable($this->resolveThumbnail)) {
            return call_user_func($this->resolveThumbnail, $model);
        }

        return $model->inplaceThumb ?? null;
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
