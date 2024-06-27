<?php


class CronJob extends OpalProject {

    protected $questionnaireDB;

    /**
     * CronJob constructor. It establish connection to the OpalDB and QuestionnaireDB. The default user is set up to
     * DEFAULT_CRON_OAUSERID.
     */
    public function __construct() {
        parent::__construct(DEFAULT_CRON_OAUSERID, false);

        $this->opalDB->setUsername(DEFAULT_CRON_USERNAME);
        $this->opalDB->setOAUserId(DEFAULT_CRON_OAUSERID);
        $this->opalDB->setSessionId(HelpSetup::makeSessionId());
        $this->opalDB->setType(SYSTEM_USER);
        $this->opalDB->setUserRole(DEFAULT_CRON_ROLE);
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
     * Retrieve the IP of a requesting container based on the service name definition.
     * @param string containerName the name of the container
     * @return string the external IP address of the container
     */
    protected function _getContainerIp($containerName) {
        $ip = gethostbyname($containerName);
        if ($ip === $containerName) {
            // gethostbyname returns the input if it can't resolve it to an IP
            return false;
        }
        return $ip;
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
        $cronContainerIp = $this->_getContainerIp(CRON_CONTAINER_SERVICE_NAME);
        HelpSetup::getModuleMethodName($moduleName, $methodeName);
        if($cronContainerIp!==HelpSetup::getUserIP()){
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
        $out=$this->opalDB->updateResourcePendingLevelInProcess();
        
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
     * Take the five most recent days of data in auditSystem table and back up the data in separate tar.gz files. The
     * files are stored in the logs folder, separated by year and months. Then the data are deleted from the table.
     * At the end, returns the number of days remaining to back up, excluding the current date. This way, the process
     * can be launch again if necessary instead of waiting for the next day.
     * WARNING : because of the nature of creating file and large amount of data, the try/catch has being implemented
     * as a safeguard.
     * @return array - number of days not backed up remaining
     */
    public function backupAuditSystem() {
        try {
        $this->_checkCronAccess();
            $dateList = $this->opalDB->getAuditSystemLastDates();
            foreach ($dateList as $date) {
                $entries = $this->opalDB->getAuditSystemEntriesByDate($date["date"]);
                if (count($entries) > 0) {
                    $folder = FRONTEND_ABS_PATH . 'logs/'.date("Y", strtotime($date["date"])).'/'.date("m", strtotime($date["date"]));
                    if(!is_dir($folder))
                        mkdir($folder, 0774, true);

                    $file = 'audit-system-'.$date["date"].'.sql';
                    $contents = str_replace("%%DATE_TO_INSERT%%", str_replace("-", "", $date["date"]), OPAL_TEMPLATE_AUDIT_SYSTEM);

                    $tempData = "";
                    foreach ($entries as $entry)
                        $tempData .= "(" .
                            "'" . str_replace("'", "\'", $entry["module"]) . "', " .
                            "'" . str_replace("'", "\'", $entry["method"]) . "', " .
                            "'" . str_replace("'", "\'", $entry["argument"]) . "', " .
                            "'" . str_replace("'", "\'", $entry["access"]) . "', " .
                            "'" . str_replace("'", "\'", $entry["ipAddress"]) . "', " .
                            "'" . str_replace("'", "\'", $entry["creationDate"]) . "', " .
                            "'" . str_replace("'", "\'", $entry["createdBy"]) . "'),\r\n";

                    $contents = str_replace("%%INSERT_DATA_HERE%%", substr($tempData, 0, -3), $contents);

                    $a = new PharData($folder."/".$file);
                    $a->addFromString($file, $contents);
                    $a->compress(Phar::GZ);
                    unlink($folder."/".$file);
                    $this->opalDB->deleteAuditSystemByDate($date["date"]);
                }
            }
            return $this->opalDB->countAuditSystemRemainingDates();
        } catch (Exception $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, $e->getMessage());
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
        $this->_checkCronAccess();
        $replacementMap = array();
        $appointmentPendingList = $this->opalDB->getOldestAppointmentPendingInProcess();
        $startTime = time();
                
        while(count($appointmentPendingList) > 0 && (time() - $startTime) < 29) {
            $today = strtotime(date("Y-m-d H:i:s"));
            $appointmentPending = array_shift($appointmentPendingList);
            $appointmentPending["SourceDatabaseSerNum"] = $this->opalDB->getSourceId($appointmentPending["sourceName"])[0]['ID'];
            $SStartDateTime = strtotime($appointmentPending["ScheduledStartTime"]);
            $aliasInfos = $this->opalDB->getAlias('Appointment',$appointmentPending['appointmentTypeCode'], $appointmentPending['appointmentTypeDescription']);
            $countAlias = count($aliasInfos);
            $toPublish = 0;
            if($countAlias == 1) {
                $toPublish = $aliasInfos[0]['AliasUpdate'];
            }
            
            if($countAlias == 1 && $toPublish == 1) {

                unset($appointmentPending["Level"]);
                unset($appointmentPending["updatedBy"]);
                unset($appointmentPending["sourceName"]);
                unset($appointmentPending["DateModified"]);
                unset($appointmentPending["appointmentTypeCode"]);
                unset($appointmentPending["appointmentTypeDescription"]);
                $appointmentPending["AliasExpressionSerNum"] = $aliasInfos[0]['AliasExpressionSerNum'];
                
                $action = 'AppointmentNew';                    
                $formatter = new \IntlDateFormatter('fr_CA', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
                $replacementMap["\$newAppointmentDateFR"] =  $formatter->format($SStartDateTime);
                $formatter = new \IntlDateFormatter('fr_CA', \IntlDateFormatter::NONE, \IntlDateFormatter::SHORT);
                $replacementMap["\$newAppointmentTimeFR"] =  $formatter->format($SStartDateTime);

                $formatter = new \IntlDateFormatter('en_CA', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
                $replacementMap["\$newAppointmentDateEN"] =  $formatter->format($SStartDateTime);
                $formatter = new \IntlDateFormatter(locale: 'en_CA', dateType: \IntlDateFormatter::NONE, timeType: \IntlDateFormatter::SHORT, pattern: "h:mm a");
                $replacementMap["\$newAppointmentTimeEN"] =  $formatter->format($SStartDateTime);

                $this->opalDB->deleteAppointmentPending($appointmentPending["ID"]);
                unset($appointmentPending["ID"]);
                $sourceId = $this->opalDB->insertAppointment($appointmentPending);
                
                if ($SStartDateTime >= $today) {
                    $this->_notifyChange($appointmentPending, $action, $replacementMap,$sourceId);
                }                
            }
        }
    }
}