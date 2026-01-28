<?php
require_once './middleware/CorsMiddleware.php';
require_once './controllers/SchedulesOperationController.php';
require_once './controllers/SchedulesPayrollController.php';
require_once './responses/JsonResponse.php';

// Aplicar CORS
CorsMiddleware::handle();

// Obtener método y ruta
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/api', '', $uri);

// Enrutamiento
switch ($uri) {
    case '/Schedules/Operation':
        if ($method === 'GET') {
            $controller = new SchedulesOperationController();
            $controller->getSchedulesOperation();
        }
        break;
    
    case '/Schedules/Payroll':
        if ($method === 'GET') {
            $controller = new SchedulesPayroll();
            $controller->getSchedulesPayroll();
        }
        break;
    
    
    default:
        JsonResponse::error('Endpoint no encontrado', 404);
        break;
}
?>