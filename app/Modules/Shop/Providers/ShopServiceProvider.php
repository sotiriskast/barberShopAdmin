<?php

namespace App\Modules\Shop\Providers;

use App\Modules\Shop\Repositories\Eloquent\ShopRepository;
use App\Modules\Shop\Repositories\Interfaces\ShopRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class ShopServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            ShopRepositoryInterface::class,
            ShopRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
