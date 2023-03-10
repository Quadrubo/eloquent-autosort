<?php

namespace Quadrubo\EloquentAutosort;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Quadrubo\EloquentAutosort\Commands\EloquentAutosortCommand;

class EloquentAutosortServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('eloquent-autosort')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_eloquent-autosort_table')
            ->hasCommand(EloquentAutosortCommand::class);
    }
}
