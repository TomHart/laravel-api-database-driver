<?php

namespace TomHart\Database\Tests;

use GuzzleHttp\Psr7\Response;
use TomHart\Database\Database\ApiConnection;

class ApiConnectionTest extends TestCase
{

    private function getDb(): ApiConnection
    {
        return $this->app->make('db')->connection();
    }


    /**
     * Test default grammar can be set from the database config.
     */
    public function testSettingDefaultGrammar(): void
    {
        $default = [
            'abc' => 'def'
        ];

        $this->app['config']->set('database.connections.testbench', [
            'driver' => 'api',
            'database' => self::HOST,
            'query' => $default
        ]);

        $db = $this->getDb();

        $this->assertSame($default, $db->getQueryGrammar()->getDefaultQueryString());
    }

    /**
     * Testing a normal select works.
     */
    public function testSimpleSelect(): void
    {
        $db = $this->getDb();
        $data = ['abc' => 'def'];

        $this->mockGuzzle([new Response(200, [], json_encode($data))]);

        $results = $db->select('/test');
        $this->assertSame($data, $results);
    }

    /**
     * Testing selecting data from a sub key..
     */
    public function testSubKeySelect(): void
    {
        $db = $this->getDb();
        $data = ['data' => ['abc' => 'def']];

        $this->mockGuzzle([
            new Response(200, [], json_encode($data))
        ]);

        $results = $db->select('/test@data');
        $this->assertSame($data['data'], $results);
    }


    public function testPagination(): void
    {
        $db = $this->getDb();

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

        $results = $db->select('/test@data');
        $this->assertCount(30, $results);
    }
}