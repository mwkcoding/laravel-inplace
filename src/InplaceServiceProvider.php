<?php

namespace devsrv\inplace;

use Illuminate\Support\ServiceProvider;

class InplaceServiceProvider extends ServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name(':package_name')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_skeleton_table')
            ->hasCommand(SkeletonCommand::class);
    }
}
