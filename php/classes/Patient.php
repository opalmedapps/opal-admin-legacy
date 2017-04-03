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
     * @return array
     */
    public function getExistingPatients() {
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
                    pc.LastTransferred
                FROM
                    PatientControl pc,
                    Patient pt
                WHERE
                    pt.PatientSerNum = pc.PatientSerNum
            ";
			$query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$query->execute();

			while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {

                $patientArray = array(
                    'serial'            => $data[0],
                    'transfer'          => $data[1],
                    'name'              => "$data[2] $data[3]",
                    'patientid'         => $data[4],
                    'lasttransferred'   => $data[5]
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
     *
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
    public function fetchSecurityQuestions($language) {
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
     * @param array $patientArray : the patient details
     * @return void
     */
     public function registerPatient($patientArray) {

        $email              = $patientArray['email'];
        $password           = $patientArray['password'];
        $language           = $patientArray['language'];
        $uid                = $patientArray['uid'];
        $securityQuestion1  = $patientArray['securityQuestion1'];
        $questionSerial1    = $securityQuestion1['serial'];
        $answer1            = $securityQuestion1['answer'];
        $securityQuestion2  = $patientArray['securityQuestion2'];
        $questionSerial2    = $securityQuestion2['serial'];
        $answer2            = $securityQuestion2['answer'];
        $securityQuestion3  = $patientArray['securityQuestion3'];
        $questionSerial3    = $securityQuestion3['serial'];
        $answer3            = $securityQuestion3['answer'];
        $cellNum            = $patientArray['cellNum'];
        $SSN                = $patientArray['SSN'];
        $sourceuid          = $patientArray['data']['sourceuid'];
        $firstname          = $patientArray['data']['firstname'];
        $lastname           = $patientArray['data']['lastname'];
        $id                 = $patientArray['data']['id'];
        $id2                = $patientArray['data']['id2'];
        $picture            = $patientArray['data']['picture'];
        $sex                = $patientArray['data']['sex'];

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
                        SessionId
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
                    'AdminPanel'
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
                    'AdminPanel'
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


        } catch( PDOException $e) {
            return $e->getMessage();
        }

     }

     /**
     *
     * Gets a list of patient activities
     *
     * @return array $patientActivityList
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

}

?>


