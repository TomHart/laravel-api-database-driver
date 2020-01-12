<?php

namespace TomHart\Database\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EloquentTest extends TestCase
{

    /**
     * Test the JSON returned is hydrated into the models.
     */
    public function testSimpleSelect(): void
    {
        $this->setPaginatedResponse([['id' => 1]]);

        $models = MyModel::all();

        $this->assertInstanceOf(Collection::class, $models);
        $this->assertInstanceOf(MyModel::class, $models[0]);
        $this->assertEquals(1, $models[0]->id);
    }

    /**
     * Test adding limit adds ?limit to the query string.
     */
    public function testLimitedSelect(): void
    {
        $mock = $this->getMockedApiConnection();

        $mock->shouldReceive('select')->with('/my_models?limit=5', [], true);

        MyModel::limit(5)->get();
    }

    /**
     * Testing a select with a where clause
     */
    public function testWhereSelect(): void
    {
        $this
            ->getMockedApiConnection()
            ->shouldReceive('select')
            ->with('/my_models?name=test', ['test'], true);

        MyModel::where('name', 'test')->get();
    }

    /**
     * Testing first limits to 1
     */
    public function testFirstLimitsToOne(): void
    {
        $mock = $this->getMockedApiConnection();

        $mock->shouldReceive('select')->with('/my_models?limit=1', [], true);

        MyModel::first();
    }

    /**
     * Testing models are hydrated from the response
     */
    public function testModelsHydratedFromResponse(): void
    {
        $this->setPaginatedResponse([['abc' => 'def']]);

        $model = MyModel::first();
        $this->assertSame('def', $model->abc);
    }


    public function testPaginatedModels(): void
    {
        $this->setPaginatedResponse([
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => 5],
            ['id' => 6],
            ['id' => 7],
            ['id' => 8],
            ['id' => 9],
            ['id' => 10],
            ['id' => 11],
            ['id' => 12],
            ['id' => 13],
            ['id' => 14],
            ['id' => 15],
            ['id' => 16],
            ['id' => 17],
            ['id' => 18],
            ['id' => 19],
            ['id' => 20],
            ['id' => 21],
            ['id' => 22],
            ['id' => 23],
            ['id' => 24],
            ['id' => 25],
            ['id' => 26],
            ['id' => 27],
            ['id' => 28],
            ['id' => 29],
            ['id' => 30]
        ]);

        $models = MyModel::all();

        $this->assertCount(30, $models);
    }
}

/**
 * Class MyModel
 * @package TomHart\Database\Tests
 * @mixin Builder
 */
class MyModel extends Model
{

}