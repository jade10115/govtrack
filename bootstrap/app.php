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
        // Fix CORS right here for Laravel 11
        $middleware->statefulApi();
        
        $middleware->validateCsrfTokens(except: [
            'api/*', // Bypasses CSRF tokens for all API routes since we use Bearer Tokens
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();