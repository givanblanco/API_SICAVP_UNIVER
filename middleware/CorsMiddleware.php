<?php
class CorsMiddleware {
    public static function handle() {
        // Configura aquí tus dominios permitidos
        header('Access-Control-Allow-Origin: *'); // Cambia '*' por tu dominio específico
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json; charset=UTF-8');
        
        // Manejo de preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
}
?>