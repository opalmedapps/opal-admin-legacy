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
     * @param array $cronArray : cron details
     * @return void
     */
    public function updateCron( $cronArray ) {

		$cronSer	    = 1;
		$nextCronDate	= $cronArray['nextCronDate'];
		$repeatUnits	= $cronArray['repeatUnits'];
		$nextCronTime	= $cronArray['nextCronTime'];
		$repeatInterval	= $cronArray['repeatInterval'];
	
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
			$cronjob_perl = "$min $hour $day $month * ".BACKEND_ABS_PATH."dataControl.pl >/dev/null 2>&1";
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
				$cronjob_perl = "*/$repeatInterval * * * * ".BACKEND_ABS_PATH."dataControl.pl >/dev/null 2>&1";
			}
			if ($repeatUnits == "Hours") { // Hourly cron
				$cronjob_perl = "$min */$repeatInterval * * * ".BACKEND_ABS_PATH."dataControl.pl >/dev/null 2>&1";
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
			
}
?>



