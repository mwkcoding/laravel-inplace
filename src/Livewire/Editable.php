<?php

namespace devsrv\inplace\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use devsrv\inplace\Livewire\Traits\InplaceEditable;

class Editable extends Component
{
    use InplaceEditable;

    public function save($editedValue) {
        $this->handleValidation($editedValue);

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
}
