<?php

namespace Quadrubo\EloquentAutosort\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            //
        ];
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('dummies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('custom_column_sort');
            $table->integer('first_group')->nullable();
            $table->string('second_group')->nullable();
            $table->integer('order_column');
        });
    }

    protected function setupDummies()
    {
        collect(range(1, 20))->each(function (int $i) {
            Dummy::create([
                'name' => $i,
                'custom_column_sort' => rand(),
            ]);
        });
    }

    protected function setupDummiesWithGroups()
    {
        config([
            'eloquent-sortable.groups' => [
                'first_group',
                'second_group',
            ],
        ]);

        collect(range(1, 5))->each(function (int $i) {
            Dummy::create([
                'name' => $i,
                'custom_column_sort' => rand(),
            ]);
        });

        collect(range(1, 5))->each(function (int $i) {
            Dummy::create([
                'name' => $i,
                'custom_column_sort' => rand(),
                'first_group' => 1,
            ]);
        });

        collect(range(1, 5))->each(function (int $i) {
            Dummy::create([
                'name' => $i,
                'custom_column_sort' => rand(),
                'first_group' => 2,
                'second_group' => 'a',
            ]);
        });

        collect(range(1, 5))->each(function (int $i) {
            Dummy::create([
                'name' => $i,
                'custom_column_sort' => rand(),
                'first_group' => 2,
                'second_group' => 'b',
            ]);
        });
    }

    protected function setUpSoftDeletes()
    {
        $this->app['db']->connection()->getSchemaBuilder()->table('dummies', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    protected function newOneOfEachDummy($classType = Dummy::class)
    {
        return [
            $this->newEmptyDummy($classType),
            $this->newGroupDummy($classType),
            $this->newMultiGroupOneDummy($classType),
            $this->newMultiGroupTwoDummy($classType),
        ];
    }

    protected function getRandomOneOfEachDummy($classType = Dummy::class)
    {
        return [
            $this->getRandomEmptyDummy($classType),
            $this->getRandomGroupDummy($classType),
            $this->getRandomMultiGroupOneDummy($classType),
            $this->getRandomMultiGroupTwoDummy($classType),
        ];
    }

    protected function newEmptyDummy($classType = Dummy::class)
    {
        return $this->getDummy($classType);
    }

    protected function getRandomEmptyDummy($classType = Dummy::class)
    {
        return $classType::where('first_group', null)->where('second_group', null)->inRandomOrder()->firstOrFail();
    }

    protected function newGroupDummy($classType = Dummy::class)
    {
        return $this->getDummy($classType, 1);
    }

    protected function getRandomGroupDummy($classType = Dummy::class)
    {
        return $classType::where('first_group', 1)->where('second_group', null)->inRandomOrder()->firstOrFail();
    }

    protected function newMultiGroupOneDummy($classType = Dummy::class)
    {
        return $this->getDummy($classType, 2, 'a');
    }

    protected function getRandomMultiGroupOneDummy($classType = Dummy::class)
    {
        return $classType::where('first_group', 2)->where('second_group', 'a')->inRandomOrder()->firstOrFail();
    }

    protected function newMultiGroupTwoDummy($classType = Dummy::class)
    {
        return $this->getDummy($classType, 2, 'b');
    }

    protected function getRandomMultiGroupTwoDummy($classType = Dummy::class)
    {
        return $classType::where('first_group', 2)->where('second_group', 'b')->inRandomOrder()->firstOrFail();
    }

    private function getDummy($classType = Dummy::class, $firstGroup = null, $secondGroup = null)
    {
        $dummy = new $classType();

        if (! is_null($firstGroup)) {
            $dummy->first_group = $firstGroup;
        }

        if (! is_null($secondGroup)) {
            $dummy->second_group = $secondGroup;
        }

        return $dummy;
    }
}
