<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Custom rendering for API responses
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Resource not found',
                    'message' => $e->getMessage() ?: 'The requested resource was not found',
                ], 404);
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'You must be logged in to access this resource',
                ], 401);
            }
        });

        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => $e->getMessage() ?: 'You do not have permission to access this resource',
                ], 403);
            }
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => 'Validation failed',
                    'message' => 'The given data was invalid',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Generic exception handler for API
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') && !($e instanceof ValidationException)) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                return response()->json([
                    'error' => class_basename($e),
                    'message' => $e->getMessage() ?: 'An error occurred',
                    'trace' => config('app.debug') ? $e->getTrace() : null,
                ], $statusCode);
            }
        });
    }
}
