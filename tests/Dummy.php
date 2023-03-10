<?php

namespace Quadrubo\EloquentAutosort\Tests;

use Illuminate\Database\Eloquent\Model;
use Quadrubo\EloquentAutosort\Sortable;
use Quadrubo\EloquentAutosort\SortableTrait;

class Dummy extends Model implements Sortable
{
    use SortableTrait;

    protected $table = 'dummies';

    protected $guarded = [];

    public $timestamps = false;
}
