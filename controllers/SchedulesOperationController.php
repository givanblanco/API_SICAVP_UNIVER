<?php
require_once './models/SchedulesOperation.php';
require_once './responses/JsonResponse.php';

class SchedulesOperationController {
    
    public function getSchedulesOperation() {
        // Validar que los parámetros requeridos estén presentes
        if (!isset($_GET['academic_year']) || !isset($_GET['academic_term'])) {
            JsonResponse::error('Los parámetros academic_year y academic_term son requeridos', 400);
        }
        
        $academicYear = $_GET['academic_year'];
        $academicTerm = $_GET['academic_term'];
        
        // Validar que no estén vacíos
        if (empty($academicYear) || empty($academicTerm)) {
            JsonResponse::error('Los parámetros academic_year y academic_term no pueden estar vacíos', 400);
        }
        
        // Validar y sanitizar academic_year (años separados por comas)
        $years = $this->validateAndSanitizeYears($academicYear);
        if ($years === false) {
            JsonResponse::error('academic_year contiene valores inválidos. Debe contener solo números separados por comas', 400);
        }
        
        // Validar y sanitizar academic_term (períodos separados por comas)
        $terms = $this->validateAndSanitizeTerms($academicTerm);
        if ($terms === false) {
            JsonResponse::error('academic_term contiene valores inválidos. Ejemplo válido: 1C,1CMA,1CMB', 400);
        }
        
        // Instanciar modelo y pasar los parámetros
        $model = new SchedulesOperation();
        $schedules_operation = $model->getSchedulesOperation($years, $terms);
        
        if ($schedules_operation === null) {
            JsonResponse::error('Error al conectar con la base de datos', 500);
        }
        
        JsonResponse::success($schedules_operation, 'Horarios de operación SICAVP obtenidos correctamente');
    }
    
    /**
     * Valida y sanitiza los años académicos
     * @param string $yearInput Años separados por comas (ej: 2025,2026)
     * @return array|false Array de años válidos o false si hay error
     */
    private function validateAndSanitizeYears($yearInput) {
        $years = explode(',', $yearInput);
        $validYears = [];
        
        foreach ($years as $year) {
            $year = trim($year);
            
            // Validar que sea un número de 4 dígitos
            if (!preg_match('/^\d{4}$/', $year)) {
                return false;
            }
            
            $validYears[] = $year;
        }
        
        return array_unique($validYears); // Eliminar duplicados
    }
    
    /**
     * Valida y sanitiza los períodos académicos
     * @param string $termInput Períodos separados por comas (ej: 1C,1CMA,1CMB)
     * @return array|false Array de períodos válidos o false si hay error
     */
    private function validateAndSanitizeTerms($termInput) {
        $terms = explode(',', $termInput);
        $validTerms = [];
        
        foreach ($terms as $term) {
            $term = trim($term);
            
            // Validar que contenga solo números, letras y no esté vacío
            // Formato esperado: 1C, 1CMA, 1CMB, etc.
            if (empty($term) || !preg_match('/^[a-zA-Z0-9]{1,10}$/', $term)) {
                return false;
            }
            
            $validTerms[] = strtoupper($term); // Convertir a mayúsculas para consistencia
        }
        
        return array_unique($validTerms); // Eliminar duplicados
    }
}
?>