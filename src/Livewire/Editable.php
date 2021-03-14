<?php

namespace devsrv\inplace\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use devsrv\inplace\Livewire\Traits\InplaceEditable;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Editable extends Component
{
    use InplaceEditable;

    public function save($editedValue) {
        // $this->authorize('update', $this->post);

        $this->handleValidation($editedValue);

        if($this->saveusing) { return $this->customSave($editedValue); }

        // db perform success
        $this->value = $editedValue;
        return [
            'success' => 1
        ];

        // db perform fail
        // return [
        //     'success' => 0
        // ];
    }

    protected function customSave($editedValue) {
        if(! class_exists($this->saveusing)) { throw new \Exception('Custom editable class not found'); }

        $saveAs = new $this->saveusing;
        
        if(! is_callable([$saveAs, 'save'])) throw new \Exception('Custom editable method not callable');

        $status = ($saveAs)->save($editedValue);

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
