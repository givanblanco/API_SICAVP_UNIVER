<?php
require_once dirname(__DIR__) . '/config/connect.php';

class SchedulesPayroll {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getSchedulesPayroll($academicYears = [], $academicTerms = []) {
        $conn = $this->db->connPwC();
        
        if (!$conn) {
            return null;
        }
        
        // Validar que los parámetros no estén vacíos
        if (empty($academicYears) || empty($academicTerms)) {
            return null;
        }
        
        // Formatear arrays a strings SQL (entrecomillados)
        $yearList = "'" . implode("','", $academicYears) . "'";
        $termList = "'" . implode("','", $academicTerms) . "'";

        $query = "SELECT DISTINCT 
                ROW_NUMBER() OVER(ORDER BY LTRIM(RTRIM(ISNULL(PE.LAST_NAME,'')+' '+ISNULL(PE.Last_Name_Prefix,'')+' '+ISNULL(PE.FIRST_NAME,'')+' '+ISNULL(PE.MIDDLE_NAME,''))) ) PK
                , PE.PEOPLE_CODE_ID
                , PE.PREV_GOV_ID
                , UPPER(LTRIM(RTRIM(ISNULL(PE.LAST_NAME,'')+' '+ISNULL(PE.Last_Name_Prefix,'')+' '+ISNULL(PE.FIRST_NAME,'')+' '+ISNULL(PE.MIDDLE_NAME,'')))) NOMBRE
                , SE.ACADEMIC_YEAR
                , SE.ACADEMIC_TERM
                , SE.ACADEMIC_SESSION
                , FORMAT(SE.START_DATE, 'yyyy-MM-dd') START_DATE
                , FORMAT(SE.END_DATE, 'yyyy-MM-dd') END_DATE
                , SE.EVENT_ID
                , UPPER(SE.PUBLICATION_NAME_1) PUBLICATION_NAME_1
                , SE.SECTION
                , SE.SERIAL_ID
                , SE.PROGRAM
                , CP.LONG_DESC PROGRAM_DESC
                , CASE
                WHEN SE.CURRICULUM IS NULL OR SE.CURRICULUM = '' THEN
                            (SELECT TOP 1 AC.CURRICULUM
                                    FROM TRANSCRIPTDETAIL AS TD
                                    INNER JOIN ACADEMIC AC ON TD.PEOPLE_CODE_ID = AC.PEOPLE_CODE_ID
                                        AND TD.ACADEMIC_YEAR = AC.ACADEMIC_YEAR AND TD.ACADEMIC_TERM = AC.ACADEMIC_TERM
                                        AND TD.ACADEMIC_SESSION = AC.ACADEMIC_SESSION
                                    WHERE TD.ACADEMIC_YEAR = SE.ACADEMIC_YEAR
                                    AND TD.ACADEMIC_TERM = SE.ACADEMIC_TERM 
                                    AND TD.ACADEMIC_SESSION = SE.ACADEMIC_SESSION
                                    AND TD.EVENT_ID = SE.EVENT_ID 
                                    AND TD.SECTION = SE.SECTION 
                                    AND TD.SERIAL_ID = SE.SERIAL_ID
                                    AND TD.ADD_DROP_WAIT = 'A'
                                    GROUP BY AC.CURRICULUM ORDER BY COUNT(TD.PEOPLE_CODE_ID) DESC)
                    ELSE SE.CURRICULUM
                    END CURRICULUM
                , CASE
                WHEN SE.CURRICULUM IS NULL OR SE.CURRICULUM = '' THEN 'COMPACTADA'
                ELSE CC.LONG_DESC
                END CURRICULUM_DESC
                , SE.EVENT_STATUS
                , SE.ADDS
                , SSC.DAY
                , CASE SSC.DAY WHEN 'DOM' THEN '0' WHEN 'LUN' THEN '1' WHEN 'MAR' THEN '2' WHEN 'MIE' THEN '3' WHEN 'JUE' THEN '4' WHEN 'VIE' THEN '5' WHEN 'SAB' THEN '6' ELSE '' END CODE_DAY
                , CONVERT(VARCHAR, SSC.START_TIME, 108) START_CLASS
                , CONVERT(VARCHAR, SSC.END_TIME, 108) END_CLASS
                , SE.CONTACT_HR_SESSION
                , DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) MinutesClass
                , DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) / 60.0 HourClass
                , CASE
                WHEN SE.PROGRAM IN ('BGCMAT','BGCVES','BGUDG','BGUNI','BTEC') THEN FLOOR(CEILING(DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) / 60.0 * 2) / 2.0)
                ELSE CEILING(DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) / 60.0 * 2) / 2.0
                END ROUND_HourClass

                , IIF(DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) IS NULL, 'SH','HN') VAL_HORAS --(SH SIN HORARIO POSIBLE HIPERCOMPACTADA, HN HORARIO NORMAL)

                FROM SECTIONS SE 
                    INNER JOIN SECTIONPER SEP
                    ON SE.ACADEMIC_YEAR = SEP.ACADEMIC_YEAR
                    AND SE.ACADEMIC_TERM = SEP.ACADEMIC_TERM
                    AND SE.ACADEMIC_SESSION = SEP.ACADEMIC_SESSION
                    AND SE.EVENT_ID = SEP.EVENT_ID
                    AND SE.SECTION = SEP.SECTION
                INNER JOIN PEOPLE PE
                    ON SEP.PERSON_CODE_ID = PE.PEOPLE_CODE_ID
                INNER JOIN EVENT EV ON SE.EVENT_ID = EV.EVENT_ID
                LEFT OUTER JOIN CODE_CURRICULUM CC
                    ON SE.CURRICULUM = CC.CODE_VALUE_KEY
                LEFT OUTER JOIN SECTIONSCHEDULE SSC
                    ON SSC.ACADEMIC_YEAR = SE.ACADEMIC_YEAR
                    AND SSC.ACADEMIC_TERM = SE.ACADEMIC_TERM
                    AND SSC.ACADEMIC_SESSION = SE.ACADEMIC_SESSION
                    AND SSC.EVENT_ID = SE.EVENT_ID
                    AND SSC.SECTION = SE.SECTION
                LEFT OUTER JOIN CODE_PROGRAM CP ON CP.CODE_VALUE_KEY = SE.PROGRAM

                WHERE SE.ACADEMIC_YEAR IN ($yearList)
                AND SE.ACADEMIC_TERM IN ($termList)
                    AND SE.EVENT_STATUS = 'A'
                    AND SE.ADDS <> 0
                    AND SEP.PERSON_CODE_ID IS NOT NULL
                    AND ISNULL(DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME),0) + SE.CONTACT_HR_SESSION > 0
                    
                GROUP BY 
                PE.PEOPLE_CODE_ID
                , PE.PREV_GOV_ID
                , PE.LAST_NAME
                , PE.Last_Name_Prefix
                , PE.FIRST_NAME
                , PE.MIDDLE_NAME
                , SE.ACADEMIC_YEAR
                , SE.ACADEMIC_TERM
                , SE.ACADEMIC_SESSION
                , SE.START_DATE
                , SE.END_DATE
                , SE.EVENT_ID
                , SE.PUBLICATION_NAME_1
                , SE.SECTION
                , SE.SERIAL_ID
                , SE.PROGRAM
                , CP.LONG_DESC
                , SE.CURRICULUM
                , CC.LONG_DESC
                , SE.EVENT_STATUS
                , SE.ADDS
                , SE.CONTACT_HR_SESSION
                , SSC.DAY
                , SSC.START_TIME
                , SSC.END_TIME
                , SE.SCHEDULED_MEETINGS
                , EV.PROGRAM
                , EV.CURRICULUM";



        $stmt = sqlsrv_query($conn, $query);
        
        if (!$stmt) {
            sqlsrv_close($conn);
            return null;
        }
        
        $schedules_Payroll = array();

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $schedules_Payroll[] = $row;
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        return $schedules_Payroll;
    }
    
    
}
?>