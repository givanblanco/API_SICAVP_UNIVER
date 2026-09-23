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
        

        $query = "SELECT
                    SE.ACADEMIC_TERM,
                    CASE WHEN CAST(GETDATE() AS date) BETWEEN CAL.START_DATE AND CAL.END_DATE   -- vigente
                        OR CAL.START_DATE > CAST(GETDATE() AS date)                        -- por iniciar
                    THEN 1 ELSE 0
                END AS ESTATUS
                FROM SECTIONS SE
                LEFT JOIN (
                    SELECT ACADEMIC_YEAR, ACADEMIC_TERM,
                        MIN(START_DATE) AS START_DATE,
                        MAX(END_DATE)   AS END_DATE
                    FROM ACADEMICCALENDAR
                    WHERE ACADEMIC_YEAR = '$year'
                    GROUP BY ACADEMIC_YEAR, ACADEMIC_TERM
                ) CAL
                ON CAL.ACADEMIC_YEAR = SE.ACADEMIC_YEAR
                AND CAL.ACADEMIC_TERM = SE.ACADEMIC_TERM
                WHERE SE.ACADEMIC_YEAR = '$year'
                AND SE.EVENT_STATUS = 'A'
                AND SE.CONTACT_HR_SESSION > 0
                AND SE.ADDS <> 0
                GROUP BY SE.ACADEMIC_TERM, CAL.START_DATE, CAL.END_DATE
                ORDER BY CAL.START_DATE;";

        $stmt = sqlsrv_query($conn, $query);
        
        if (!$stmt) {
            sqlsrv_close($conn);
            return null;
        }
        
        $schedules_acaterm = array();

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $schedules_acaterm[] = $row;
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        return $schedules_acaterm;
    }
    
    
}
?>