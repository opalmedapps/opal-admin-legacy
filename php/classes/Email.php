<?php

/**
 * Email API class
 * 
 */
 class Email {

	/**
	*
	* Gets a list of existing email templates
	*
	* @return array $emailList : list of existing email templates
	*/
	public function getEmailTemplates() {
		$emailList = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT DISTINCT
					ec.EmailControlSerNum,
					ec.Subject_EN,
					ec.Subject_FR,
					ec.Body_EN,
					ec.Body_FR,
					et.EmailTypeId
				FROM
					EmailControl ec,
					EmailType et
				WHERE
					ec.EmailTypeSerNum = et.EmailTypeSerNum
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

				$serial 		= $data[0];
				$subject_EN 	= $data[1];
				$subject_FR 	= $data[2];
				$body_EN 		= $data[3];
				$body_FR 		= $data[4];
				$type 			= $data[5];

				$emailArray = array(
					'serial'		=> $serial,
					'subject_EN'	=> $subject_EN,
					'subject_FR' 	=> $subject_FR,
					'body_EN'		=> $body_EN,
					'body_FR'		=> $body_FR,
					'type'			=> $type
				);

				array_push($emailList, $emailArray);
			}

			return $emailList;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $notificationList;
		}
	}

	/**
	*
	* Get details of a particular email template
	*
	* @param integer $serial : the email control serial number
	* @return array $emailDetails : details of an email
	*/
	public function getEmailDetails ($serial) {
		$emailDetails = array();
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				SELECT DISTINCT
					ec.Subject_EN,
					ec.Subject_FR,
					ec.Body_EN,
					ec.Body_FR,
					ec.EmailTypeSerNum
				FROM
					EmailControl ec
				WHERE
					ec.EmailControlSerNum = $serial
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$subject_EN 	= $data[0];
			$subject_FR 	= $data[1];
			$body_EN 		= $data[2];
			$body_FR 		= $data[3];
			$type 			= $data[4];

			$emailDetails = array(
				'serial' 		=> $serial,
				'subject_EN'	=> $subject_EN,
				'subject_FR'	=> $subject_FR,
				'body_EN'		=> $body_EN,
				'body_FR'		=> $body_FR,
				'type'			=> $type
			);

			return $emailDetails;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $notificationDetails;
		}
	}

	/**
	*
	* Gets the types of email templates from the database
	*
	* @return array $types : the types of email
	*/ 
	public function getEmailTypes () {
		$types = array();
		try {
		$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
		$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$sql = "
			SELECT DISTINCT 
				et.EmailTypeSerNum,
				et.EmailTypeName,
				et.EmailTypeId
			FROM
				EmailType et
			LEFT JOIN EmailControl ec
			ON ec.EmailTypeSerNum = et.EmailTypeSerNum
			WHERE
				ec.EmailTypeSerNum IS NULL
		";
		$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$query->execute();

		while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
			$typeArray = array(
				'serial'	=> $data[0],
				'name'		=> $data[1],
				'id'		=> $data[2]
			);

			array_push($types, $typeArray);
		}

		return $types;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $types;
		}
	}

	/**
	*
	* Inserts an email template into the database
	*
	* @param array $emailDetails : the email details
	*/
	public function insertEmail($emailDetails){

		$subject_EN 	= $emailDetails['subject_EN'];
		$subject_FR 	= $emailDetails['subject_FR'];
		$body_EN 		= $emailDetails['body_EN'];
		$body_FR 		= $emailDetails['body_FR'];
		$type 			= $emailDetails['type'];
		$userSer 		= $emailDetails['user']['id'];
		$sessionId 		= $emailDetails['user']['sessionid'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
				INSERT INTO 
					EmailControl (
						Subject_EN,
						Subject_FR,
						Body_EN,
						Body_FR,
						EmailTypeSerNum,
						DateAdded,
						LastUpdatedBy,
						SessionId
					)
				VALUES (
					\"$subject_EN\",
					\"$subject_FR\",
					\"$body_EN\",
					\"$body_FR\",
					'$type',
					NOW(),
					'$userSer',
					'$sessionId'
				)
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();
		} catch( PDOException $e) {
			return $e->getMessage();
		}

	}

	/**
	*
	* Updates the email template in the database
	*
	* @param array $emailDetails : the email template details
	* @return array $response : response
	*/
	public function updateEmail ($emailDetails) {

		$subject_EN 	= $emailDetails['subject_EN'];
		$subject_FR 	= $emailDetails['subject_FR'];
		$body_EN 		= $emailDetails['body_EN'];
		$body_FR 		= $emailDetails['body_FR'];
		$serial 		= $emailDetails['serial'];
		$userSer 		= $emailDetails['user']['id'];
		$sessionId 		= $emailDetails['user']['sessionid'];

		$response = array(
            'value'     => 0,
            'message'   => ''
        );

        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				UPDATE
					EmailControl
				SET
					EmailControl.Subject_EN 		= \"$subject_EN\",
					EmailControl.Subject_FR 		= \"$subject_FR\",
					EmailControl.Body_EN 			= \"$body_EN\",
					EmailControl.Body_FR 		 	= \"$body_FR\",
					EmailControl.LastUpdatedBy 		= '$userSer',
					EmailControl.SessionId 			= '$sessionId'
				WHERE
					EmailControl.EmailControlSerNum = $serial
			";
			$query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

	    } catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response;
		}


	}

	/**
	*
	* Deletes an email template from the database 
	*
	* @param integer $serial : the email control serial number
	* @param object $user : the current user in session
	* @return array $response : response
	*/
	public function deleteEmail ($serial, $user) {

		$response = array(
            'value'     => 0,
            'message'   => ''
		);
		
		$userSer = $user['id'];
		$sessionId = $user['sessionid'];

        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
				DELETE FROM
					EmailControl
				WHERE
					EmailControl.EmailControlSerNum = $serial 
			";
			$query = $host_db_link->prepare( $sql );
			$query->execute();
			
			$sql = "
                UPDATE EmailControlMH
                SET 
                    EmailControlMH.LastUpdatedBy = '$userSer',
                    EmailControlMH.SessionId = '$sessionId'
                WHERE
                    EmailControlMH.EmailControlSerNum = $serial
                ORDER BY EmailControlMH.RevSerNum DESC 
                LIMIT 1
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

        } catch( PDOException $e) {
		    $response['message'] = $e->getMessage();
			return $response;
		}


	}

	/**
     *
     * Gets chart logs of a email or emails
     *
     * @param integer $serial : the email serial number
     * @return array $emailLogs : the email logs for highcharts
     */
    public function getEmailChartLogs ($serial) {
        $emailLogs = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = null;
            if (!$serial) {
            	 $sql = "
                    SELECT DISTINCT 
                        emmh.CronLogSerNum,
                        COUNT(emmh.CronLogSerNum),
                        cl.CronDateTime,
                        emt.EmailTypeName
                    FROM
                        EmailLogMH emmh,
                        CronLog cl,
                        EmailControl emc,
                        EmailType emt
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = emmh.CronLogSerNum
                    AND emmh.CronLogSerNum IS NOT NULL
                    AND emmh.EmailControlSerNum = emc.EmailControlSerNum
                    AND emc.EmailTypeSerNum = emt.EmailTypeSerNum
                    GROUP BY 
                        emmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC
                ";

            }
            else {
                $sql = "
                    SELECT DISTINCT 
                        emmh.CronLogSerNum,
                        COUNT(emmh.CronLogSerNum),
                        cl.CronDateTime,
                        emt.EmailTypeName
                    FROM
                        EmailLogMH emmh,
                        CronLog cl,
                        EmailControl emc,
                        EmailType emt
                    WHERE
                        cl.CronStatus = 'Started'
                    AND cl.CronLogSerNum = emmh.CronLogSerNum
                    AND emmh.CronLogSerNum IS NOT NULL
                    AND emmh.EmailControlSerNum = '$serial'
                    AND emmh.EmailControlSerNum = emc.EmailControlSerNum
                    AND emc.EmailTypeSerNum = emt.EmailTypeSerNum
                    GROUP BY 
                        emmh.CronLogSerNum,
                        cl.CronDateTime
                    ORDER BY 
                        cl.CronDateTime ASC
                ";
            }

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $emailSeries = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $seriesName = $data[3];
                $emailDetail = array (
                    'x' => $data[2],
                    'y' => intval($data[1]),
                    'cron_serial' => $data[0]
                );
                if(!isset($emailSeries[$seriesName])) {
                    $emailSeries[$seriesName] = array(
                        'name'  => $seriesName,
                        'data'  => array()
                    );
                }
                array_push($emailSeries[$seriesName]['data'], $emailDetail);
            }

            foreach ($emailSeries as $seriesName => $series) {
                array_push($emailLogs, $series);
            }

            return $emailLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $emailLogs;
        }
    }

    /**
     *
     * Gets list logs of emails during one or many cron sessions
     *
     * @param array $serials : a list of cron log serial numbers
     * @return array $emailLogs : the email logs for table view
     */
    public function getEmailListLogs ($serials) {
        $emailLogs = array();
        $serials = implode(',', $serials);
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    emmh.EmailControlSerNum,
                    emmh.EmailRevSerNum,
                    emmh.CronLogSerNum,
                    emmh.PatientSerNum,
                    emt.EmailTypeName,
                    emmh.DateAdded,
                    emmh.ModificationAction
                FROM
                    EmailLogMH emmh,
                    EmailControl emc,
                    EmailType emt
                WHERE
                    emmh.EmailControlSerNum  = emc.EmailControlSerNum
                AND emc.EmailTypeSerNum      = emt.EmailTypeSerNum 
                AND emmh.CronLogSerNum              IN ($serials)
            ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $logDetails = array (
                    'control_serial'        => $data[0],
                    'revision'              => $data[1],
                    'cron_serial'           => $data[2],
                    'patient_serial'        => $data[3],
                    'type'                  => $data[4],
                    'date_added'            => $data[5],
                    'mod_action'            => $data[6]
                );

                array_push($emailLogs, $logDetails);
            }

            return $emailLogs;

        } catch( PDOException $e) {
            echo $e->getMessage();
            return $emailLogs;
        }
    }



}