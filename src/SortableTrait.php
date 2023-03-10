<?php

namespace Quadrubo\EloquentAutosort;

use ArrayAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use InvalidArgumentException;

trait SortableTrait
{
    public static function bootSortableTrait()
    {
        static::creating(function ($model) {
            if ($model instanceof Sortable && $model->shouldSortWhenCreating()) {
                $model->setHighestOrderNumber();
            }
        });

        static::updating(function ($model) {
            if ($model instanceof Sortable && $model->shouldSortWhenUpdating()) {
                if ($model->hasChangedOrderColumn() && $model->hasChangedGroupAttributes()) {
                    $model->moveToNewPosition();
                    $model->repairOrder(dirty: false);
                } elseif ($model->hasChangedOrderColumn()) {
                    $model->moveToNewPosition();
                } elseif ($model->hasChangedGroupAttributes()) {
                    $model->repairOrder();
                }
            }
        });

        static::deleting(function ($model) {
            if ($model instanceof Sortable && $model->shouldSortWhenDeleting()) {
                $model->setHighestOrderNumber();
            }
        });
    }

    public function buildBaseSortQuery(): Builder
    {
        return static::query()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function buildSortQuery(bool $original = false): Builder
    {
        $query = $this->buildBaseSortQuery();

        foreach ($this->getSortableGroups() as $attribute) {
            $query = $query->where($attribute, $original ? $this->getOriginal($attribute) : $this->{$attribute});
        }

        return $query;
    }

    public function repairOrder(bool $original = true, bool $dirty = true): void
    {
        if ($original) {
            $originalCollection = $this->buildSortQuery()->ordered()->withoutKey($this->getKey())->get();
            $originalArray = $originalCollection->pluck($this->getKeyName())->toArray();

            // sort old records without id of model
            static::setNewOrder($originalArray);
        }

        if ($dirty) {
            $dirtyCollection = $this->buildSortQuery()->ordered()->get();
            $dirtyCollection->push($this);
            $dirtyArray = $dirtyCollection->pluck($this->getKeyName())->toArray();

            // sort new records with id of model
            static::setNewOrder($dirtyArray);
        }
    }

    public function hasChangedGroupAttributes(): bool
    {
        return ! empty($this->getChangedAttributes());
    }

    public function hasChangedOrderColumn(): bool
    {
        return $this->isDirty($this->determineOrderColumnName());
    }

    public function getChangedAttributes(): array
    {
        $dirtyAttributes = $this->getDirty();
        $sortableGroups = $this->getSortableGroups();

        $changedAttributes = [];

        foreach ($dirtyAttributes as $key => $dirtyAttribute) {
            if (in_array($key, $sortableGroups)) {
                $changedAttributes[] = $key;
            }
        }

        return $changedAttributes;
    }

    public function setHighestOrderNumber(): void
    {
        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    public function getHighestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->max($this->determineOrderColumnName());
    }

    public function getLowestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->min($this->determineOrderColumnName());
    }

    public function scopeWithoutKey(Builder $query, mixed $key): void
    {
        $query->whereNot($this->getKeyName(), $key);
    }

    public function scopeWithKey(Builder $query, mixed $key): void
    {
        $query->orWhere($this->getKeyName(), $key);
    }

    public function scopeOrdered(Builder $query, string $direction = 'asc')
    {
        return $query->orderBy($this->determineOrderColumnName(), $direction);
    }

    public static function setNewOrder($ids, int $startOrder = 1, string $primaryKeyColumn = null): void
    {
        if (! is_array($ids) && ! $ids instanceof ArrayAccess) {
            throw new InvalidArgumentException('You must pass an array or ArrayAccess object to setNewOrder');
        }

        $model = new static();

        $orderColumnName = $model->determineOrderColumnName();

        if (is_null($primaryKeyColumn)) {
            $primaryKeyColumn = $model->getKeyName();
        }

        foreach ($ids as $id) {
            static::withoutGlobalScope(SoftDeletingScope::class)
                ->where($primaryKeyColumn, $id)
                ->update([$orderColumnName => $startOrder++]);
        }
    }

