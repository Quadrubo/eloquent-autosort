<?php

namespace Quadrubo\EloquentAutosort\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Quadrubo\EloquentAutosort\Sortable;
use Quadrubo\EloquentAutosort\SortableTrait;

class DummyWithSoftDeletes extends Model implements Sortable
{
    use SoftDeletes;
    use SortableTrait;

    protected $table = 'dummies';

    protected $guarded = [];

    public $timestamps = false;
}
