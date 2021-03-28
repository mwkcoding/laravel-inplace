<?php

namespace devsrv\inplace;

use Livewire\Livewire;
use Illuminate\Support\ServiceProvider;
use devsrv\inplace\Components\{
    Component,
    InlineBasicCommon,
    InlineText,
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
            InlineText::class,
        ]);

        Livewire::component('editable', Editable::class);

        $this->publishes([
            __DIR__.'/../config/inplace.php' => config_path('inplace.php'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
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
