<?php

namespace App\Foundation\Exception;

use App\Foundation\HTTP\Response;
use Throwable;

class ExceptionHandler
{
    public static function handleException(Throwable $exception): void
    {
        $exception_body = [
            'time' => date('Y-m-d H:i:s'),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
        ];

        $response = new Response($exception_body, (int) $exception->getCode());

        $response->send();

        exit(1);
    }

    public static function handleError(
        int    $error_level,
        string $message,
        string $file = null,
        int    $line = null,
        array  $context = null
    ): bool
    {
        $error_body = [
            'time' => date('Y-m-d H:i:s'),
            'level' => $message,
            'code' => $error_level,
            'file' => $file,
            'line' => $line,
            'context' => $context,
        ];

        $response = new Response($error_body, 400);

        $response->send();

        return true;
    }
}
