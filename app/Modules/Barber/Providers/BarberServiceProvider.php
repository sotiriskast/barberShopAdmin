<?php

namespace App\Modules\Barber\Providers;

use App\Modules\Barber\Repositories\Eloquent\BarberRepository;
use App\Modules\Barber\Repositories\Interfaces\BarberRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class BarberServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register repositories
        $this->app->bind(BarberRepositoryInterface::class, BarberRepository::class);

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/barber.php', 'barber'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Load routes with api/v1 prefix
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/barber.php' => config_path('barber.php'),
        ], 'barber-config');
    }
}
