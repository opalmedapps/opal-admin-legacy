<?php
/**
 * Cron class
 *
 */
class Cron {

    /**
     *
     * Gets cron details in the database
     *
     * @return array $cronDetails : the cron details
     */    
	public function getCronDetails () {
		$cronDetails = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

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
			
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
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
			echo $e->getMessage();
			return $cronDetails;
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

		$cronSer	    = 1;
		$nextCronDate	= $cronDetails['nextCronDate'];
		$repeatUnits	= $cronDetails['repeatUnits'];
		$nextCronTime	= $cronDetails['nextCronTime'];
		$repeatInterval	= $cronDetails['repeatInterval'];
	
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

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

			$query = $host_db_link->prepare( $sql );
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
			return $e->getMessage();
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
			return $e->getMessage();
		}
	}

	/**
     *
     * Gets chart cron logs
     *
     * @return array $cronLogs : the cron logs for highcharts
     */
    public function getCronChartLogs () {
        $cronLogs = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			
			// Go through each content type
			
			/* Appointments */
			$appointmentSeries = array (
				'name'	=> 'Appointment',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT 
                    apmh.CronLogSerNum,
                    COUNT(apmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    AppointmentMH apmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = apmh.CronLogSerNum
                AND apmh.CronLogSerNum IS NOT NULL
                GROUP BY
                    apmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC 
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $aliasDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($appointmentSeries['data'], $aliasDetail);
            }

            /* Documents */
			$documentSeries = array (
				'name'	=> 'Document',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT 
                    docmh.CronLogSerNum,
                    COUNT(docmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    DocumentMH docmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = docmh.CronLogSerNum
                AND docmh.CronLogSerNum IS NOT NULL
                GROUP BY
                    docmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC 
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $aliasDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($documentSeries['data'], $aliasDetail);
            }

            /* Tasks */
			$taskSeries = array (
				'name'	=> 'Task',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT 
                    tmh.CronLogSerNum,
                    COUNT(tmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    TaskMH tmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = tmh.CronLogSerNum
                AND tmh.CronLogSerNum IS NOT NULL
                GROUP BY
                    tmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC 
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $aliasDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($taskSeries['data'], $aliasDetail);
            }

            /* Announcements */
			$announcementSeries = array (
				'name'	=> 'Announcement',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT
                    anmh.CronLogSerNum,
                    COUNT(anmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    AnnouncementMH anmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = anmh.CronLogSerNum
                AND anmh.CronLogSerNum IS NOT NULL
                GROUP BY
                    anmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC  
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $postDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($announcementSeries['data'], $postDetail);
            }

            /* Treatment Team Messages */
			$txTeamMessageSeries = array (
				'name'	=> 'Treatment Team Message',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT
                    ttmmh.CronLogSerNum,
                    COUNT(ttmmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    TxTeamMessageMH ttmmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = ttmmh.CronLogSerNum
                AND ttmmh.CronLogSerNum IS NOT NULL
                GROUP BY
                    ttmmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC  
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $postDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($txTeamMessageSeries['data'], $postDetail);
            }

            /* Patents for patients */
			$pfpSeries = array (
				'name'	=> 'Patients for Patients',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT
                    pfpmh.CronLogSerNum,
                    COUNT(pfpmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    PatientsForPatientsMH pfpmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = pfpmh.CronLogSerNum
                AND pfpmh.CronLogSerNum IS NOT NULL
                GROUP BY
                    pfpmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC  
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $postDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($pfpSeries['data'], $postDetail);
            }

            /* Educational Material */
			$educationalMaterialSeries = array (
				'name'	=> 'Educational Material',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT
	                emmh.CronLogSerNum,
	                COUNT(emmh.CronLogSerNum),
	                cl.CronDateTime
	            FROM
	                EducationalMaterialMH emmh,
	                CronLog cl
	            WHERE
	                cl.CronStatus = 'Started'
	            AND cl.CronLogSerNum = emmh.CronLogSerNum
	            AND emmh.CronLogSerNum IS NOT NULL
	            GROUP BY
	                emmh.CronLogSerNum,
	                cl.CronDateTime
	            ORDER BY 
	                cl.CronDateTime ASC   
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $educationalMaterialDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($educationalMaterialSeries['data'], $educationalMaterialDetail);
            }

            /* Notifications */
			$notificationSeries = array (
				'name'	=> 'Notification',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT 
                    ntmh.CronLogSerNum,
                    COUNT(ntmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    NotificationMH ntmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = ntmh.CronLogSerNum
                AND ntmh.CronLogSerNum IS NOT NULL
                GROUP BY 
                    ntmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC   
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $notificationDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($notificationSeries['data'], $notificationDetail);
            }

            /* Test results */
			$testResultSeries = array (
				'name'	=> 'Test Result',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT
                    trmh.CronLogSerNum,
                    COUNT(trmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    TestResultMH trmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = trmh.CronLogSerNum
                AND trmh.CronLogSerNum IS NOT NULL
                GROUP BY
                    trmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC  
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $testResultDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($testResultSeries['data'], $testResultDetail);
            }

            /* Email */
			$emailSeries = array (
				'name'	=> 'Email',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT 
                    emmh.CronLogSerNum,
                    COUNT(emmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    EmailLogMH emmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = emmh.CronLogSerNum
                AND emmh.CronLogSerNum IS NOT NULL
                GROUP BY 
                    emmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC 
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $emailDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($emailSeries['data'], $emailDetail);
            }

            /* Email */
			$legacyQuestionnaireSeries = array (
				'name'	=> 'Legacy Questionnaire',
				'data' 	=> array()
			);

			$sql = "
				SELECT DISTINCT
                    lqmh.CronLogSerNum,
                    COUNT(lqmh.CronLogSerNum),
                    cl.CronDateTime
                FROM
                    QuestionnaireMH lqmh,
                    CronLog cl
                WHERE
                    cl.CronStatus = 'Started'
                AND cl.CronLogSerNum = lqmh.CronLogSerNum
                AND lqmh.CronLogSerNum IS NOT NULL
                GROUP BY
                    lqmh.CronLogSerNum,
                    cl.CronDateTime
                ORDER BY 
                    cl.CronDateTime ASC  
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $legacyQuestionnaireDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                array_push($legacyQuestionnaireSeries['data'], $legacyQuestionnaireDetail);
            }

            // push all conter
            array_push($cronLogs, $appointmentSeries, $documentSeries, $taskSeries, $announcementSeries, $txTeamMessageSeries,
            	$pfpSeries, $educationalMaterialSeries, $notificationSeries, $testResultSeries, $emailSeries, $legacyQuestionnaireSeries);

            return $cronLogs;
        } catch( PDOException $e) {
			echo $e->getMessage();
			return $cronLogs;
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
    	$cronLogs = array();

   		$cronLogs['appointment'] = (!empty($contents['Appointment'])) ? Alias::getAliasListLogs($contents['Appointment'], 'Appointment') : array();
   		$cronLogs['document'] = (!empty($contents['Document'])) ? Alias::getAliasListLogs($contents['Document'], 'Document') : array();
   		$cronLogs['task'] = (!empty($contents['Task'])) ? Alias::getAliasListLogs($contents['Task'], 'Task') : array();
   		$cronLogs['announcement'] = (!empty($contents['Announcement'])) ? Post::getPostListLogs($contents['Announcement'], 'Announcement') : array();
   		$cronLogs['txTeamMessage'] = (!empty($contents['Treatment Team Message'])) ? Post::getPostListLogs($contents['Treatment Team Message'], 'Treatment Team Message') : array();
   		$cronLogs['pfp'] = (!empty($contents['Patients for Patients'])) ? Post::getPostListLogs($contents['Patients for Patients'], 'Patients for Patients') : array();
   		$cronLogs['educationalMaterial'] = (!empty($contents['Educational Material'])) ? EduMaterial::getEducationalMaterialListLogs($contents['Educational Material']) : array();
   		$cronLogs['email'] = (!empty($contents['Email'])) ? Email::getEmailListLogs($contents['Email']) : array();
   		$cronLogs['legacyQuestionnaire'] = (!empty($contents['Legacy Questionnaire'])) ? LegacyQuestionnaire::getLegacyQuestionnaireListLogs($contents['Legacy Questionnaire']) : array();
   		$cronLogs['notification'] = (!empty($contents['Notification'])) ? Notification::getNotificationListLogs($contents['Notification']) : array();
   		$cronLogs['testResult'] = (!empty($contents['Test Result'])) ? TestResult::getTestResultListLogs($contents['Test Result']) : array();

   		return $cronLogs;
    }
}
?>



