<?php

namespace App\Modules\User\Providers;

use App\Modules\User\Repositories\Eloquent\UserRepository;
use App\Modules\User\Repositories\Interfaces\UserRepositoryInterface;
use App\Providers\AbstractModuleServiceProvider;

class UserServiceProvider extends AbstractModuleServiceProvider
{
    /**
     * The module namespace.
     *
     * @var string
     */
    protected $moduleNamespace = 'App\Modules\User\Controllers';

    /**
     * The module path.
     *
     * @var string
     */
    protected $modulePath = __DIR__ . '/..';

    /**
     * The routes files to be loaded.
     *
     * @var array
     */
    protected $routeFiles = [
        __DIR__ . '/../routes/api.php',
    ];

    /**
     * The migrations path.
     *
     * @var string
     */
    protected $migrationPath = __DIR__ . '/../database/migrations';

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
