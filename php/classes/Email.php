<?php

/**
 * Email API class
 *
 */
class Email extends Module {
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

        parent::__construct(MODULE_EMAIL, $guestStatus);
    }

    /**
     *
     * Gets a list of existing email templates
     *
     * @return array $emailList : list of existing email templates
     */
    public function getEmailTemplates() {
        $this->checkReadAccess();
        $emailList = array();
        try {
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
            $query = $this->host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for email. " . $e->getMessage());
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
        $this->checkReadAccess($serial);
        $emailDetails = array();
        try {
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
            $query = $this->host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for email. " . $e->getMessage());
        }
    }

    /**
     *
     * Gets the types of email templates from the database
     *
     * @return array $types : the types of email
     */
    public function getEmailTypes () {
        $this->checkReadAccess();
        $types = array();
        try {
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
				ec.EmailTypeSerNum IS NOT NULL
		";
            $query = $this->host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for email. " . $e->getMessage());
        }
    }

    /**
     *
     * Inserts an email template into the database
     *
     * @param array $emailDetails : the email details
     */
    public function insertEmail($emailDetails){
        $this->checkWriteAccess($emailDetails);

        $subject_EN 	= $emailDetails['subject_EN'];
        $subject_FR 	= $emailDetails['subject_FR'];
        $body_EN 		= $emailDetails['body_EN'];
        $body_FR 		= $emailDetails['body_FR'];
        $type 			= $emailDetails['type'];
        $userSer 		= $emailDetails['user']['id'];
        $sessionId 		= $emailDetails['user']['sessionid'];

        try {
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
            $query = $this->host_db_link->prepare( $sql );
            $query->execute();
        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for email. " . $e->getMessage());

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
        $this->checkWriteAccess($emailDetails);

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
            $query = $this->host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for email. " . $e->getMessage());

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
        $this->checkDeleteAccess(array($serial, $user));

        $response = array(
            'value'     => 0,
            'message'   => ''
        );

        $userSer = $user['id'];
        $sessionId = $user['sessionid'];

        try {
            $sql = "
				DELETE FROM
					EmailControl
				WHERE
					EmailControl.EmailControlSerNum = $serial 
			";
            $query = $this->host_db_link->prepare( $sql );
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
            $query = $this->host_db_link->prepare( $sql );
            $query->execute();

            $response['value'] = 1;
            return $response;

        } catch( PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for email. " . $e->getMessage());

        }


    }

   

    /**
     * Gets list logs of emails during one or many cron sessions
     */
    public function getEmailListLogs($emailIds) {
        $this->checkReadAccess($emailIds);
        foreach ($emailIds as &$id) {
            $id = intval($id);
        }

        return $this->opalDB->getEmailsLogs($emailIds);

        $serials = implode(',', $emailIds);
        try {
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

            $query = $this->host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
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
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for email. " . $e->getMessage());
        }
    }
}