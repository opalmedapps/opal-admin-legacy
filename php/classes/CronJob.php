<?php


class CronJob extends OpalProject {

    protected $questionnaireDB;

    public function __construct() {
        $this->opalDB = new DatabaseOpal(
            OPAL_DB_HOST,
            OPAL_DB_NAME,
            OPAL_DB_PORT,
            OPAL_DB_USERNAME,
            OPAL_DB_PASSWORD,
            false,
            DEFAULT_CRON_OAUSERID,
            false
        );

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

    public function updateResourcePending() {
        $this->_checkCronAccess();
        echo "Update process level 2\r\nUser: " . $this->opalDB->getOAUserId();
        $rowCount = $this->opalDB->updateResourcePendingLevelInProcess();

        $resourcePending = $this->opalDB->getOldestResourcePendingInProcess();
        while(count($resourcePending) > 0) {
            $resourcePending = $resourcePending[0];
            if($resourcePending["AppointmentSerNum"] == "" || $resourcePending["SourceDatabaseSerNum"] == "")
                HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Appointment or source database missing.");

            $resources = json_decode($resourcePending["resources"]);
            $this->_insertResources($resourcePending["AppointmentSerNum"], $resources, $resourcePending["SourceDatabaseSerNum"]);
            $this->deleteResourcePendingInProcess($resourcePending["ID"]);


            break;
            $resourcePending = $this->opalDB->getOldestResourcePendingInProcess();
        }

        die("end of the test");
    }
}