<?php

namespace devsrv\inplace;

use Illuminate\Support\ServiceProvider;
use devsrv\inplace\Components\{
    Relation,
    InlineBasicCommon,
    Text,
};
use devsrv\inplace\Commands\GenerateConfig;
use devsrv\inplace\InplaceConfig;

class InplaceServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(InplaceConfig::class, function ($app) {
            $config = [
                'text' => class_exists('\App\Http\Inplace\Text') ? \App\Http\Inplace\Text::config() : null,
                'relation' => class_exists('\App\Http\Inplace\Relation') ? \App\Http\Inplace\Relation::config() : null
            ];
            return new InplaceConfig($config);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateConfig::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inplace');

        $this->loadViewComponentsAs('inplace', [
            InlineBasicCommon::class,
            Text::class,
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
