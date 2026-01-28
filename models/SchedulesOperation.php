<?php
require_once dirname(__DIR__) . '/config/connect.php';

class SchedulesOperation {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getSchedulesOperation($academicYears = [], $academicTerms = []) {
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
                    ROW_NUMBER() OVER(ORDER BY LTRIM(RTRIM(ISNULL(PE.LAST_NAME,'')+' '+ISNULL(PE.Last_Name_Prefix,'')+' '+ISNULL(PE.FIRST_NAME,'')+' '+ISNULL(PE.MIDDLE_NAME,''))) ) NUM
                    , PE.PEOPLE_CODE_ID
                    , PE.PREV_GOV_ID
                    , PE.GOVERNMENT_ID
                    , UPPER(PE.LAST_NAME) LAST_NAME
                    , UPPER(PE.Last_Name_Prefix) Last_Name_Prefix
                    , UPPER(PE.FIRST_NAME) FIRST_NAME
                    , UPPER(PE.MIDDLE_NAME) MIDDLE_NAME
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
                                CASE  WHEN EV.CURRICULUM IS NULL OR EV.CURRICULUM = '' THEN 'GENERICA'
                                ELSE EV.CURRICULUM END
                        ELSE SE.CURRICULUM
                        END CURRICULUM

                    , CASE
                    WHEN SE.CURRICULUM IS NULL OR SE.CURRICULUM = '' THEN
                                CASE  WHEN EV.CURRICULUM IS NULL OR EV.CURRICULUM = '' THEN (SELECT 
                                                                                            STUFF((
                                                                                                    SELECT DISTINCT ',' + CURRICULUM 
                                                                                                FROM EVENTCROSSREF EVC
                                                                                                INNER JOIN EVENT EV ON EVC.EVENT_ID = EV.EVENT_ID
                                                                                                WHERE CROSS_ID = SE.EVENT_ID
                                                                                                FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)')
                                                                                            , 1, 1, '') )
                                                                                        ELSE '' END
                        ELSE ''
                        END CURRICULUMS_GEN

                    , CC.LONG_DESC FORMAL_TITLE
                    , SE.CLASS_LEVEL
                    , SE.CIP_CODE
                    , SE.EVENT_STATUS
                    , SE.GENERAL_ED
                    , (SELECT CGE.LONG_DESC FROM CODE_GENERALED CGE WHERE CGE.CODE_VALUE_KEY = SE.GENERAL_ED) DESC_GENERAL_ED
                    , SE.ADDS
                    , SSC.BUILDING_CODE
                    , BLD.BUILD_NAME_1
                    , SSC.ROOM_ID
                    , ROOM.ROOM_NAME
                    , SSC.DAY
                    , CASE SSC.DAY WHEN 'DOM' THEN '0' WHEN 'LUN' THEN '1' WHEN 'MAR' THEN '2' WHEN 'MIE' THEN '3' WHEN 'JUE' THEN '4' WHEN 'VIE' THEN '5' WHEN 'SAB' THEN '6' ELSE '' END CODE_DAY
                    , CONVERT(VARCHAR, SSC.START_TIME, 108) START_TIME
                    , CONVERT(VARCHAR, SSC.END_TIME, 108) END_TIME
                    , SE.SCHEDULED_MEETINGS
                    , CASE WHEN ATS.AssignmentTemplateHeaderId <> 0 THEN 'SI' ELSE 'NO' END AS [PLANTILLA]
                    , SE.CONTACT_HR_SESSION
                    , DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) MinutesClass
                    , DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) / 60.0 HourClass
                    , CEILING(DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) / 60.0 * 2) / 2.0 AS ROUND_HourClass
                    , IIF(DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME) IS NULL, 'SH','HN') VAL_HORAS --(SH SIN HORARIO POSIBLE HIPERCOMPACTADA, HN HORARIO NORMAL)
                    , IIF (SE.EVENT_ID IN ('RLENFEENF1501','RLENFEENF1601','RLENFEENF1701','RLENFEENF1801','RLENFEENF1901','RLENFEENF1001','RLENFEENF1002','RLENFEENF1111','RLENFEENF1121','RLENFEENF1122','RLENFEENF1123'), 1, 0) AS [FLAG_CLINIC]

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
                    LEFT OUTER JOIN AssignmentTemplateSection ATS
                        ON SE.SectionId = ATS.SectionId
                    LEFT OUTER JOIN BUILDING BLD ON BLD.BUILDING_CODE = SSC.BUILDING_CODE
                    LEFT OUTER JOIN ROOM ROOM ON ROOM.ROOM_ID = SSC.ROOM_ID AND ROOM.BUILDING_CODE = SSC.BUILDING_CODE
                    LEFT OUTER JOIN CODE_PROGRAM CP ON CP.CODE_VALUE_KEY = SE.PROGRAM

                    WHERE SE.ACADEMIC_YEAR IN ($yearList)
                        AND SE.ACADEMIC_TERM IN ($termList)
                        AND SE.EVENT_STATUS = 'A'
                        AND SE.CONTACT_HR_SESSION <> 0
                        AND SE.ADDS <> 0
                        AND SEP.PERSON_CODE_ID IS NOT NULL
                        AND ISNULL(DATEDIFF(MINUTE, SSC.START_TIME, SSC.END_TIME),0) + SE.CONTACT_HR_SESSION > 0
                        
                    GROUP BY 
                    PE.PEOPLE_CODE_ID
                    , PE.PREV_GOV_ID
                    , PE.GOVERNMENT_ID
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
                    , SE.CLASS_LEVEL
                    , SE.CIP_CODE
                    , SE.EVENT_STATUS
                    , SE.GENERAL_ED
                    , SE.ADDS
                    , SSC.BUILDING_CODE
                    , BLD.BUILD_NAME_1
                    , SSC.ROOM_ID
                    , ROOM.ROOM_NAME
                    , SSC.DAY
                    , SSC.START_TIME
                    , SSC.END_TIME
                    , SE.SCHEDULED_MEETINGS
                    , ATS.AssignmentTemplateHeaderId
                    , SE.CONTACT_HR_SESSION
                    , EV.PROGRAM
                    , EV.CURRICULUM";

        $stmt = sqlsrv_query($conn, $query);
        
        if (!$stmt) {
            sqlsrv_close($conn);
            return null;
        }
        
        $schedules_operation = array();

        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $schedules_operation[] = $row;
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        
        return $schedules_operation;
    }
    
    
}
?>