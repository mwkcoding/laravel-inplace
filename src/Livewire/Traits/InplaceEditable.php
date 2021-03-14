<?php

namespace devsrv\inplace\Livewire\Traits;

trait InplaceEditable
{
    public bool $inline = false;
    public $model;
    public $value;
    public $prepend = null;
    public $append = null;
    public $editedValue = '';
    public $options;
    public $renderAs;
    public $saveusing = null;
    public $renderFormField;
    public $validation = 'required';

    public function mount($model = null, $prepend = null, $append = null, $renderAs = null) {
        $this->renderAs = $renderAs ?? ( $this->inline ? 'inplace-inline-basic-common' : 'inplace-editable-renderas-common' );

        $this->prepend = $prepend ? htmlentities($prepend) : null;
        $this->append = $append ? htmlentities($append) : null;

        if($model) {
            try {
                [$modelClass, $primaryKey] = explode(':', $model);
            } catch (\Exception $th) {
                throw new \Exception('incorrect model attribute format, expected namespace\Model:key');
            }
            
            if(! class_exists($modelClass)) throw new \Exception('incorrect model class');

            $this->model = $modelClass::findOrFail($primaryKey);
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
        $globalAuthorize = config('inplace.authorize');

        if($globalAuthorize !== null && $globalAuthorize && $this->model) $this->authorize('update', $this->model);
    }

    public function render()
    {
        $view = $this->inline ? 'inline-editable' : 'editable';

        return view('inplace::livewire.'. $view);
    }
}
