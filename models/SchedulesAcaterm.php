<?php
require_once dirname(__DIR__) . '/config/connect.php';

class SchedulesAcaterm {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Obtiene los períodos académicos de un año específico
     * @param string $academicYear Año académico único (ej: 2025)
     * @return string|null Array de períodos académicos o null si hay error
     */
    
    public function getSchedulesAcaterm($academicYear) {
        $conn = $this->db->connPwC();
        
        if (!$conn) {
            return null;
        }
        
        // Validar que los parámetros no estén vacíos
        if (empty($academicYear)) {
            return null;
        }
        
        // Formatear arrays a strings SQL (entrecomillados)
        // ✅ Usar parámetro preparado para evitar SQL injection
        // Escapar el valor para SQL Server
        $year = $academicYear;
        

        $query = "SELECT DISTINCT ACADEMIC_TERM FROM SECTIONS WHERE ACADEMIC_YEAR = '$year' ORDER BY ACADEMIC_TERM";

        $stmt = sqlsrv_query($conn, $query);
        
        if (!$stmt) {
            sqlsrv_close($conn);
            return null;
        }
        
        $schedules_acaterm = array();

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $schedules_acaterm[] = $row['ACADEMIC_TERM'];
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        return implode(', ', $schedules_acaterm);
    }
    
    
}
?>