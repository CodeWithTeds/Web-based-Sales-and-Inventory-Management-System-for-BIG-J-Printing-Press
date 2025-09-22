<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\App\Repositories\ProductRepositoryInterface::class, \App\Repositories\ProductRepository::class);
        $this->app->bind(\App\Repositories\MaterialRepositoryInterface::class, \App\Repositories\MaterialRepository::class);
        // Removed CheckoutRepositoryInterface binding
        $this->app->bind(\App\Repositories\SupplierRepositoryInterface::class, \App\Repositories\SupplierRepository::class);
    }

    public function boot(): void
    {
        //
    }
}