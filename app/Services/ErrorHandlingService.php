<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ErrorHandlingService
{
    /**
     * Log error details for debugging
     */
    public static function logError(Throwable $exception, Request $request = null): void
    {
        $context = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => $request ? $request->fullUrl() : null,
            'method' => $request ? $request->method() : null,
            'ip' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->userAgent() : null,
            'user_id' => auth()->id(),
        ];

        Log::error('Application Error', $context);
    }

    /**
     * Send error notification to administrators
     */
    public static function notifyAdmins(Throwable $exception, Request $request = null): void
    {
        // Only send notifications for critical errors (500, 503)
        if ($exception->getCode() >= 500) {
            try {
                // You can implement email notifications here
                // Mail::to(config('mail.admin_email'))->send(new ErrorNotificationMail($exception, $request));
                
                // For now, just log it
                Log::critical('Critical Error Occurred', [
                    'exception' => $exception->getMessage(),
                    'url' => $request ? $request->fullUrl() : null,
                    'user_id' => auth()->id(),
                ]);
            } catch (Throwable $e) {
                Log::error('Failed to send error notification', [
                    'original_error' => $exception->getMessage(),
                    'notification_error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get user-friendly error message
     */
    public static function getUserFriendlyMessage(Throwable $exception): string
    {
        $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

        return match ($statusCode) {
            404 => 'The page you are looking for could not be found.',
            403 => 'You do not have permission to access this resource.',
            419 => 'Your session has expired. Please refresh the page and try again.',
            500 => 'An internal server error occurred. Please try again later.',
            503 => 'The service is temporarily unavailable. Please try again later.',
            default => 'An unexpected error occurred. Please try again or contact support.',
        };
    }

    /**
     * Check if error should be reported
     */
    public static function shouldReportError(Throwable $exception): bool
    {
        // Don't report certain exceptions
        $ignoredExceptions = [
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        ];

        foreach ($ignoredExceptions as $ignoredException) {
            if ($exception instanceof $ignoredException) {
                return false;
            }
        }

        return true;
    }
}
