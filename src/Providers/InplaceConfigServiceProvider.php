<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use devsrv\inplace\{InplaceConfig, RelationManager};

class InplaceConfigServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(InplaceConfig::class, function ($app) {
            return new InplaceConfig($this->config());
        });
    }

    private function config()
    {
        return [
            'inline' => [

            ],
            'relation' => [
                RelationManager::make('AUTHOR_BADGES')
                ->relation('badges', 'label')
                ->thumbnailed()
                ->renderUsing(fn($q) => $q->pluck('label')->implode('/')),
            ]
        ];
    }
}
