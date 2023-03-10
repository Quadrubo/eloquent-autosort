<?php

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
