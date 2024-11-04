// src/Utils/Response.php
<?php
namespace App\Utils;

class Response {
    public static function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function error($message, $statusCode = 400) {
        return self::json(['error' => $message], $statusCode);
    }

    public static function success($data, $message = 'Success') {
        return self::json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
    }
}