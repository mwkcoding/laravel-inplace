<?php

namespace devsrv\inplace;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use devsrv\inplace\Components\{
    Component,
    InlineBasicCommon
};
use devsrv\inplace\Livewire\Editable;

class InplaceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inplace');

        $this->loadViewComponentsAs('inplace', [
            Component::class,
            InlineBasicCommon::class,
        ]);

        Livewire::component('editable', Editable::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
