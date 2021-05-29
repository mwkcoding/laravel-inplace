<?php

namespace devsrv\inplace\Components;

use Illuminate\View\Component as ViewComponent;
use Illuminate\Support\Facades\Crypt;
use devsrv\inplace\Fields\Relation\Relation as RelationField;

class Relation extends ViewComponent
{
    public $id;
    public $model;
    public $relationName;
    public $relationColumn;
    public $relationPrimaryKey;
    public $validation;
    public $validateEach;
    public $thumbnailed;
    public $thumbnailWidth;
    public $resolveThumbnail = null;
    public $multiple;
    public $renderValue;
    public $renderTemplate;
    public $filterOptionsQuery;

    public $options = [];
    public $currentValues = [];
    public $csrf_token;
    public $save_route;
    public $field_id;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($model, $relationName = null, $relationColumn = null, $id = null, $validation = null, $validateEach = null, $filterOptionsQuery = null, $thumbnailed = false, $thumbnailWidth = 30, $multiple = true, $renderTemplate = null)
    {
        $field = new RelationField(
            $model,
            $id,
            $relationName, 
            $relationColumn,
            $validation,
            $validateEach,
            $thumbnailed,
            $thumbnailWidth,
            $multiple,
            $renderTemplate,
            $filterOptionsQuery
        );

        if($id) {
            $this->id = Crypt::encryptString($id);
            $configPayload = $field->resolveFromFieldMaker()->getValues();
        }
        else {
            $configPayload = $field->resolveFromComponentAttribute()->getValues();

            $this->relationName = Crypt::encryptString($configPayload['relation_name']);
            $this->validation = $configPayload['rules'] ? Crypt::encryptString(serialize($configPayload['rules'])) : null;
            $this->validateEach = $configPayload['eachRules'] ? Crypt::encryptString(serialize($configPayload['eachRules'])) : null;
        }

        $this->field_id = 'relation:'.bin2hex(random_bytes(16));
        $this->csrf_token = csrf_token();
        $this->save_route = route('inplace.relation.save');
        $this->model = Crypt::encryptString($configPayload['model']);
        $this->options = $configPayload['options'];
        $this->multiple = $configPayload['multiple'];
        $this->thumbnailed = $configPayload['thumbnailed'];
        $this->thumbnailWidth = $configPayload['thumbnail_width'];
        $this->renderValue = $configPayload['render_current'];
        $this->currentValues = $configPayload['current_values'];
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
