<?php

namespace devsrv\inplace\Livewire\Traits;

trait InplaceEditable
{
    public bool $inline = false;
    public $value;
    public $prepend = null;
    public $append = null;
    public $editedValue = '';
    public $options;
    public $renderAs;
    public $saveusing = null;
    public $renderFormField;
    public $validation = 'required';

    public function mount($prepend = null, $append = null, $renderAs = null) {
        $this->renderAs = $renderAs ?? ( $this->inline ? 'inplace-inline-basic-common' : 'inplace-editable-renderas-common' );

        $this->prepend = $prepend ? htmlentities($prepend) : null;
        $this->append = $append ? htmlentities($append) : null;
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

    public function render()
    {
        $view = $this->inline ? 'inline-editable' : 'editable';

        return view('inplace::livewire.'. $view);
    }
}
