<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Enable CORS for API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Trust all proxies (required for shared hosting/load balancers)
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
            'administration' => \App\Http\Middleware\AdministratorMiddleware::class,
            'permit' => \App\Http\Middleware\PermissionMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'online' => \App\Http\Middleware\OnlineCheckerMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'track.visitors' => \App\Http\Middleware\TrackVisitors::class,
        ]);
        
        // Apply visitor tracking middleware to web routes
        $middleware->web(append: [
            \App\Http\Middleware\TrackVisitors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
