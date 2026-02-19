<?php
require_once './middleware/CorsMiddleware.php';
require_once './controllers/SchedulesOperationController.php';
require_once './controllers/SchedulesPayrollController.php';
require_once './controllers/SchedulesAcatermController.php';
require_once './responses/JsonResponse.php';

// Aplicar CORS
CorsMiddleware::handle();

// Obtener método y ruta
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// !importante cambiar remplazo de URL dependiendo dónde se están generando pruebas
$uri = str_replace('/ProdNG/SICAVP/API-UNIVER', '', $uri); // Producción
//$uri = str_replace('/DevNG/SICAVP/API_SICAVP_UNIVER', '', $uri); // Dev

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
            $controller = new SchedulesPayrollController();  // ✅ CORRECTO: Controlador
            $controller->getSchedulesPayroll();
        }
        break;

        case '/Schedules/Acaterm':
            if ($method === 'GET') {
                $controller = new SchedulesAcatermController();  // ✅ CORRECTO: Controlador
                $controller->getSchedulesAcaterm();
            }
            break;
    
    
    default:
        JsonResponse::error('Endpoint no encontrado', 404);
        break;
}
?>