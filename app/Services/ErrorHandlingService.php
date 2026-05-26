<?php

namespace App\Services;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ErrorHandlingService
{
    /** @var array<int, string> */
    public const STATUS_VIEWS = [
        401 => 'errors.401',
        403 => 'errors.403',
        404 => 'errors.404',
        405 => 'errors.405',
        419 => 'errors.419',
        429 => 'errors.429',
        500 => 'errors.500',
        503 => 'errors.503',
    ];

    public static function resolveStatusCode(Throwable $exception): int
    {
        if ($exception instanceof AuthenticationException) {
            return 401;
        }

        if ($exception instanceof TokenMismatchException) {
            return 419;
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return 405;
        }

        if ($exception instanceof NotFoundHttpException) {
            return 404;
        }

        if ($exception instanceof TooManyRequestsHttpException) {
            return 429;
        }

        $code = (int) $exception->getCode();

        if ($code >= 400 && $code < 600) {
            return $code;
        }

        return 500;
    }

    public static function resolveView(int $statusCode): string
    {
        if (isset(self::STATUS_VIEWS[$statusCode]) && view()->exists(self::STATUS_VIEWS[$statusCode])) {
            return self::STATUS_VIEWS[$statusCode];
        }

        if ($statusCode >= 500) {
            return view()->exists('errors.500') ? 'errors.500' : 'errors.generic';
        }

        if ($statusCode >= 400) {
            return view()->exists('errors.generic') ? 'errors.generic' : 'errors.500';
        }

        return 'errors.generic';
    }

    public static function logError(Throwable $exception, ?Request $request = null): void
    {
        Log::error('Application Error', [
            'status' => self::resolveStatusCode($exception),
            'message' => $exception->getMessage(),
            'exception' => $exception::class,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
            'ip' => $request?->ip(),
            'user_id' => auth()->id(),
        ]);
    }

    public static function notifyAdmins(Throwable $exception, ?Request $request = null): void
    {
        $statusCode = self::resolveStatusCode($exception);

        if ($statusCode < 500) {
            return;
        }

        try {
            Log::critical('Critical application error', [
                'status' => $statusCode,
                'message' => $exception->getMessage(),
                'url' => $request?->fullUrl(),
                'user_id' => auth()->id(),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to record critical error notification', [
                'notification_error' => $e->getMessage(),
            ]);
        }
    }

    public static function getUserFriendlyMessage(Throwable $exception, ?int $statusCode = null): string
    {
        $statusCode ??= self::resolveStatusCode($exception);

        if ($exception instanceof HttpExceptionInterface && $exception->getMessage() !== '') {
            $message = $exception->getMessage();
            if (!str_contains($message, 'HttpException') && !str_contains($message, 'Symfony')) {
                return $message;
            }
        }

        return match ($statusCode) {
            401 => 'You must be signed in to access this page.',
            403 => 'You do not have permission to access this resource.',
            404 => 'The page you are looking for could not be found.',
            405 => 'This action is not allowed for the requested URL.',
            419 => 'Your session has expired. Please refresh and try again.',
            429 => 'Too many requests. Please wait and try again.',
            500 => 'An internal server error occurred. Please try again later.',
            503 => 'The service is temporarily unavailable. Please try again later.',
            default => $statusCode >= 500
                ? 'A server error occurred. Please try again later.'
                : 'The request could not be completed.',
        };
    }

    public static function shouldReportError(Throwable $exception): bool
    {
        $ignored = [
            NotFoundHttpException::class,
            MethodNotAllowedHttpException::class,
            AuthenticationException::class,
            TokenMismatchException::class,
        ];

        foreach ($ignored as $class) {
            if ($exception instanceof $class) {
                return false;
            }
        }

        return self::resolveStatusCode($exception) >= 500;
    }

    public static function renderResponse(Throwable $exception, Request $request)
    {
        if (config('app.debug')) {
            return null;
        }

        if ($exception instanceof ValidationException || $exception instanceof HttpResponseException) {
            return null;
        }

        if ($exception instanceof AuthenticationException && ! $request->expectsJson()) {
            return null;
        }

        $statusCode = self::resolveStatusCode($exception);
        $message = self::getUserFriendlyMessage($exception, $statusCode);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'status' => $statusCode,
            ], $statusCode);
        }

        if (self::shouldReportError($exception)) {
            self::logError($exception, $request);
        }

        self::notifyAdmins($exception, $request);

        $view = self::resolveView($statusCode);

        return response()->view($view, [
            'statusCode' => $statusCode,
            'message' => $message,
            'exception' => $exception,
        ], $statusCode);
    }
}
