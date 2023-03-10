# Eloquent Autosort

[![Latest Version on Packagist](https://img.shields.io/packagist/v/quadrubo/eloquent-autosort.svg?style=flat-square)](https://packagist.org/packages/quadrubo/eloquent-autosort)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/quadrubo/eloquent-autosort/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/quadrubo/eloquent-autosort/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/quadrubo/eloquent-autosort/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/quadrubo/eloquent-autosort/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/quadrubo/eloquent-autosort.svg?style=flat-square)](https://packagist.org/packages/quadrubo/eloquent-autosort)

This package provides sortable behaviour for Eloquent models using a trait.

The package is based on [spatie/eloquent-sortable](https://github.com/spatie/eloquent-sortable).

## Features

-   Automatic sorting on create
-   Automatic sorting on update
-   Automatic sorting on delete
-   Grouping with unlimited Columns

## Installation

This package can be installed through Composer.

```bash
composer require quadrubo/eloquent-autosort
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="eloquent-autosort-config"
```

This is the content of the file that will be published in `config/eloquent-autosort.php`

```php
return [
    /*
     * Which column will be used as the order column.
     */
    'order_column_name' => 'order_column',

    /*
     * Define if the models should sort when creating.
     * When true, the package will automatically assign the highest order number to a new model.
     */
    'sort_when_creating' => true,

    /**
     * Define if the models should sort when updating.
     * When true, the package will automatically update the order of both the old and new group.
     */
    'sort_when_updating' => true,

    /**
     * Define if the models should sort when deleting.
     * When true, the package will fix the order within the current group when deleting.
     */
    'sort_when_deleting' => true,

    /**
     * Define the columns the model should be grouped by.
     * You can leave this empty and implement your own solution by overwriting
     * `buildSortQuery` and `hasChangedGroupAttributes`.
     */
    'groups' => [],
];
```

## Usage

To add sortable behaviour to your model you must:

1. Implement the `Quadrubo\EloquentAutosort\Sortable` interface.
2. Use the trait `Quadrubo\EloquentAutosort\SortableTrait`.

### Example

```php
use Quadrubo\EloquentSortable\Sortable;
use Quadrubo\EloquentSortable\SortableTrait;

class MyModel extends Model implements Sortable
{
    use SortableTrait;

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
        'sort_when_updating' => true,
        'sort_when_deleting' => true,
        'groups' => [],
    ];

    // ...
}
```

If you don't set a value `$sortable['order_column_name']` the package will assume that your order column name will be named `order_column`.

If you don't set a value `$sortable['sort_when_creating']` the package will automatically assign the highest order number to a new model.

If you don't set a value `$sortable['sort_when_updating']` the package will automatically repair the order on update.

If you don't set a value `$sortable['sort_when_deleting']` the package will automatically repair the order on delete.

If you don't set a value `$sortable['groups']` groups will not be used.

Assuming that the db-table for `MyModel` is empty:

```php
$myModel = new MyModel();
$myModel->save(); // order_column for this record will be set to 1

$myModel = new MyModel();
$myModel->save(); // order_column for this record will be set to 2

$myModel = new MyModel();
$myModel->save(); // order_column for this record will be set to 3


// the trait also provides the ordered query scope.
// note that when you use grouping, you should
// build the query first for this to be useful.
$orderedRecords = MyModel::ordered()->get();
```

You can set a new order for all the records using the `setNewOrder`-method

```php
/**
 * the record for model id 3 will have order_column value 1
 * the record for model id 1 will have order_column value 2
 * the record for model id 2 will have order_column value 3
 */
MyModel::setNewOrder([3,1,2]);
```

Optionally you can pass the starting order number as the second argument.

```php
/**
 * the record for model id 3 will have order_column value 11
 * the record for model id 1 will have order_column value 12
 * the record for model id 2 will have order_column value 13
 */
MyModel::setNewOrder([3,1,2], 10);
```

To sort using a column other than the primary key, use the `setNewOrderByCustomColumn`-method.

```php
/**
 * the record for model uuid '7a051131-d387-4276-bfda-e7c376099715' will have order_column value 1
 * the record for model uuid '40324562-c7ca-4c69-8018-aff81bff8c95' will have order_column value 2
 * the record for model uuid '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1' will have order_column value 3
 */
MyModel::setNewOrderByCustomColumn('uuid', [
   '7a051131-d387-4276-bfda-e7c376099715',
   '40324562-c7ca-4c69-8018-aff81bff8c95',
   '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1'
]);
```

As with `setNewOrder`, `setNewOrderByCustomColumn` will also accept an optional starting order argument.

```php
/**
 * the record for model uuid '7a051131-d387-4276-bfda-e7c376099715' will have order_column value 10
 * the record for model uuid '40324562-c7ca-4c69-8018-aff81bff8c95' will have order_column value 11
 * the record for model uuid '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1' will have order_column value 12
 */
MyModel::setNewOrderByCustomColumn('uuid', [
   '7a051131-d387-4276-bfda-e7c376099715',
   '40324562-c7ca-4c69-8018-aff81bff8c95',
   '5dc4d0f4-0c88-43a4-b293-7c7902a3cfd1'
], 10);
```

You can also move a model up or down with these methods:

```php
$myModel->moveOrderDown();
$myModel->moveOrderUp();
```

You can also move a model to the first or last position:

```php
$myModel->moveToStart();
$myModel->moveToEnd();
```

You can also move a model to a specific or the new position:

```php
$myModel->moveToPosition(3);

// moves to a position using the value in your order_column attribute
$myModel->moveToNewPosition();
```

You can determine whether an element is first or last in order:

```php
$myModel->isFirstInOrder();
$myModel->isLastInOrder();
```

You can swap the order of two models:

```php
MyModel::swapOrder($myModel, $anotherModel);
```

### Grouping

If your model/table has one or multiple grouping fields (usually a foreign key): `id, `**`user_id`**`, title, order_column` and you'd like the package to take it into considerations, you cann add the grouping fields to the `$sortable['groups']` array.

```php
public $sortable = [
    'groups' => [
        'user_id',
    ],
];
```

This will restrict the calculations to the fields in the array.

### Replacing the groups functionality

If you don't like the way the groups functionality is implemented, you can easily make your own.

1. Overwrite the `buildSortQuery` method to change the behaviour of how the query is build.
2. Overwrite the `hasChangedGroupAttributes` method to provide a way for the package to know when it should resort.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Quadrubo](https://github.com/Quadrubo)
-   [All Contributors](../../contributors)

Thanks to the [Spatie/Eloquent-Sortable Team](https://github.com/spatie/eloquent-sortable) for the base package functionality!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
