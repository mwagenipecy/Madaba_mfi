<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'otp.verified' => \App\Http\Middleware\OtpVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom error page handling
        $exceptions->render(function (Throwable $e, Request $request) {
            // Log error for debugging
            if (config('app.debug') || \App\Services\ErrorHandlingService::shouldReportError($e)) {
                \App\Services\ErrorHandlingService::logError($e, $request);
            }
            
            // Notify admins for critical errors
            \App\Services\ErrorHandlingService::notifyAdmins($e, $request);
            
            // Handle different HTTP status codes with custom views
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->view('errors.404', [], 404);
            }
            
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                $statusCode = $e->getStatusCode();
                
                switch ($statusCode) {
                    case 403:
                        return response()->view('errors.403', [], 403);
                    case 419:
                        return response()->view('errors.419', [], 419);
                    case 500:
                        return response()->view('errors.500', [], 500);
                    case 503:
                        return response()->view('errors.503', [], 503);
                }
            }
            
            // For any other errors, show generic error page
            if (!config('app.debug')) {
                return response()->view('errors.generic', ['exception' => $e], 500);
            }
            
            return null; // Let Laravel handle debug mode
        });
    })->create();
