<?php

class Response
{
    public static function json($data, int $statusCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function error(string $message, int $statusCode = 400)
    {
        self::json(['success' => false, 'message' => $message], $statusCode);
    }
}
