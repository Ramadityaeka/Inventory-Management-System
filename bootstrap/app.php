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
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
        
        // Replace default CSRF middleware
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );
        
        // Redirect unauthenticated users to login
        $middleware->redirectGuestsTo('/login');
        
        // Redirect authenticated users trying to access login/register
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom exception handling for session errors
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Handle CSRF token mismatch (session errors)
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Session expired or invalid CSRF token. Please refresh the page and try again.'], 419);
            }

            return redirect()->back()->withErrors(['session' => 'Session expired. Please try again.'])->withInput();
        });

        // You can add more custom handlers here for other session-related exceptions
        // For example, handling authentication exceptions or other session issues
    })->create();
