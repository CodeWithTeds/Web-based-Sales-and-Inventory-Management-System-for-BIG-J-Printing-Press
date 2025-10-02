<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Providers\RepositoryServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            // Add activity logger middleware alias
            'activity' => \App\Http\Middleware\ActivityLogger::class,
        ]);
        // Apply activity logger to all web routes (it will only log for staff and client users)
        $middleware->appendToGroup('web', [\App\Http\Middleware\ActivityLogger::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withProviders([
        RepositoryServiceProvider::class,
    ])
    ->create();
