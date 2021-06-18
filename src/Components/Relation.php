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

    public $hash = null;

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

            $optionsResolver = $field->resolveFromFieldMaker();

            $config = $optionsResolver->getConfigs();
            $values = $optionsResolver->getValues();
        }
        else {
            $optionsResolver = $field->resolveFromComponentAttribute();
            $config = $optionsResolver->getConfigs();
            $values = $optionsResolver->getValues();

            $this->relationName = Crypt::encryptString($config['relation_name']);
            $this->relationColumn = Crypt::encryptString($relationColumn);
            $this->renderTemplate = $renderTemplate ? Crypt::encryptString($renderTemplate) : null;
            $this->validation = $config['rules'] ? Crypt::encryptString(serialize($config['rules'])) : null;
            $this->validateEach = $config['eachRules'] ? Crypt::encryptString(serialize($config['eachRules'])) : null;
        }

        $rand = bin2hex(random_bytes(16));
        $this->field_id = 'relation:'. $rand;
        $this->hash = $id ? md5($id) : md5($rand);
        $this->csrf_token = csrf_token();
        $this->save_route = route('inplace.relation.save');
        $this->model = Crypt::encryptString($config['model']);
        $this->multiple = $config['multiple'];
        $this->thumbnailed = $config['thumbnailed'];
        $this->thumbnailWidth = $config['thumbnail_width'];
        $this->renderValue = $values['render'];
        $this->options = $values['options'];
        $this->currentValues = $values['current_values'];
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
