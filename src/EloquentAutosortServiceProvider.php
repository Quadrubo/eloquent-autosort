<?php

namespace Quadrubo\EloquentAutosort;

use Quadrubo\EloquentAutosort\Commands\EloquentAutosortCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->hasConfigFile();
    }
}