    public static function setNewOrderByCustomColumn(string $primaryKeyColumn, $ids, int $startOrder = 1)
    {
        self::setNewOrder($ids, $startOrder, $primaryKeyColumn);
    }

    public function determineOrderColumnName(): string
    {
        return $this->sortable['order_column_name'] ?? config('eloquent-sortable.order_column_name', 'order_column');
    }

    public function getSortableGroups(): array
    {
        return $this->sortable['groups'] ?? config('eloquent-sortable.groups', []);
    }

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool
    {
        return $this->sortable['sort_when_creating'] ?? config('eloquent-sortable.sort_when_creating', true);
    }

    public function shouldSortWhenUpdating(): bool
    {
        return $this->sortable['sort_when_updating'] ?? config('eloquent-sortable.sort_when_updating', true);
    }

    public function shouldSortWhenDeleting(): bool
    {
        return $this->sortable['sort_when_deleting'] ?? config('eloquent-sortable.sort_when_deleting', true);
    }

    public function moveOrderDown(): static
    {
        $orderColumnName = $this->determineOrderColumnName();

        $swapWithModel = $this->buildSortQuery()->limit(1)
            ->ordered()
            ->where($orderColumnName, '>', $this->$orderColumnName)
            ->first();

        if (! $swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    public function moveOrderUp(): static
    {
        $orderColumnName = $this->determineOrderColumnName();

        $swapWithModel = $this->buildSortQuery()->limit(1)
            ->ordered('desc')
            ->where($orderColumnName, '<', $this->$orderColumnName)
            ->first();

        if (! $swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    public function swapOrderWithModel(Sortable $otherModel): static
    {
        $orderColumnName = $this->determineOrderColumnName();

        $oldOrderOfOtherModel = $otherModel->$orderColumnName;

        $otherModel->$orderColumnName = $this->$orderColumnName;
        $otherModel->saveQuietly();

        $this->$orderColumnName = $oldOrderOfOtherModel;
        $this->saveQuietly();

        return $this;
    }

    public static function swapOrder(Sortable $model, Sortable $otherModel): void
    {
        $model->swapOrderWithModel($otherModel);
    }

    public function moveToStart(): static
    {
        $firstModel = $this->buildSortQuery()->limit(1)
            ->ordered()
            ->first();

        if ($firstModel->getKey() === $this->getKey()) {
            return $this;
        }

        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = $firstModel->$orderColumnName;
        $this->saveQuietly();

        $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->getKey())->increment($orderColumnName);

        return $this;
    }

    public function moveToEnd(): static
    {
        $maxOrder = $this->getHighestOrderNumber();

        $orderColumnName = $this->determineOrderColumnName();

        if ($this->$orderColumnName === $maxOrder) {
            return $this;
        }

        $oldOrder = $this->$orderColumnName;

        $this->$orderColumnName = $maxOrder;
        $this->saveQuietly();

        $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->getKey())
            ->where($orderColumnName, '>', $oldOrder)
            ->decrement($orderColumnName);

        return $this;
    }

    public function moveToNewPosition(): static
    {
        return $this->moveToPosition($this->{$this->determineOrderColumnName()});
    }

    public function moveToPosition(int $position): static
    {
        $query = $this->buildSortQuery()->ordered()->get();

        $sortArray = $query->pluck($this->getKeyName())->toArray();

        // remove from array
        array_splice($sortArray, (int) $this->getOriginal($this->determineOrderColumnName()) - 1, 1);

        // add at positon
        array_splice($sortArray, (int) $position - 1, 0, $this->getKey());

        static::setNewOrder($sortArray);

        // set the order column incase user provided value out of range
        $orderColumnName = $this->determineOrderColumnName();

        $this->$orderColumnName = array_search($this->getKey(), $sortArray) + 1;

        return $this;
    }

    public function isLastInOrder(): bool
    {
        $orderColumnName = $this->determineOrderColumnName();

        return (int) $this->$orderColumnName === $this->getHighestOrderNumber();
    }

    public function isFirstInOrder(): bool
    {
        $orderColumnName = $this->determineOrderColumnName();

        return (int) $this->$orderColumnName === $this->getLowestOrderNumber();
    }
}
