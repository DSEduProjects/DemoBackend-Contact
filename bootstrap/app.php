<?php

use App\Exceptions\MailSendingException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: [
                '127.0.0.1',
                '::1',
            ],
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->api(append: [
            \App\Http\Middleware\LogHttpRequest::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (
            ValidationException $exception,
            Request $request
        ) {
            if (!$request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации.',
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (
            MailSendingException $exception,
            Request $request
        ) {
            return response()->json([
                'success' => false,
                'message' => "Сервис отправки писем временно недоступен."
            ], 503);
        });

        $exceptions->render(function (
            Throwable $exception,
            Request $request
        ) {
            if (!$request->is('api/*')) {
                return null;
            }

            if ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();

                return response()->json([
                    'success' => false,
                    'message' => match ($status) {
                        404 => 'Маршрут не найден.',
                        405 => 'HTTP-метод не поддерживается.',
                        429 => 'Слишком много запросов. Попробуйте позже.',
                        default => 'Ошибка HTTP-запроса.',
                    },
                ], $status);
            }

            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка сервера.',
            ], 500);
        });
    })->create();
