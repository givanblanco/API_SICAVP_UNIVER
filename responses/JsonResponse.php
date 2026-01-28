<?php
class JsonResponse {
    public static function success($data, $message = 'Success') {
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }
    
    public static function error($message, $code = 500) {
        http_response_code($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit();
    }
}
?>