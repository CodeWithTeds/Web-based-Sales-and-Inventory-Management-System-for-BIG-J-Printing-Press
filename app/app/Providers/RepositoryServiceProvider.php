<?php

namespace App\Providers;

use App\Repositories\BaseRepositoryInterface;
use App\Repositories\BaseRepository;
use App\Repositories\MaterialRepositoryInterface;
use App\Repositories\MaterialRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(MaterialRepositoryInterface::class, MaterialRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}