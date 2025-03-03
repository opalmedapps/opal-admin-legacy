<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Cron class
 *
 */
class Cron extends Module {
    private $host_db_link;

    public function __construct($guestStatus = false) {
        // Setup class-wide database connection with or without SSL
        if(USE_SSL == 1){
            $this->host_db_link = new PDO(
                OPAL_DB_DSN,
                OPAL_DB_USERNAME,
                OPAL_DB_PASSWORD,
                array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    PDO::MYSQL_ATTR_SSL_CA => SSL_CA,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
                )
            );
        }else{
            $this->host_db_link = new PDO(
                OPAL_DB_DSN,
                OPAL_DB_USERNAME,
                OPAL_DB_PASSWORD,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
        }
        $this->host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        
        parent::__construct(MODULE_CRON_LOG, $guestStatus);
    }

    /**
     *
     * Gets cron details in the database
     *
     * @return array $cronDetails : the cron details
     */
    public function getCronDetails () {
        $this->checkReadAccess();
        $cronDetails = array();
        try {
            $sql = "
				SELECT DISTINCT 
					Cron.CronSerNum,
					Cron.NextCronDate, 
					Cron.RepeatUnits, 
					DATE_FORMAT(Cron.NextCronTime, '%H:%i'), 
					Cron.RepeatInterval
				FROM 
					Cron 
			";

            $query = $this->host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

            $cronSer	    = $data[0];
            $nextCronDate	= $data[1];
            $repeatUnits	= $data[2];
            $nextCronTime	= $data[3];
            $repeatInterval = intval($data[4]);

            $cronDetails = array(
                'cronSer'	=> $cronSer,
                'nextCronDate' 	=> $nextCronDate,
                'repeatUnits' 	=> $repeatUnits,
                'nextCronTime' 	=> $nextCronTime,
                'repeatInterval'=> $repeatInterval
            );

            return $cronDetails;

        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for cron log. " . $e->getMessage());
        }
    }

    /**
     *
     * Updates cron settings in the database and sets the crontab
     *
     * @param array $cronDetails : cron details
     * @return void
     */
    public function updateCron( $cronDetails ) {
        $this->checkWriteAccess($cronDetails);

        $cronSer	    = 1;
        $nextCronDate	= $cronDetails['nextCronDate'];
        $repeatUnits	= $cronDetails['repeatUnits'];
        $nextCronTime	= $cronDetails['nextCronTime'];
        $repeatInterval	= $cronDetails['repeatInterval'];

        try {
            $sql ="
				UPDATE 
					Cron
	 			SET 
					Cron.NextCronDate 	= '$nextCronDate', 
					Cron.RepeatUnits 	= '$repeatUnits', 
					Cron.NextCronTime 	= '$nextCronTime',
					Cron.RepeatInterval	= '$repeatInterval' 
				WHERE 
					Cron.CronSerNum 	= $cronSer
			";

            $query = $this->host_db_link->prepare( $sql );
            $query->execute();

            /* Build our custom cronjobs for crontab
             * In this case, we are concerned about triggering the next cron
             *
             * One cronjob will be the execution of the dataControl.pl script itself.
             * And the second job will be a php script (updateCrontab() method below)
             * that modifies the first job based on the repeat options.
             * Again, we are writing two cronjobs that will fire on the nextCron variables.
             */

            // Parse date
            // Current Format: yyyy-MM-dd
            $datePieces = explode("-", $nextCronDate); // split the dashes
            $year = $datePieces[0];
            $month = $datePieces[1];
            $day = $datePieces[2];

            // Parse time
            // Current Format: hh:mm
            $timePieces = explode(":", $nextCronTime); // split the colons
            $hour = $timePieces[0];
            $min = $timePieces[1];

            // Our cron jobs
            $cronjob_perl = "$min $hour $day $month * ".BACKEND_ABS_PATH."dataControl.pl >/dev/null 2>>".BACKEND_ABS_PATH."logs/executions.log";
            $cronjob_php = "$min $hour $day $month * /usr/bin/php ".FRONTEND_ABS_PATH."php/cron/update.crontab.php $cronSer >/dev/null 2>&1";

            $cronjobs = array($cronjob_perl,$cronjob_php);

            // Construct our crontab manager
            $crontab = new CrontabManager();

            // Remove any existing cron jobs related to the dataCrontrol.pl script
            // and the update.crontab.php script because if we've reached this point,
            // we've changed the cron control settings, so we need to get rid of any
            // existing control settings. We do this using regular expressions.
            $cron_regex = array(
                BACKEND_ABS_PATH_REGEX."dataControl.pl"."/",
                FRONTEND_ABS_PATH_REGEX."php\/cron\/update.crontab.php ".$cronSer."/"
            );
            // If crontab is not empty, remove cronjobs
            if ($crontab->crontab_exists()) $crontab->remove_cronjob($cron_regex);

            // Append our new cronjobs to the crontab
            $crontab->append_cronjob($cronjobs);

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for cron log. " . $e->getMessage());
        }
    }

    /**
     *
     * Updates the crontab
     *
     * @param integer $cronSer : the cron serial number
     * @return void
     */

