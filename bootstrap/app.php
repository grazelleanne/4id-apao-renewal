<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\ApplicationSession::class,
        ]);
        $middleware->alias(['account'=>\App\Http\Middleware\AccountAccess::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Database\QueryException $exception, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() && in_array((int) ($exception->errorInfo[1] ?? 0), [2002,2003,2006,2013], true)) {
                return response()->json(['success'=>false,'error'=>app()->environment('local')
                    ? 'The local database is unavailable. Start MySQL in XAMPP and try again.'
                    : 'The database is temporarily unavailable. Please try again later.'],503);
            }
        });
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $exception, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) return response()->json(['success'=>false,'message'=>'Session expired. Refresh and retry.'],419);
        });
    })->create();
