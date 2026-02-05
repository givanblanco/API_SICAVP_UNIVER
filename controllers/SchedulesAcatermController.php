<?php
require_once './models/SchedulesAcaterm.php';
require_once './responses/JsonResponse.php';

class SchedulesAcatermController {
    
    public function getSchedulesAcaterm() {
        // Validar que los parámetros requeridos estén presentes
        if (!isset($_GET['academic_year'])) {
            JsonResponse::error('El parámetro academic_year es obligatorio', 400);
        }
        
        $academicYear = $_GET['academic_year'];

        
        // Validar que no estén vacíos
        if (empty($academicYear) ) {
            JsonResponse::error('El parámetro academic_year no puede estar vacío', 400);
        }
        
        // Validar y sanitizar academic_year (años separados por comas)
        $year = $this->validateAndSanitizeYear($academicYear);
        if ($year === false) {
            JsonResponse::error('academic_year contiene valores inválidos. Debe contener solo años', 400);
        }
        
        // Instanciar modelo y pasar los parámetros
        $model = new SchedulesAcaterm();
        $schedules_Acaterm = $model->getSchedulesAcaterm($year);
        
        if ($schedules_Acaterm === null) {
            JsonResponse::error('Error al conectar con la base de datos', 500);
        }

         //Si no hay períodos, retornar string vacío
         if (empty($schedules_Acaterm)) {
            $schedules_Acaterm = 'No hay periodos académicos disponibles para el año ' . $year;
        }
        
        JsonResponse::success($schedules_Acaterm, 'Periodos académicos obtenidos correctamente');
    }
    
    /**
     * Valida y sanitiza los años académicos
     * @param string $yearInput Años separados por comas (ej: 2025,2026)
     * @return string|false Array de años válidos o false si hay error
     */
    private function validateAndSanitizeYear($yearInput) {
        $year = trim($yearInput);
        
        // Validar que sea un número de 4 dígitos
        if (!preg_match('/^\d{4}$/', $year)) {
            return false;
        }
        
        return $year;
    }

}
?>