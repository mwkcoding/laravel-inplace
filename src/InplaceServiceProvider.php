<?php

namespace devsrv\inplace;

use Illuminate\Support\ServiceProvider;
use devsrv\inplace\Components\Component;

class InplaceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inplace');

        $this->loadViewComponentsAs('inplace', [
            Component::class,
        ]);
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
