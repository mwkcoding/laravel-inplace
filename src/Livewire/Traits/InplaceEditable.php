<?php

namespace devsrv\inplace\Livewire\Traits;
use devsrv\inplace\Exceptions\ModelException;

trait InplaceEditable
{
    public bool $inline = false;
    public $model;
    public $column;
    public $value;
    public $prepend = null;
    public $append = null;
    public $editedValue = '';
    public $options;
    public $renderAs;
    public $saveusing = null;
    public $renderFormField;
    public $validation = 'required';
    public $shouldAuthorize = null;

    public function mount($model = null, $prepend = null, $append = null, $renderAs = null) {
        $this->renderAs = $renderAs ?? ( $this->inline ? 'inplace-inline-basic-common' : 'inplace-editable-renderas-common' );

        $this->prepend = $prepend ? htmlentities($prepend) : null;
        $this->append = $append ? htmlentities($append) : null;

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

    private function handleValidation($editedValue, $customRules = null) {
        $this->editedValue = $editedValue;

        if($customRules) {
            $this->validate([
                'editedValue' => $customRules
            ]);

            return;
        }

        $validateRules = $this->validation;

        try {
            $validateRules = unserialize($this->validation);
        } catch(\Exception $e) {
            $validateRules = $this->validation;
        }

        $this->validate([
            'editedValue' => $validateRules
        ]);
    }

    protected function handleAuthorize() {
        if($this->shouldAuthorize !== null) {
            if($this->shouldAuthorize && $this->model) { $this->authorize('update', $this->model); }
            else return true;
        }

        $globalAuthorize = config('inplace.authorize');

        if($globalAuthorize !== null && $globalAuthorize && $this->model) $this->authorize('update', $this->model);
    }

    public function render()
    {
        $view = $this->inline ? 'inline-editable' : 'editable';

        return view('inplace::livewire.'. $view);
    }
}
