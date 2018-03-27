<?php

/**
 * Patient class
 *
 */
class Patient {

    /**
     *
     * Updates the patient transfer flags in the database
     *
     * @param array $patientList : a list of patients
	 * @return void
     */
    public function updatePatientTransferFlags( $patientList ) {

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			foreach ($patientList as $patient) {
				$patientTransfer = $patient['transfer'];
				$patientSer = $patient['serial'];
				$sql = "
					UPDATE
						PatientControl
					SET
						PatientControl.PatientUpdate = $patientTransfer
					WHERE
						PatientControl.PatientSerNum = $patientSer
				";

				$query = $host_db_link->prepare( $sql );
				$query->execute();
			}
		} catch( PDOException $e) {
			return $e->getMessage();
		}
    }

    /**
     *
     * Gets a list of existing patients in the database
     *
     * @return array $patientList : the list of existing patients 
     */
    public function getPatients() {
        $patientList = array();
        try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$sql = "
                SELECT DISTINCT
                    pc.PatientSerNum,
                    pc.PatientUpdate,
                    pt.FirstName,
                    pt.LastName,
                    pt.PatientId,
                    pc.LastTransferred,
					pt.BlockedStatus,
					usr.Username,
					pt.email
                FROM
                    PatientControl pc,
                    Patient pt,
					Users usr
                WHERE
                    pt.PatientSerNum = pc.PatientSerNum
				AND pt.PatientSerNum 	= usr.UserTypeSerNum
				AND usr.UserType 		= 'Patient'
				
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $patientArray = array(
                    'serial'            => $data[0],
                    'transfer'          => $data[1],
                    'name'              => "$data[2] $data[3]",
                    'patientid'         => $data[4],
                    'lasttransferred'   => $data[5],
					'disabled' 			=> intval($data[6]),
					'uid'				=> $data[7],
					'email'				=> $data[8]
                );

                array_push($patientList, $patientArray);
            }

            return $patientList;
		} catch (PDOException $e) {
			echo $e->getMessage();
			return $patientList;
		}
	}

    /**
     *
     * Determines the existence of an email
     *
     * @param string $email : email to check
     *
     * @return array $Response : response
     */
     public function emailAlreadyInUse($email) {
        $Response = null;
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
                SELECT DISTINCT
                    Patient.Email
                FROM
                    Patient
                WHERE
                    Patient.Email = '$email'
                LIMIT 1
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $Response = 'FALSE';
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                if ($data[0]) {
                    $Response = 'TRUE';
                }
            }

            return $Response;

         } catch (PDOException $e) {
            return $Response;
        }
     }

    /**
     *
     * Determines the existence of a patient
     *
     * @param string $ssn : patient SSN
     * @return array $patientResponse : patient information or response
     */
    public function findPatient($ssn, $id) {
        $patientResponse = array(
            'message'   => '',
            'status'    => '',
            'data'      => ''
        );
        $databaseObj = new Database();

        try{

            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            // First make a lookup in our database
            $sql = "
                SELECT DISTINCT
                    Patient.SSN
                FROM
                    Patient
                WHERE
                    Patient.SSN         LIKE '$ssn%'
                AND Patient.PatientId   = '$id'
                LIMIT 1
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $lookupSSN = null;
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $lookupSSN = $data[0];
            }

            if (!is_null($lookupSSN)) { // Found an ssn
                $patientResponse['status'] = 'PatientAlreadyRegistered';
                return $patientResponse;
            }

            // Then lookup in source database if patient DNE in our database

            // ***********************************
            // ARIA
            // ***********************************
            $sourceDBSer = 1;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "
                    SELECT DISTINCT TOP 1
                        pt.SSN,
                        pt.PatientSer,
                        pt.FirstName,
                        pt.LastName,
                        pt.PatientId,
                        pt.PatientId2,
                        pt.DateOfBirth,
                        ph.Picture,
                        RTRIM(pt.Sex)
                    FROM
                        variansystem.dbo.Patient pt
                    LEFT JOIN variansystem.dbo.Photo ph
                    ON ph.PatientSer = pt.PatientSer
                    WHERE
                        pt.SSN          LIKE '$ssn%'
                    AND pt.PatientId    = '$id'
                ";
                $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();
                
                $lookupSSN = null;
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    $lookupSSN = $data[0];

                    $patientArray = array(
                        'SSN'           => $data[0],
                        'sourceuid'     => $data[1],
                        'firstname'     => $data[2],
                        'lastname'      => $data[3],
                        'id'            => $data[4],
                        'id2'           => $data[5],
                        'dob'           => $data[6],
                        'picture'       => $data[7],
                        'sex'           => $data[8]
                    );

                    $patientResponse['data'] = $patientArray;
                }

                if (is_null($lookupSSN)) { // Could not find the ssn
                    $patientResponse['status'] = 'PatientNotFound';
                }

                return $patientResponse;
            }

            // ***********************************
            // WaitRoomManagement
            // ***********************************
            $sourceDBSer = 2;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "SELECT 'QUERY_HERE'";
                $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();

                $lookupSSN = null;
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    //$lookupSSN = $data[0];

                    // Set appropriate patient information here from query

                    //$patientResponse['data'] = $patientArray; // Uncomment for use
                }

                if (is_null($lookupSSN)) { // Could not find the ssn
                    $patientResponse['status'] = 'PatientNotFound';
                }

                return $patientResponse;
            }

            // ***********************************
            // Mosaiq
            // ***********************************
            $sourceDBSer = 3;
            $source_db_link = $databaseObj->connectToSourceDatabase($sourceDBSer);
            if ($source_db_link) {

                $sql = "SELECT 'QUERY_HERE'";
                $query = $source_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
                $query->execute();
                
                $lookupSSN = null;
                while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                    //$lookupSSN = $data[0];

                    // Set appropriate patient information here from query

                    //$patientResponse['data'] = $patientArray; // Uncomment for use
                }

                if (is_null($lookupSSN)) { // Could not find the ssn
                    $patientResponse['status'] = 'PatientNotFound';
                }

                return $patientResponse;
            }

            return $patientResponse; // return found data
        } catch (PDOException $e) {
            $patientResponse['status'] = 'Error';
            $patientResponse['message'] = $e->getMessage();
            return $patientResponse;
        }
    }

    /**
     *
     * Gets a list of security questions in the database
     *
     * @param string $language : site language
     * @return array $securityQuestions
     */
    public function getSecurityQuestions($language) {
        $securityQuestions = array();
        try {
            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    sq.SecurityQuestionSerNum,
                    sq.QuestionText_$language
                FROM
                    SecurityQuestion sq
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $questionArray = array(
                    'serial'    => $data[0],
                    'question'  => $data[1]
                );

                array_push($securityQuestions, $questionArray);
            }
             return $securityQuestions;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $securityQuestions;
        }
    }

     /**
     *
     * Registers a patient into the database
     *
     * @param array $patientDetails : the patient details
     * @return void
     */
     public function registerPatient($patientDetails) {

        $email              = $patientDetails['email'];
        $password           = $patientDetails['password'];
        $language           = $patientDetails['language'];
        $uid                = $patientDetails['uid'];
        $securityQuestion1  = $patientDetails['securityQuestion1'];
        $questionSerial1    = $securityQuestion1['serial'];
        $answer1            = $securityQuestion1['answer'];
        $securityQuestion2  = $patientDetails['securityQuestion2'];
        $questionSerial2    = $securityQuestion2['serial'];
        $answer2            = $securityQuestion2['answer'];
        $securityQuestion3  = $patientDetails['securityQuestion3'];
        $questionSerial3    = $securityQuestion3['serial'];
        $answer3            = $securityQuestion3['answer'];
        $cellNum            = $patientDetails['cellNum'];
        $SSN                = $patientDetails['SSN'];
        $accessLevel        = $patientDetails['accessLevel'];
        $sourceuid          = $patientDetails['data']['sourceuid'];
        $firstname          = $patientDetails['data']['firstname'];
        $lastname           = $patientDetails['data']['lastname'];
        $id                 = $patientDetails['data']['id'];
        $id2                = $patientDetails['data']['id2'];
        $picture            = $patientDetails['data']['picture'];
        $sex                = $patientDetails['data']['sex'];

        try {

            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                INSERT INTO
                    Patient (
                        PatientAriaSer,
                        PatientId,
                        PatientId2,
                        FirstName,
                        LastName,
                        ProfileImage,
                        Sex,
                        TelNum,
                        Email,
                        Language,
                        SSN,
                        AccessLevel,
                        SessionId,
						ConsentFormExpirationDate,
                        RegistrationDate
                    )
                VALUES (
                    '$sourceuid',
                    '$id',
                    '$id2',
                    \"$firstname\",
                    \"$lastname\",
                    '$picture',
                    '$sex',
                    '$cellNum',
                    '$email',
                    '$language',
                    '$SSN',
                    '$accessLevel',
                    'opalAdmin',
					DATE_ADD(NOW(), INTERVAL 1 YEAR),
                    NOW()
                )
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $patientSer = $host_db_link->lastInsertId();

            $sql = "
                INSERT INTO
                    Users (
                        UserType,
                        UserTypeSerNum,
                        Username,
                        Password,
                        SessionId
                    )
                VALUES (
                    'Patient',
                    '$patientSer',
                    '$uid',
                    '$password',
                    'opalAdmin'
                )
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                INSERT INTO
                    PatientControl (
                        PatientSerNum
                    )
                VALUES (
                    '$patientSer'
                )
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $sql = "
                INSERT INTO
                    SecurityAnswer (
                        SecurityQuestionSerNum,
                        PatientSerNum,
                        AnswerText,
                        CreationDate
                    )
                VALUES (
                    '$questionSerial1',
                    '$patientSer',
                    '$answer1',
                    NOW()
                ), (
                    '$questionSerial2',
                    '$patientSer',
                    '$answer2',
                    NOW()
                ), (
                    '$questionSerial3',
                    '$patientSer',
                    '$answer3',
                    NOW()

                )
            ";
            $query = $host_db_link->prepare( $sql );
            $query->execute();

            $questionnaires_db_link = new PDO( QUESTIONNAIRE_DB_DSN, QUESTIONNAIRE_DB_USERNAME, QUESTIONNAIRE_DB_PASSWORD );
            $questionnaires_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            $sql = "
                INSERT INTO 
                    Patient (
                        PatientName,
                        PatientId
                    )
                VALUES (
                    \"$firstname $lastname\",
                    '$id'
                )
            ";

            $query = $questionnaires_db_link->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );
            $query->execute();

        } catch( PDOException $e) {
            return $e->getMessage();
        }

     }

     /**
     *
     * Gets a list of patient activities
     *
     * @return array $patientActivityList : the list of patient activities
     */
     public function getPatientActivities() {
        $patientActivityList = array();
         try {

            $host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql = "
                SELECT DISTINCT
                    pt.PatientSerNum,
                    pt.PatientId,
                    pt.SSN,
                    pt.FirstName,
                    pt.LastName,
                    pal.SessionId,
                    pal.DateTime AS LoginTime,
                    pal.Request,
                    pal.DeviceId
                FROM
                    Patient pt,
                    PatientActivityLog pal,
                    Users
                WHERE
                    pt.PatientSerNum    = Users.UserTypeSerNum
                AND Users.Username      = pal.Username
                AND Users.UserType      = 'Patient'
                AND pal.Request         = 'Login'

                ORDER BY pal.DateTime DESC
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            $tmpPAList = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $deviceid = $data[8];
                if ($deviceid == 'browser') {
                    // do nothing
                }
                else if (strtoupper($deviceid) == $deviceid) {
                    $deviceid = "iOS/".$deviceid;
                }
                else {
                    $deviceid = "Android/".$deviceid;
                }
                $patientArray = array(
                    $data[5] => array(
                        'serial'                => $data[0],
                        'patientid'             => $data[1],
                        'ssn'                   => $data[2],
                        'name'                  => "$data[3] $data[4]",
                        'sessionid'             => $data[5],
                        'login'                 => $data[6],
                        'request'               => $data[7],
                        'deviceid'              => $deviceid

                    )
                );

                array_push($tmpPAList, $patientArray);
            }

            $sql = "
                SELECT DISTINCT
                    pal.SessionId,
                    pal.DateTime AS LogoutTime
                FROM
                    PatientActivityLog pal
                WHERE
                    pal.Request     = 'Logout'
            ";
            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();

            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                foreach ($tmpPAList as &$session) {
                    if($data[0] == key($session)){
                        $session[$data[0]]['logout'] = $data[1];
                        break;
                    }
                }
            }

            foreach ($tmpPAList as $session) {
                foreach ($session as $value) {
                    array_push($patientActivityList, $value);
                }
            }

            return $patientActivityList;
        } catch (PDOException $e) {
            echo $e->getMessage();
            return $patientActivityList;
        }
    }

	/**
	 *
	 * Gets details for one patient
	 *
	 * @param int $serial : the patient serial number
	 * @return array $patientDetails : the patient details
	 */
	 public function getPatientDetails ($serial) {

		 $patientDetails = array();
		 try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				SELECT DISTINCT 
					pt.FirstName,
					pt.LastName,
					pt.PatientId,
					usr.Username,
					pt.BlockedStatus,
					pt.email
				FROM
					Patient pt,
					Users usr
				WHERE
					pt.PatientSerNum = '$serial'
				AND pt.PatientSerNum = usr.UserTypeSerNum
				AND usr.UserType = 'Patient'
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);

			$patientDetails = array(
				'serial'            => $serial,
				'name'              => "$data[0] $data[1]",
				'patientid'         => $data[2],
				'uid' 				=> $data[3],
				'disabled'			=> intval($data[4]),
				'email'				=> $data[5]
			);

			return $patientDetails;

		} catch (PDOException $e) {
			echo $e->getMessage();
			return $patientDetails;
		}

	}

	/**
     *
     * Updates the patient
     *
     * @param array $patientDetails : the patient details
     * @return array $response : response
     */
     public function updatePatient($patientDetails) {
		$response = array (
			'value'		=> 0,
			'error'		=> array(
				'code'		=> '',
				'message'	=> ''
			)
		);

		$password 	= $patientDetails['password'];
		$serial 	= $patientDetails['serial'];
		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				UPDATE 
					Users
				SET
					Users.Password = \"$password\"
				WHERE
					Users.UserTypeSerNum = '$serial'
				AND Users.UserType = 'Patient'
			";

			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			$response['value'] = 1; // Success
			return $response;
			
		} catch (PDOException $e) {
			 $response['error']['code'] = 'db-catch';
			 $response['error']['message'] = $e->getMessage();
			 return $response;
		}
		 
	 }

	 /**
     *
     * Sets the block status
     *
     * @param array $patientDetails : the patient details
     * @return array $response : response
     */
     public function toggleBlock($patientDetails) {
		 $response = array (
			'value'		=> 0,
			'error'		=> array(
				'code'		=> '',
				'message'	=> ''
			)
		);

		$blockedStatus 	= $patientDetails['disabled'];
		$reason 		= $patientDetails['reason'];
		$serial 		= $patientDetails['serial'];
		$firebaseUID 	= $patientDetails['uid'];

		try {
			$host_db_link = new PDO( OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD );
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

			$sql = "
				UPDATE 
					Patient
				SET
					Patient.BlockedStatus 	= '$blockedStatus',
					Patient.StatusReasonTxt = \"$reason\"
				WHERE
					Patient.PatientSerNum = '$serial'
			";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			# call our nodejs script to block user on Firebase
            $command = "/usr/bin/node " . FRONTEND_ABS_PATH . 'js/firebaseSetBlock.js --blocked=' . $blockedStatus . ' --uid=' . $firebaseUID;
            # uncomment appropriate system call
			#$command = "/usr/local/bin/node " . FRONTEND_ABS_PATH . 'js/firebaseSetBlock.js --blocked=' . $blockedStatus . ' --uid=' . $firebaseUID;
			$commandResponse = system($command);

			if ($commandResponse == 0) {
				$response['value'] = 1; // Success
				$response['error']['message'] = $command;
			}
			else {
				$response['error']['message'] = "System command failed";
			}
			
			return $response;
			
		} catch (PDOException $e) {
			 $response['error']['code'] = 'db-catch';
			 $response['error']['message'] = $e->getMessage();
			 return $response;
		}
	 }
}

?>


