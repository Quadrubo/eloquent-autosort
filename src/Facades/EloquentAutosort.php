<?php

namespace Quadrubo\EloquentAutosort\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Quadrubo\EloquentAutosort\EloquentAutosort
 */
class EloquentAutosort extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Quadrubo\EloquentAutosort\EloquentAutosort::class;
    }
}
