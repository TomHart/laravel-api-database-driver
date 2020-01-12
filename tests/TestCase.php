<?php

namespace TomHart\Database\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Foundation\Application;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use TomHart\Database\ApiDriverServiceProvider;
use TomHart\Database\Database\ApiConnection;
use TomHart\Database\Database\Query\Grammars\ApiGrammar;

abstract class TestCase extends OrchestraTestCase
{

    public const HOST = 'https://api.example.com';

    protected function getPackageProviders($app)
    {
        return [ApiDriverServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'api',
            'database' => self::HOST
        ]);
    }

    /**
     * Mock Guzzle responses.
     * @param array $requests
     * @return MockObject
     */
    protected function mockGuzzle(array $requests = [])
    {

        // Create a mock and queue two responses.
        $mock = new MockHandler($requests);

        $handlerStack = HandlerStack::create($mock);

        $mockedGuzzle = new Client(['handler' => $handlerStack]);

        $this->app->bind(Client::class, static function () use ($mockedGuzzle, $handlerStack) {
            return $mockedGuzzle;
        });

        return $mockedGuzzle;
    }


    /**
     * Mock a paginated response.
     * @param array $responseData
     */
    protected function setPaginatedResponse(array $responseData = [])
    {
        $mock = $this->mock(Client::class);
        $chunked = array_chunk($responseData, 15);
        $responses = [];
        foreach($chunked as $index => $chunk) {
            $responses[] = new Response(200, [], json_encode([
                    'current_page' => ($index + 1),
                    'data' => $chunk,
                    'first_page_url' => '',
                    'from' => 1 + (15 * $index),
                    'last_page' => ceil(count($responseData) / 15),
                    'last_page_url' => 1,
                    'next_page_url' => 1,
                    'path' => 1,
                    'per_page' => $index * 15,
                    'prev_page_url' => '',
                    'to' => 15 + (15 * $index),
                    'total' => count($chunked)
                ]));
        }

        $mock
            ->shouldReceive('request')
            ->andReturn(...$responses);
    }


    /**
     * @return ApiConnection
     */
    protected function getMockedApiConnection(): MockInterface
    {
        /** @var ApiConnection|MockInterface $mock */
        $mock = $this->mock(ApiConnection::class);
        $mock->shouldReceive('getQueryGrammar')->andReturn(new ApiGrammar());
        $mock->shouldReceive('getPostProcessor')->andReturn(new Processor());
        $mock->shouldReceive('setEventDispatcher', 'setReconnector');
        $mock->shouldReceive('query')->andReturn(new QueryBuilder($mock));
        $mock->shouldReceive('getName');

        return $mock;
    }
}
