<?php

declare(strict_types=1);

namespace Jcuna\ApiKeys;

use Jcuna\ApiKeys\Cli\ApiCommands;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register package's services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ApiCommands::class
            ]);
        }
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->app->routeMiddleware(['api.keys' => ApiMiddleware::class]);
    }
}
