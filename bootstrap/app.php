<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // La API siempre responde JSON, incluso si el cliente no manda Accept.
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Formato de error estándar: {message, errors?}.
        $exceptions->render(function (ValidationException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'message' => 'Los datos enviados no son válidos.',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json(['message' => 'No autenticado.'], 401);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json(['message' => 'No autorizado para este recurso.'], 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json(['message' => 'Recurso no encontrado.'], 404);
        });

        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json(['message' => 'Demasiadas solicitudes. Intenta de nuevo en un momento.'], 429);
        });
    })->create();
