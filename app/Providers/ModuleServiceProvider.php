<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * All module service providers to be registered.
     *
     * @var array
     */
    protected $moduleProviders = [
        // We'll add module service providers here as we implement them
        // Example: \App\Modules\User\Providers\UserServiceProvider::class,
        \App\Modules\User\Providers\UserServiceProvider::class,
        \App\Modules\Barber\Providers\BarberServiceProvider::class,
        \App\Modules\Shop\Providers\ShopServiceProvider::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        // Register each module service provider
        foreach ($this->moduleProviders as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
