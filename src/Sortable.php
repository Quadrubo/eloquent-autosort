<?php

namespace Quadrubo\EloquentAutosort;

use Illuminate\Database\Eloquent\Builder;

interface Sortable
{
    /**
     * Modify the order column value.
     */
    public function setHighestOrderNumber(): void;

    /**
     * Let's be nice and provide an ordered scope.
     *
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOrdered(Builder $query);

    /**
     * This function reorders the records: the record with the first id in the array
     * will get order 1, the record with the second it will get order 2,...
     *
     * @param  array|\ArrayAccess  $ids
     */
    public static function setNewOrder($ids, int $startOrder = 1): void;

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool;

    public function shouldSortWhenUpdating(): bool;

    public function shouldSortWhenDeleting(): bool;

    public function hasChangedGroupAttributes(): bool;

    public function hasChangedOrderColumn(): bool;

    public function moveToNewPosition(): static;

    public function repairOrder(bool $original = true, bool $dirty = true): void;
}
