<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function(){
            Route::middleware('web')->group(base_path('routes/home_route.php'));
            Route::middleware('web')->group(base_path('routes/user_route.php'));
            Route::middleware('web')->group(base_path('routes/core_route.php'));
            Route::middleware('web')->group(base_path('routes/vendor_route.php'));
            Route::middleware('web')->group(base_path('routes/distributor_route.php'));
            Route::middleware('web')->group(base_path('routes/item_indent_route.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
 // Global middleware (like StartSession, VerifyCsrfToken, etc.)
     $middleware->group('web', [
        \Illuminate\Cookie\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class, 
    ]);

     // Route middleware (like auth, permission, etc.)
    $middleware->alias([
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
