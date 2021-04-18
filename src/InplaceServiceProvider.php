<?php

namespace devsrv\inplace;

use Illuminate\Support\ServiceProvider;
use devsrv\inplace\Components\{
    Relation,
    InlineBasicCommon,
    Inline,
};
use devsrv\inplace\Commands\GenerateConfig;

class InplaceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateConfig::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inplace');

        $this->loadViewComponentsAs('inplace', [
            InlineBasicCommon::class,
            Inline::class,
            Relation::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/inplace.php' => config_path('inplace.php'),
        ], 'config');

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        $this->publishes([
            __DIR__.'/../public/dist' => public_path('vendor/inplace'),
        ], 'public');
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
