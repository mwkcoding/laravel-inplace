<?php

namespace devsrv\inplace;

use Illuminate\Support\ServiceProvider;
use devsrv\inplace\Components\{
    Relation,
    InlineBasicCommon,
    Inline,
};

class InplaceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inplace');

        $this->loadViewComponentsAs('inplace', [
            InlineBasicCommon::class,
            Inline::class,
            Relation::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/inplace.php' => config_path('inplace.php'),
        ]);

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->publishes([
            __DIR__.'/../public/dist' => public_path('vendor/inplace'),
        ], 'public');
        
        $this->publishes([
            __DIR__.'/Providers' => app_path('Providers'),
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
