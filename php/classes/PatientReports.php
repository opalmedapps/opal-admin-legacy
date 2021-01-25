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
    private $nameList = "('TEST','QA_OPAL','Demo','TRANSITION','TelNum')";
    /**
     * Search database for patient
     * 
     * @param name: patient last name case insensitive
     * @return patientList: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByName( $post ) {
        //check read access before proceeding
        $this->checkReadAccess($post);
        //sanitize
        $plname = HelpSetup::arraySanitization($post['pname']);
        // SQL call from ORM in place
        return $this->opalDB->getPatientName($plname);
    }

    /**
     * Search database for patient
     * 
     * @param mrn: patient mrn
     * @return patientList: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByMRN( $post ) {
        $this->checkReadAccess($post);
        $pmrn = HelpSetup::arraySanitization($post['pmrn']);
        return $this->opalDB->getPatientMRN($pmrn);

    }

    /**
     * Search database for patient
     * 
     * @param ramq: patient ramq
     * @return patientList: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByRAMQ( $post ) {
        $this->checkReadAccess($post);
        $ramq = HelpSetup::arraySanitization($post['pramq']);
        return $this->opalDB->getPatientRAMQ($ramq);        
        
    }
    
    /**
     *  Generate the patient report given patient serial number & feature list
     *  @param pnum: selected patient serial number
     *  @param flist: array of report segments each pointing to a truthy/falsy variable for report generation
     *  @return resultArray: patient data report JSON object, keyed by report segment name
     */
    public function getPatientReport($post){
        $this->checkReadAccess($post);
        
        $pnum = HelpSetup::arraySanitization($post['psnum']);
        $flist = array(
            "diagnosis" => $post['diagnosis'],
            "appointments" => $post['appointments'],
            "questionnaires" => $post['questionnaires'],
            "education" => $post['education'],
            "testresults" => $post['testresults'],
            "pattestresults" => $post['pattestresults'],
            "notes" => $post['notes'],
            "treatplan" => $post['treatplan'],
            "clinicalnotes" => $post['clinicalnotes'],
            "treatingteam" => $post['treatingteam'],
            "general" => $post['general']
        );
        
        $resultArray = array();
        if($flist["diagnosis"] === "true"){
            $resultArray["diagnosis"] = $this->opalDB->getPatientDiagnosisReport($pnum);
        }
        if($flist["appointments"] === "true"){
            $resultArray["appointments"] = $this->opalDB->getPatientAppointmentReport($pnum);
        }
        if($flist["questionnaires"] === "true"){
            $resultArray["questionnaires"] = $this->opalDB->getPatientQuestionnaireReport($pnum);
        }
        if($flist["education"] === "true"){
            $resultArray["education"] = $this->opalDB->getPatientEducMaterialReport($pnum);
        }
        if($flist["testresults"] === "true"){
            $resultArray["testresults"] = $this->opalDB->getPatientLegacyTestReport($pnum);
        }
        if($flist["pattestresults"] === "true"){
            $resultArray["pattestresults"] = $this->opalDB->getPatientTestReport($pnum);
        }
        if($flist["notes"] === "true"){
            $resultArray["notes"] = $this->opalDB->getPatientNotificationsReport($pnum);
        }
        if($flist["treatplan"] === "true"){
            $resultArray["treatplan"] = $this->opalDB->getPatientTreatmentPlanReport($pnum);
        }
        if($flist["clinicalnotes"] === "true"){
            $resultArray["clinicalnotes"] = $this->opalDB->getPatientClinNoteReport($pnum);
        }
        if($flist["treatingteam"] === "true"){
            $resultArray["treatingteam"] = $this->opalDB->getPatientTxTeamReport($pnum);
        }
        if($flist["general"] === "true"){
            $resultArray["general"] = $this->opalDB->getPatientGeneralReport($pnum);
        }
        return $resultArray;
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