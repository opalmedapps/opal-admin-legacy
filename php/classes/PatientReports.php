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

    //Theres probably a better place for this. Used to disclude these patients from group reports
    //private $nameList = "('TEST','QA_OPAL','Demo','TRANSITION','TelNum')";
    private $nameList = "('Demo')"; //limiting the list to one for testing purposes REMOVE AND REPLACE WITH PROPER NAMELIST  TODO
    /**
     * Search database for patient
     * 
     * @param name: patient last name case insensitive
     * @return patientList: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByName( $nameInp ) {
        $name = HelpSetup::arraySanitization($nameInp);
        //check read access before proceeding
        $this->checkReadAccess(array("name"=>$name));
        
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
                    'pname'      => $data[2],
                    'plname'      => $data[3],
                    'pramq'        => $data[4],
                    'psex' 	     => $data[5],
                    'pemail'      => $data[6],
                    'plang'   => $data[7]
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
     * @param mrn: patient mrn
     * @return patientList: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByMRN( $mrnInp ) {
        $mrn = HelpSetup::arraySanitization($mrnInp);
        //check read access before proceeding
        $this->checkReadAccess(array("mrn"=>$mrn));
        
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
                    'pname'      => $data[2],
                    'plname'      => $data[3],
                    'pramq'        => $data[4],
                    'psex' 	     => $data[5],
                    'pemail'      => $data[6],
                    'plang'   => $data[7]
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
     * @param ramq: patient ramq
     * @return patientList: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByRAMQ( $ramqInp ) {
        $ramq = HelpSetup::arraySanitization($ramqInp);
        //check read access before proceeding
        $this->checkReadAccess(array("ramq"=>$ramq));
                    
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
                    'pname'      => $data[2],
                    'plname'      => $data[3],
                    'pramq'        => $data[4],
                    'psex' 	     => $data[5],
                    'pemail'      => $data[6],
                    'plang'   => $data[7]
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
        $this->checkReadAccess(array("serial"=>$pnum,"features"=>$fList));
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            
            $resultArray = array();
            $sql = "";
            if($flist["diagnosis"] === "true"){
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
            
            if($flist["appointments"] === "true"){
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
            
            if($flist["questionnaires"] === "true"){
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

            if($flist["education"] === "true"){
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

            if($flist["testresults"] === "true"){
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

            if($flist["pattestresults"] === "true"){
                $sql="
                SELECT DISTINCT
                    IF(ptr.TestGroupExpressionSerNum IS NULL , '', tge.ExpressionName) as groupName,
                    ptr.ReadStatus as readStatus,
                    tc.Name_EN as name_EN,
                    ptr.AbnormalFlag as abnormalFlag,
                    ptr.NormalRange as normalRange,
                    ptr.TestValue as testValue,
                    ptr.UnitDescription as unitDescription,
                    ptr.DateAdded AS dateAdded,
                    ptr.CollectedDateTime AS collectedDateTime,
                    ptr.ResultDateTime AS resultDateTime
                FROM
                    PatientTestResult as ptr, TestExpression as te,
                    TestGroupExpression as tge, TestControl as tc,
                    EducationalMaterialControl as emc
                WHERE
                    ptr.PatientSerNum = '$pnum'
                    AND (ptr.TestGroupExpressionSerNum = tge.TestGroupExpressionSerNum OR
                    ptr.TestGroupExpressionSerNum IS NULL)
                    AND ptr.TestExpressionSerNum = te.TestExpressionSerNum
                    AND te.TestControlSerNum = tc.TestControlSerNum
                    AND tc.EducationalMaterialControlSerNum = emc.EducationalMaterialControlSerNum
                    AND ptr.TestValueNumeric is not null
                    ORDER BY groupName, sequenceNum";
        
                $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $query->execute();
                $pattestReport = array();
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $reportArray = array(
                        'groupname'      => $data[0],
                        'readstatus'        => $data[1],
                        'testname'      => $data[2],
                        'abnormalflag'      => $data[3],
                        'normalrange'         => $data[4],
                        'testvalue'         => $data[5],
                        'description'       => $data[6],
                        'dateadded'        => $data[7],
                        'datecollected'     => $data[8],
                        'resultdate'        => $data[9],
                    );
                    array_push($pattestReport, $reportArray);
                }
                $resultArray["pattestresults"] = $pattestReport;
                $sql = ""; 
            }

            if($flist["notes"] === "true"){
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

            if($flist["treatplan"] === "true"){
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

            if($flist["clinicalnotes"] === "true"){
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

            if($flist["treatingteam"] === "true"){
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

            if($flist["general"] === "true"){
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

            return $resultArray;

        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }


    }


    /**
     *  Generate list of available educational materials from DB
     *  @param matType: user selected material type
     *  @return educList: array of educational materials
     */
    public function findEducationalMaterialOptions( $matType ){
        $this->checkReadAccess(array("type"=>$matType));
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                Name_EN,
                PublishFlag
            FROM
                EducationalMaterialControl
            WHERE
                EducationalMaterialType_EN = '$matType'";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $educList = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $educArray = array(
                    'name'      => $data[0],
                    'pflag'        => $data[1],
                );
                array_push($educList, $educArray);
            }
            return $educList;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }

    }

    /**
     *  Generate educational materials group report
     *  @param matType: user selected material type
     *  @param matName: user selected material name
     *  @return educReport: educational material report
     */
    public function getEducationalMaterialReport( $matType, $matName ){
        $this->checkReadAccess(array("type"=>$matType,"name"=>$matName));
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                p.FirstName,
                p.LastName,
                p.PatientSerNum,
                p.Sex,
                p.Age,
                p.DateOfBirth,
                em.DateAdded,
                em.ReadStatus,
                em.LastUpdated
            FROM
                Patient AS p,
                EducationalMaterial AS em,
                EducationalMaterialControl AS emc
            WHERE
                em.PatientSerNum = p.PatientSerNum
                AND p.LastName NOT IN {$this->nameList}
                AND em.EducationalMaterialControlSerNum = emc.EducationalMaterialControlSerNum
                AND emc.EducationalMaterialType_EN = '$matType'
                AND emc.Name_EN = '$matName'";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $educReport = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $educArray = array(
                    'pname'      => $data[0],
                    'plname'        => $data[1],
                    'pser'          => $data[2],
                    'psex'          => $data[3],
                    'page'          => $data[4],
                    'pdob'          => $data[5],
                    'edate'         => $data[6],
                    'eread'         => $data[7],
                    'eupdate'       => $data[8]
                );
                array_push($educReport, $educArray);
            }
            return $educReport;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }

    }

    /**
     *  Generate list of questionnaires available in DB
     *  @return qstList: questionnaire names array
     */
    public function findQuestionnaireOptions(){
        $this->checkReadAccess();
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                QuestionnaireName_EN
            FROM
                QuestionnaireControl";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $qstList = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $qstArray = array(
                    'name'      => $data[0],
                );
                array_push($qstList, $qstArray);
            }
            return $qstList;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }

    }


    /**
     *  Generate questionnaires report given user selected qName
     *  @param qName: questionnaire name
     *  @return qstReport: questionnaire report JSON object
     */
    public function getQuestionnaireReport( $qName ){
        $this->checkReadAccess(array("qName"=>$qName));
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                p.FirstName,
                p.LastName,
                p.PatientSerNum,
                p.Sex,
                p.DateOfBirth,
                q.DateAdded,
                q.CompletionDate
            FROM
                Patient AS p,
                Questionnaire AS q,
                QuestionnaireControl AS qc
            WHERE
                p.PatientSerNum = q.PatientSerNum
                AND p.LastName NOT IN {$this->nameList}
                AND q.QuestionnaireControlSerNum = qc.QuestionnaireControlSerNum
                AND qc.QuestionnaireName_EN = '$qName'";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $qstReport = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $qstArray = array(
                    'pname'      => $data[0],
                    'plname'        => $data[1],
                    'pser'          => $data[2],
                    'psex'          => $data[3],
                    'pdob'          => $data[4],
                    'qdate'          => $data[5],
                    'qcomplete'         => $data[6]
                );
                array_push($qstReport, $qstArray);
            }
            return $qstReport;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }

    }

    /**
     *  Generate patient group report
     *  @return ptReport: patient group report JSON object
     */
    public function getPatientGroupReport(){
        $this->checkReadAccess();
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                p.PatientSerNum,
                p.FirstName,
                p.LastName,
                p.Sex,
                p.DateOfBirth,
                p.Age,
                p.Email,
                p.Language,
                p.RegistrationDate,
                p.ConsentFormExpirationDate,
                ifnull((select d1.Description_EN from Diagnosis d1 where p.PatientSerNum = d1.PatientSerNum order by CreationDate desc limit 1), 'NA') as Description_EN,
                ifnull((select d2.CreationDate from Diagnosis d2 where p.PatientSerNum = d2.PatientSerNum order by CreationDate desc limit 1), now()) as CreationDate
            FROM
                Patient AS p
            WHERE
                p.LastName NOT IN {$this->nameList}
            ORDER BY p.RegistrationDate";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $ptReport = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $ptArray = array(
                    'pser'          => $data[0],
                    'pname'         => $data[1],
                    'plname'        => $data[2],
                    'psex'          => $data[3],
                    'pdob'          => $data[4],
                    'page'          => $data[5],
                    'pemail'        => $data[6],
                    'plang'         => $data[7],
                    'preg'          => $data[8],
                    'pcons'         => $data[9],
                    'diagdesc'      => $data[10],
                    'diagdate'      => $data[11]
                );
                array_push($ptReport, $ptArray);
            }
            return $ptReport;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }

    }

}