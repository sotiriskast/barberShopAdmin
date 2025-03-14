<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

abstract class AbstractModuleServiceProvider extends ServiceProvider
{
    /**
     * The module namespace.
     *
     * @var string
     */
    protected $moduleNamespace;

    /**
     * The module path.
     *
     * @var string
     */
    protected $modulePath;

    /**
     * The routes files to be loaded.
     *
     * @var array
     */
    protected $routeFiles = [];

    /**
     * The migrations path.
     *
     * @var string|null
     */
    protected $migrationPath = null;

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->routesAreCached() === false) {
            $this->loadRoutes();
        }

        if ($this->migrationPath) {
            $this->loadMigrationsFrom($this->migrationPath);
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Register bindings, classes, repositories, services etc.
    }

    /**
     * Load routes from module.
     */
    protected function loadRoutes(): void
    {
        foreach ($this->routeFiles as $file) {
            $this->loadRoutesFrom($file);
        }
    }
}
