<?php

namespace devsrv\inplace\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use devsrv\inplace\Livewire\Traits\InplaceEditable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Editable extends Component
{
    use InplaceEditable, AuthorizesRequests;

    public function save($editedValue) {
        $this->handleAuthorize();

        $this->handleValidation($editedValue);

        if($this->saveusing) { return $this->customSave($this->model, $this->column, $editedValue); }

        if(! $this->model) throw new \Exception('No model to update');

        // db save success
        $this->model->{$this->column} = $editedValue;
        if($this->model->save()) {
            $this->value = $editedValue;
            return [
                'success' => 1
            ];
        }

        // db perform fail
        return [
            'success' => 0,
            'message' => "couldn't save data"
        ];
    }

    protected function customSave($model, $column, $editedValue) {
        if(! class_exists($this->saveusing)) { throw new \Exception('Custom editable class not found'); }

        $saveAs = new $this->saveusing;
        
        if(! is_callable([$saveAs, 'save'])) throw new \Exception('Custom editable method not callable');

        $status = ($saveAs)->save($model, $column, $editedValue);

        if(! is_array($status) || !isset($status['success'])) {
            throw new \Exception('Invalid response, expected array with required parameter - (bool) success');
        }

        if($status['success']) {
            $this->value = $editedValue;
            return [
                'success' => 1
            ];
        }

        return [
            'success' => 0,
            'message' => isset($status['message'])? $status['message'] : 'Error saving data!'
        ];
    }
}
