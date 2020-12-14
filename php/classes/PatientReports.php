<?php

/**
 * Patient Reports class
 * @author K. Agnew Dec 2020 - Patient reports refactor - death to Perl
 * 
 */

class PatientReports extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_PATIENT, $guestStatus);
    }

    /**
     * Search database for patient
     * 
     * @param $name: patient last name case insensitive
     * @return $response: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByName( $name ) {
        $this->checkReadAccess();
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                PatientSerNum,
                PatientId,
                FirstName,
                LastName,
                SSN,
                Sex,
                Email,
                Language
            FROM
                Patient 
            WHERE
                Patient.LastName = '$name'
             ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();
            $patientList = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $patientArray = array(
                    'psnum'      => $data[0],
                    'pid'        => $data[1],
                    'fname'      => $data[2],
                    'lname'      => $data[3],
                    'ssn'        => $data[4],
                    'sex' 	     => $data[5],
                    'email'      => $data[6],
                    'language'   => $data[7]
                );
                array_push($patientList, $patientArray);
            }
            return $patientList;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }
    }

    /**
     * Search database for patient
     * 
     * @param $mrn: patient mrn
     * @return $response: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByMRN( $mrn ) {
        $this->checkReadAccess();
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                PatientSerNum,
                PatientId,
                FirstName,
                LastName,
                SSN,
                Sex,
                Email,
                Language
            FROM
                Patient 
            WHERE
                Patient.PatientId = '$mrn'
             ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();
            $patientList = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $patientArray = array(
                    'psnum'      => $data[0],
                    'pid'        => $data[1],
                    'fname'      => $data[2],
                    'lname'      => $data[3],
                    'ssn'        => $data[4],
                    'sex' 	     => $data[5],
                    'email'      => $data[6],
                    'language'   => $data[7]
                );
                array_push($patientList, $patientArray);
            }
            return $patientList;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }

    }

    /**
     * Search database for patient
     * 
     * @param $ramq: patient ramq
     * @return $response: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByRAMQ( $ramq ) {
        $this->checkReadAccess();
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                PatientSerNum,
                PatientId,
                FirstName,
                LastName,
                SSN,
                Sex,
                Email,
                Language
            FROM
                Patient 
            WHERE
                Patient.SSN = '$ramq'
             ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();
            $patientList = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $patientArray = array(
                    'psnum'      => $data[0],
                    'pid'        => $data[1],
                    'fname'      => $data[2],
                    'lname'      => $data[3],
                    'ssn'        => $data[4],
                    'sex' 	     => $data[5],
                    'email'      => $data[6],
                    'language'   => $data[7]
                );
                array_push($patientList, $patientArray);
            }
            return $patientList;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }

    }
    
    /**
     *  Generate the patient report given patient serial number & feature list
     *  @param pnum: selected patient serial number
     *  @param flist: array of report segments each pointing to a truthy/falsy variable for report generation
     *  @return resultArray: patient data report JSON object, keyed by report segment name
     */
    public function getPatientReport($pnum, $flist){
        $this->checkReadAccess();
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            
            $resultArray = array();
            $sql = "";
            if($flist["diagnosis"] == true){
                $sql="
                SELECT
                    DiagnosisSerNum,
                    CreationDate,
                    Description_EN
                FROM
                    Diagnosis
                WHERE
                    PatientSerNum = '$pnum'";

                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $diagnosisReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'sernum'      => $data[0],
                        'creationdate'        => $data[1],
                        'description'      => $data[2],
                    );
                    array_push($diagnosisReport, $reportArray);
                }
                $resultArray["diagnosis"] = $diagnosisReport;
                $sql = "";
            }
            
            if($flist["appointments"] == true){
                $sql ="  
                SELECT
                    a.ScheduledStartTime,
                    a.Status,
                    a.DateAdded,
                    als.AliasName_EN,
                    als.AliasType,
                    r.ResourceName
                FROM
                    Appointment AS a,
                    AliasExpression AS ae,
                    Alias AS als,
                    Resource AS r,
                    ResourceAppointment AS ra
                WHERE
                    PatientSerNum = '$pnum'
                    AND a.AliasExpressionSerNum = ae.AliasExpressionSerNum
                    AND ae.AliasSerNum = als.AliasSerNum
                    AND r.ResourceSerNum = ra.ResourceSerNum
                    AND ra.AppointmentSerNum = a.AppointmentSerNum";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $appointmentsReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'starttime'      => $data[0],
                        'status'        => $data[1],
                        'dateadded'      => $data[2],
                        'aliasname'          => $data[3],
                        'aliastype'          => $data[4],
                        'resourcename'      => $data[5]
                    );
                    array_push($appointmentsReport, $reportArray);
                }
                $resultArray["appointments"] = $appointmentsReport;
                $sql = "";  
            }
            
            if($flist["questionnaires"] == true){
                $sql ="  
                SELECT
                    q.DateAdded,
                    q.CompletionDate,
                    qc.QuestionnaireName_EN
                FROM
                    Questionnaire AS q,
                    QuestionnaireControl AS qc
                WHERE
                    q.QuestionnaireControlSerNum = qc.QuestionnaireControlSerNum
                    AND PatientSerNum = '$pnum'";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $questionnairesReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'dateadded'      => $data[0],
                        'datecompleted'        => $data[1],
                        'name'      => $data[2]
                    );
                    array_push($questionnairesReport, $reportArray);
                }
                $resultArray["questionnaires"] = $questionnairesReport;
                $sql = ""; 
            }

            if($flist["education"] == true){
                $sql ="  
                SELECT
                    em.DateAdded,
                    em.ReadStatus,
                    emc.EducationalMaterialType_EN,
                    emc.Name_EN
                FROM
                    EducationalMaterial AS em,
                    EducationalMaterialControl AS emc
                WHERE
                    em.EducationalMaterialControlSerNum = emc.EducationalMaterialControlSerNum
                    AND PatientSerNum = '$pnum'";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $educationReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'dateadded'      => $data[0],
                        'readstatus'        => $data[1],
                        'materialtype'      => $data[2],
                        'name'      => $data[3]
                    );
                    array_push($educationReport, $reportArray);
                }
                $resultArray["education"] = $educationReport;
                $sql = ""; 
            }

            if($flist["testresults"] == true){
                $sql ="  
                SELECT
                    DateAdded,
                    TestDate,
                    ComponentName,
                    AbnormalFlag,
                    TestValue,
                    MinNorm,
                    MaxNorm,
                    UnitDescription,
                    ReadStatus
                FROM
                    TestResult
                WHERE
                    PatientSerNum = '$pnum'";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $testResultsReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'dateadded'      => $data[0],
                        'testdate'        => $data[1],
                        'componentname'      => $data[2],
                        'abnormalflag'      => $data[3],
                        'testvalue'         => $data[4],
                        'minnorm'           => $data[5],
                        'maxnorm'           => $data[6],
                        'unitdescription'   => $data[7],
                        'readstatus'        => $data[8]
                    );
                    array_push($testResultsReport, $reportArray);
                }
                $resultArray["testresults"] = $testResultsReport;
                $sql = ""; 
            }

            if($flist["notes"] == true){
                $sql ="  
                SELECT
                    n.DateAdded,
                    n.LastUpdated,
                    n.ReadStatus,
                    nc.Name_EN,
                    n.RefTableRowTitle_EN
                FROM
                    Patient AS p,
                    Notification AS n,
                    NotificationControl AS nc
                WHERE
                    p.PatientSerNum = n.PatientSerNum
                    AND n.NotificationControlSerNum = nc.NotificationControlSerNum
                    AND p.PatientSerNum = '$pnum'";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $notesReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'dateadded'      => $data[0],
                        'lastupdated'        => $data[1],
                        'readstatus'      => $data[2],
                        'name'      => $data[3],
                        'tablerowtitle'         => $data[4],
                    );
                    array_push($notesReport, $reportArray);
                }
                $resultArray["notes"] = $notesReport;
                $sql = ""; 
            }

            if($flist["treatplan"] == true){
                $sql ="  
                SELECT
                    d.Description_EN,
                    a.AliasType,
                    pr.PriorityCode,
                    ae.Description,
                    a.AliasName_EN,
                    a.AliasDescription_EN,
                    t.Status,
                    t.State,
                    t.DueDateTime,
                    t.CompletionDate
                FROM
                    Task AS t,
                    Patient AS p,
                    AliasExpression AS ae,
                    Alias AS a,
                    Diagnosis AS d,
                    Priority AS pr
                WHERE
                    t.PatientSerNum = p.PatientSerNum
                    AND p.PatientSerNum = pr.PatientSerNum
                    AND ae.AliasExpressionSerNum = t.AliasExpressionSerNum
                    AND ae.AliasSerNum = a.AliasSerNum
                    AND t.DiagnosisSerNum = d.DiagnosisSerNum
                    AND t.PrioritySerNum = pr.PrioritySerNum
                    AND p.PatientSerNum = '$pser'";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $treatPlanReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'diagnosisdescription'      => $data[0],
                        'aliastype'        => $data[1],
                        'prioritycode'      => $data[2],
                        'aliasexpressiondescription'      => $data[3],
                        'aliasname'         => $data[4],
                        'aliasdescription'  => $data[5],
                        'taskstatus'        => $data[6],
                        'taskstate'         => $data[7],
                        'taskdue'           => $data[8],
                        'taskcompletiondate' => $data[9]
                    );
                    array_push($treatPlanReport, $reportArray);
                }
                $resultArray["treatplan"] = $treatPlanReport;
                $sql = ""; 
            }

            if($flist["clinicalnotes"] == true){
                $sql ="  
                SELECT
                    d.OriginalFileName,
                    d.FinalFileName,
                    d.CreatedTimeStamp,
                    d.ApprovedTimeStamp,
                    ae.ExpressionName
                FROM
                    Document AS d,
                    Patient AS p,
                    AliasExpression AS ae
                WHERE
                    d.PatientSerNum = p.PatientSerNum
                    AND d.AliasExpressionSerNum = ae.AliasExpressionSerNum
                    AND p.PatientSerNum = '$pnum'";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $clinNotesReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'originalname'      => $data[0],
                        'finalname'        => $data[1],
                        'created'      => $data[2],
                        'approved'      => $data[3],
                        'aliasexpressionname'  => $data[4],
                    );
                    array_push($clinNotesReport, $reportArray);
                }
                $resultArray["clinicalnotes"] = $clinNotesReport;
                $sql = ""; 
            }

            if($flist["treatingteam"] == true){
                $sql =" 
                SELECT
                    tx.DateAdded,
                    pc.PostName_EN,
                    tx.ReadStatus,
                    pc.Body_EN
                FROM
                    TxTeamMessage AS tx,
                    PostControl AS pc,
                    Patient AS p
                WHERE
                tx.PatientSerNum = p.PatientSerNum
                AND tx.PostControlSerNum = pc.PostControlSerNum
                AND p.PatientSerNum = '$pnum'";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $treatteamReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'dateadded'      => $data[0],
                        'name'        => $data[1],
                        'readstatus'      => $data[2],
                        'body'      => $data[3],
                    );
                    array_push($treatteamReport, $reportArray);
                }
                $resultArray["treatingteam"] = $treatteamReport;
                $sql = ""; 
            }

            if($flist["general"] == true){
                $sql =" 
                SELECT
                    a.DateAdded,
                    a.ReadStatus,
                    pc.PostName_EN,
                    pc.Body_EN
                FROM
                    Patient AS p,
                    Announcement AS a,
                    PostControl AS pc
                WHERE
                    p.PatientSerNum = a.PatientSerNum
                    AND a.PostControlSerNum = pc.PostControlSerNum
                    AND p.PatientSerNum = '$pnum'";
            
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $generalReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'dateadded'      => $data[0],
                        'readstatus'        => $data[1],
                        'name'      => $data[2],
                        'body'      => $data[3],
                    );
                    array_push($generalReport, $reportArray);
                }
                $resultArray["general"] = $generalReport;
                $sql = ""; 
            }

            print_r($resultArray);
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }


    }

}