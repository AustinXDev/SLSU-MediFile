<?php

namespace App\Support;

use InvalidArgumentException;
use Throwable;

final class ApiResponse
{
    public static function sendError(Throwable $error, string $fallbackMessage): void
    {
        $code = (int) $error->getCode();
        if (in_array($code, [401, 403, 429], true)) {
            $statusCode = $code;
            $message = $error->getMessage();
        } elseif ($error instanceof InvalidArgumentException) {
            $statusCode = 400;
            $message = $error->getMessage();
        } else {
            $statusCode = 500;
            $message = $fallbackMessage;
        }

        if (headers_sent()) {
            exit;
        }

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'message' => $message], JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }
}
