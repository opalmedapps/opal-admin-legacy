<?php


class CronJob extends OpalProject {

    protected $questionnaireDB;

    /**
     * CronJob constructor. It establish connection to the OpalDB and QuestionnaireDB. The default user is set up to
     * DEFAULT_CRON_OAUSERID.
     */
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
     * Validate and sanitize appointment check-in info.
     * @param $post - data for the resource to validate
     * @param $source - contains source details
     * @param $appointment - contains appointment details (if exists)
     * Validation code :    Error validation code is coded as an int of 3 bits (value from 0 to 7). Bit information
     *                      are coded from right to left:
     *                      1: source name missing or invalid
     *                      2: appointment missing
     *                      3: Duplicate appointments have being found. Contact the administrator ASAP.
     * @return string - error code
     */
    protected function _validateAppointmentCheckIn(&$post, &$source, &$appointment, &$patientInfo) {
        $errCode = "";

        if (is_array($post)) {
            // 1st bit
            if (!array_key_exists("source", $post) || $post["source"] == "") {
                if(!array_key_exists("source", $post)) $post["source"] = "";
                $errCode = "1" . $errCode;
            }
            else {
                $source = $this->opalDB->getSourceDatabaseDetails($post["source"]);
                if(count($source) < 1) {
                    $errCode = "1" . $errCode;
                    $source = array();
                }
                else if(count($source) == 1) {
                    $source = $source[0];
                    $errCode = "0" . $errCode;
                }
                else
                    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Duplicates sources found. Contact your administrator.");
            }

            // 2nd bit
            if (!array_key_exists("appointment", $post) || $post["appointment"] == "") {
                if(!array_key_exists("appointment", $post)) $post["appointment"] = "";
                $errCode = "1" . $errCode;
            }
            else
                $errCode = "0" . $errCode;

            // 3rd bit
            if(bindec($errCode) == 0) {
                $appointment = $this->opalDB->getAppointmentForResource($post["appointment"], $source["SourceDatabaseSerNum"]);
                if(count($appointment) > 1)
                    $errCode = "1" . $errCode;
                else {
                    if(count($appointment) == 1)
                        $appointment = $appointment[0];
                    $errCode = "0" . $errCode;
                }

                // 4th bit
                $patientInfo = $this->opalDB->getFirstMrnSiteBySourceAppointment($post["source"], $post["appointment"]);
                if(count($patientInfo) < 1)
                    $errCode = "1" . $errCode;
                else
                    $errCode = "0" . $errCode;

            } else
                $errCode = "1" . $errCode;

        } else {
            $post = array(
                "source"=>"",
                "appointment"=>"",
            );
            $errCode .= "1111";
        }

        return $errCode;
    }

    public function updateAppointmentCheckIn($post) {
        $this->_checkCronAccess();
        $errCode = $this->_validateAppointmentCheckIn($post, $source, $appointment, $patientInfo);
        $errCode = bindec($errCode);
        if ($errCode != 0)
            HelpSetup::returnErrorMessage(HTTP_STATUS_BAD_REQUEST_ERROR, array("validation" => $errCode));

        $rowCount = $this->opalDB->updateCheckInForAppointment($post["source"], $post["appointment"]);
        if($rowCount > 0) {
            $api = new ApiCall();
            $api->setUrl("");
            $api->setPostFields(array(
                "mrn"=>$patientInfo["mrn"],
                "site"=>$patientInfo["site"],
            ));

        }
    }
}