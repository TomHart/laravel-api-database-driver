<?php

namespace TomHart\Database;


use Illuminate\Database\Connection;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\MockObject\Api;
use TomHart\Database\Database\ApiConnection;

class ApiDriverServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Connection::resolverFor('api', static function ($connection, $database, $prefix, $config) {
            if(app()->has(ApiConnection::class)){
                return app(ApiConnection::class);
            }

            return new ApiConnection($connection, $database, $prefix, $config);
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/api-database.php', 'api-database');
    }

    /**
     * Boot the service.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/api-database.php' => config_path('api-database.php'),
        ], 'config');
    }
}
