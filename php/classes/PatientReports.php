<?php

/**
 * Patient Reports class
 * @author K. Agnew Dec 2020 - Patient reports refactor - death to Perl
 * 
 */

class PatientReports extends Module {

    public function __construct($guestStatus = false) {
        parent::__construct(MODULE_PATIENT, $guestStatus);
    }

    /**
     * Search database for patient
     * 
     * @param $name: patient last name case insensitive
     * @return $response: details for the given patient(s) matching search criteria
     * 
     */
    public function findPatientByName( $name ) {
        // $this->checkReadAccess();
        try{
            $host_db_link = new PDO(OPAL_DB_DSN, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            $host_db_link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $sql="
            SELECT
                PatientSerNum,
                PatientId,
                FirstName,
                LastName,
                SSN,
                Sex,
                Email,
                Language
            FROM
                Patient 
            WHERE
                Patient.LastName = '$name'
             ";

            $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $query->execute();
            $patientList = array();
            while ($data = $query->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
                $patientArray = array(
                    'psnum'      => $data[0],
                    'pid'        => $data[1],
                    'fname'      => $data[2],
                    'lname'      => $data[3],
                    'ssn'        => $data[4],
                    'sex' 	     => $data[5],
                    'email'      => $data[6],
                    'language'   => $data[7]
                );
                array_push($patientList, $patientArray);
            }
            return $patientList;
        }
        catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }
    }

    

    /**
     *
     * Gets a list of existing patients in the database
     *
     * @return array $patientList : the list of existing patients
     */
    public function getPatients() {
        $this->checkReadAccess();
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
                    'serial'          => $data[0],
                    'transfer'        => $data[1],
                    'name'            => "$data[2] $data[3]",
                    'patientid'       => $data[4],
                    'lasttransferred' => $data[5],
                    'disabled' 			  => intval($data[6]),
                    'uid'             => $data[7],
                    'email'           => $data[8]
                );
                array_push($patientList, $patientArray);
            }

            return $patientList;
        } catch (PDOException $e) {
            HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Database connection error for patient. " . $e->getMessage());
        }
    }
}