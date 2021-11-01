<?php


class CronJob extends OpalProject {

    protected $questionnaireDB;

    /**
     * CronJob constructor. It establish connection to the OpalDB and QuestionnaireDB. The default user is set up to
     * DEFAULT_CRON_OAUSERID.
     */
    public function __construct() {
        parent::__construct(DEFAULT_CRON_OAUSERID, false);

        $this->questionnaireDB = new DatabaseQuestionnaire(
            QUESTIONNAIRE_DB_2019_HOST,
            QUESTIONNAIRE_DB_2019_NAME,
            QUESTIONNAIRE_DB_2019_PORT,
            QUESTIONNAIRE_DB_2019_USERNAME,
            QUESTIONNAIRE_DB_2019_PASSWORD,
            false
        );

        $this->questionnaireDB->setUsername($this->opalDB->getUsername());
        $this->questionnaireDB->setOAUserId($this->opalDB->getOAUserId());
        $this->questionnaireDB->setUserRole($this->opalDB->getUserRole());
    }

    /**
     * Because the CronJob is not really a module, it has to be attached to another parent class named OpalProject. This
     * way, CronJob and Module can share some methods while having their own method. To validate if a cron call is valid
     * or not, it checks the user IP address which should be itself or locally. If it is not, rejects it.
     * @param array $arguments
     * @return false
     */
    protected function _checkCronAccess($arguments = array()) {
        $arguments = HelpSetup::arraySanitization($arguments);
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        if(!in_array(HelpSetup::getUserIP(), LOCALHOST_ADDRESS)) {
            $this->_insertAudit($moduleName, $methodeName, $arguments, ACCESS_DENIED, $this->opalDB->getUsername());
            HelpSetup::returnErrorMessage(HTTP_STATUS_FORBIDDEN_ERROR, "Access denied.");
        }
        $this->_insertAudit($moduleName, $methodeName, $arguments, ACCESS_GRANTED, $this->opalDB->getUsername());
        return false;
    }

    /**
     * Update the resource in the resourcePending table. First, every resource with an Appointment ready that exists
     * are marked with a level 2 (processing). Then, for 29 seconds, the method will take one record at the time,
     * insert it or update it in resource table, and link the resources with the appointment in the pivot table
     * resourceAppointment. Then the record in resourcePending is deleted and the processing continues.
     */
    public function updateResourcePending() {
        $this->_checkCronAccess();
        $this->opalDB->updateResourcePendingLevelInProcess();

        $resourcePending = $this->opalDB->getOldestResourcePendingInProcess();
        $startTime = time();
        while(count($resourcePending) > 0 && (time() - $startTime) < 29) {
            $resourcePending = $resourcePending[0];
            if($resourcePending["AppointmentSerNum"] == "" || $resourcePending["SourceDatabaseSerNum"] == "")
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Appointment or source database missing.");

            $resources = json_decode($resourcePending["resources"], true);
            $this->_insertResources($resourcePending["AppointmentSerNum"], $resources, $resourcePending["SourceDatabaseSerNum"]);
            $this->opalDB->deleteResourcePendingInProcess($resourcePending["ID"]);
            $resourcePending = $this->opalDB->getOldestResourcePendingInProcess();
        }
    }

    /**
     * Updates the check-in for a particular appointment to checked and send the info to the push notification API. If
     * the call returns an error, a code 502 (bad gateway) is returned to the caller to inform there's a problem with
     * the push notification. Otherwise, a code 200 (all clear) is returned.
     * @param $post array - contains the source name and the external appointment ID
     */
    public function updateAppointmentCheckIn($post) {
        $this->_checkCronAccess();
        $post = HelpSetup::arraySanitization($post);
        $this->_updateAppointmentCheckIn($post);
    }

    /**
     * Update appointment in appointmentPending table.
     */
    public function updateAppointmentPending(){
        //$this->_checkCronAccess();
        //$this->opalDB->updateAppointmentPendingLevelInProcess();        
        $appointmentPendingList = $this->opalDB->getOldestAppointmentPendingInProcess();

        $startTime = time();
        while(count($appointmentPendingList) > 0 && (time() - $startTime) < 29) {
            $appointmentPending = array_shift($appointmentPendingList);
            print_r("\n\n SourceDatabase : ");
            var_dump($this->opalDB->getSourceId($appointmentPending["sourceName"]));
            print_r("\n\n SourceDatabaseSerNum : ");
            //var_dump($this->opalDB->getSourceId($appointmentPending["sourceName"])[0]['ID']);
            $appointmentPending["SourceDatabaseSerNum"] = $this->opalDB->getSourceId($appointmentPending["sourceName"])[0]['ID'];            

            $aliasInfos = $this->opalDB->getAlias('Appointment',$appointmentPending['appointmentTypeCode'], $appointmentPending['appointmentTypeDescription']);
            
            if(count($aliasInfos) == 1) { 
                print_r("\n\n************************************************");
                print_r("\n\n TYPE Code : ");
                print_r($appointmentPending['appointmentTypeCode']);
            
                print_r("\n\n TYPE Desc : ");
                print_r($appointmentPending['appointmentTypeDescription']);                

                print_r("\n\n************************************************\n\n");
                var_dump($aliasInfos);

                unset($appointmentPending["Level"]);
                unset($appointmentPending["updatedBy"]);
                unset($appointmentPending["sourceName"]);
                unset($appointmentPending["DateModified"]);                
                                                
                unset($appointmentPending["appointmentTypeCode"]);   
                unset($appointmentPending["appointmentTypeDescription"]); 
                $appointmentPending["AliasExpressionSerNum"] = $aliasInfos[0]['AliasExpressionSerNum'];
                
                print_r("\n\n Appointment ID : ");
                print_r($appointmentPending["AppointmentSerNum"]);
                //$this->opalDB->deleteAppointmentPending($appointmentPending["ID"]);
                unset($appointmentPending["ID"]);

                var_dump($appointmentPending);
                return $this->opalDB->insertAppointment($appointmentPending);
            }
        }
    }
}