    public function updateCrontab($cronSer) {

        $nextCronDate;
        $repeatUnits;
        $nextCronTime;
        $repeatInterval;

        try {

            $cronObj = new Cron();
            $cronDetails = $cronObj->getCronDetails($cronSer);

            $nextCronDate   = $cronDetails['nextCronDate'];
            $repeatUnits    = $cronDetails['repeatUnits'];
            $nextCronTime   = $cronDetails['nextCronTime'];
            $repeatInterval = $cronDetails['repeatInterval'];

            // Initialize a date object for setting the next scheduled
            // cron depending on the repeat options
            $datetime = new DateTime($nextCronDate." ".$nextCronTime);

            $newNextCronDate;
            $newNextCronTime;

            /*
             * In this case, we are concerned about modifying the existing cronjob
             * that calls the dataControl.pl script. If we've reached this point,
             * this means the scheduled nextCron control setting has fired and
             * so we pick up the neccesary settings to properly automate any
             * future execution of our dataControl.pl script. In other words
             * modify the cronjob based on the repeat options setting.
             */

            // Parse date
            // Current Format: yyyy-MM-dd
            $datePieces = explode("-", $nextCronDate); // split the dashes
            $year = $datePieces[0];
            $month = $datePieces[1];
            $day = $datePieces[2];

            // Parse time
            // Current Format: hh:mm
            $timePieces = explode(":", $nextCronTime); // split the colons
            $hour = $timePieces[0];
            $min = $timePieces[1];

            // Initialize our cron job strings
            $cronjob_perl;
            $cronjob_php;

            // Repeat Options
            if ($repeatUnits == "Minutes") { // Minute cron
                $cronjob_perl = "*/$repeatInterval * * * * ".BACKEND_ABS_PATH."dataControl.pl >/dev/null 2>>".BACKEND_ABS_PATH."logs/executions.log";
            }
            if ($repeatUnits == "Hours") { // Hourly cron
                $cronjob_perl = "$min */$repeatInterval * * * ".BACKEND_ABS_PATH."dataControl.pl >/dev/null 2>>".BACKEND_ABS_PATH."logs/executions.log";
            }

            $cronjobs = array($cronjob_perl);

            // Construct our crontab manager
            $crontab = new CrontabManager();

            // Remove any existing cron jobs related to the dataCrontrol.pl script
            // and the update.crontab.php script because if we've reached this point,
            // the cronjob settings need to be modified, so we need to get rid of any
            // existing control settings. We do this using regular expressions.
            $cron_regex = array(
                BACKEND_ABS_PATH_REGEX."dataControl.pl"."/",
                FRONTEND_ABS_PATH_REGEX."php\/cron\/update.crontab.php ".$cronSer."/"
            );
            // If crontab is not empty, remove cronjobs
            if ($crontab->crontab_exists()) $crontab->remove_cronjob($cron_regex);

            // Append our new cronjobs to the crontab
            $crontab->append_cronjob($cronjobs);

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for cron log. " . $e->getMessage());
        }
    }

   
    /**
     *
     * Gets list logs of content during one or many cron sessions
     *
     * @param array $contents : a list of each content with their cron serials
     * @return array $cronLogs : the cron logs for table view
     */
    public function getCronListLogs ($contents) {
        $this->checkReadAccess($contents);
        $cronLogs = array();
        $moduleList = $this->opalDB->getAvailableRolesModules();
        $moduleArr = array();
        foreach($moduleList as $module) {
            array_push($moduleArr, $module["ID"]);
        }

        if(in_array(MODULE_ALIAS, $moduleArr) && HelpSetup::validateReadModule(MODULE_ALIAS)) {
            $cronLogs['appointment'] = (!empty($contents['Appointment'])) ? $this->opalDB->getAppointmentsLogs($contents['Appointment']) : array();
            $cronLogs['document'] = (!empty($contents['Document'])) ? $this->opalDB->getDocumentsLogs($contents['Document']) : array();
            $cronLogs['task'] = (!empty($contents['Task'])) ? $this->opalDB->getTasksLogs($contents['Task']) : array();
        }
        if(in_array(MODULE_POST, $moduleArr) && HelpSetup::validateReadModule(MODULE_POST)) {
            $cronLogs['announcement'] = (!empty($contents['Announcement'])) ? $this->opalDB->getAnnouncementChartLogsByIds($contents['Announcement']) : array();
            $cronLogs['txTeamMessage'] = (!empty($contents['Treatment Team Message'])) ? $this->opalDB->getTTMChartLogsByIds($contents['Treatment Team Message']) : array();
            $cronLogs['pfp'] = (!empty($contents['Patients for Patients'])) ? $this->opalDB->getPFPChartLogsByIds($contents['Patients for Patients']) : array();
        }
        if(in_array(MODULE_EMAIL, $moduleArr) && HelpSetup::validateReadModule(MODULE_EMAIL)) {
            $cronLogs['email'] = (!empty($contents['Email'])) ? $this->opalDB->getEmailsLogs($contents['Email']) : array();
        }
        if(in_array(MODULE_EDU_MAT, $moduleArr) && HelpSetup::validateReadModule(MODULE_EDU_MAT)) {
            $cronLogs['educationalMaterial'] = (!empty($contents['Educational Material'])) ? $this->opalDB->getEduMaterialLogs($contents['Educational Material']) : array();
        }
        if(in_array(MODULE_QUESTIONNAIRE, $moduleArr) && HelpSetup::validateReadModule(MODULE_QUESTIONNAIRE)) {
            $cronLogs['legacyQuestionnaire'] = (!empty($contents['Legacy Questionnaire'])) ? $this->opalDB->getQuestionnaireListLogs($contents['Legacy Questionnaire']) : array();
        }
        if(in_array(MODULE_NOTIFICATION, $moduleArr) && HelpSetup::validateReadModule(MODULE_NOTIFICATION)) {
            $cronLogs['notification'] = (!empty($contents['Notification'])) ? $this->opalDB->getNotificationsLogs($contents['Notification']) : array();
        }
        if(in_array(MODULE_TEST_RESULTS, $moduleArr) && HelpSetup::validateReadModule(MODULE_TEST_RESULTS)) {
            $cronLogs['testResult'] = (!empty($contents['Test Result'])) ? $this->opalDB->getTestResultsLogs($contents['Test Result']) : array();
        }

        return $cronLogs;
    }
}