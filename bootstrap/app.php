<?php

use App\Http\Middleware\VerifyIntegrationKey;
use App\Support\IntegrationResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'integration.key' => VerifyIntegrationKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Every error under /api/integration/* uses the B5 format:
        // {"success":false,"message":"...","errors":{...}}
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/integration/*')) {
                return null; // normal Laravel handling for everything else
            }

            if ($e instanceof ValidationException) {
                return IntegrationResponse::error('Validation failed', $e->errors(), 422);
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $status === 404 ? 'Not found' : ($e->getMessage() ?: 'HTTP error');

                return IntegrationResponse::error($message, [], $status);
            }

            // Laravel already wrote the error to the log before this runs.
            return IntegrationResponse::error(
                config('app.debug') ? $e->getMessage() : 'Server error',
                [],
                500
            );
        });
    })->create();
