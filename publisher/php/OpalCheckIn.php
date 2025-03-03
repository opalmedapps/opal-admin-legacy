<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

//=============================================================================================================
// OpalCheckIn.php -- PHP class that contains functions needed to properly check in a patient in Opal DB
//=============================================================================================================

//
// INCLUDES
//=============================================

// Get the database configurations
require_once "database.inc";

// Used to send push notification to all of the user devices
require_once('PatientCheckInPushNotification.php');

// Used to determine which to use (PatientId or MRN)
require_once('HospitalPushNotification.php');

//
// PROCESS INCOMING REQUEST
//============================================
// determine patientId or MRN
$wsPatientID = HospitalPushNotification::sanitizeInput(isset($_GET["PatientId"]) ? $_GET["PatientId"] : "---NA---");
$wsMRN = HospitalPushNotification::sanitizeInput(isset($_GET["mrn"]) ? $_GET["mrn"] : "---NA---");
$PatientId = HospitalPushNotification::getPatientIDorMRN($wsPatientID, $wsMRN);

// $wsSite is the site of the hospital code (should be three digit)
// If $wsSite is empty, then default it to RVH because it could be from a legacy call
$wsSite = HospitalPushNotification::sanitizeInput(isset($_GET["site"]) ? $_GET["site"] : "RVH");

// Process the checkin
$response = OpalCheckin::ValidateCheckin($PatientId, $wsSite);

if($response['failure']) print("Error: " . $response['error']);
else if (count($response['data']) > 0) {
	$result = OpalCheckin::UpdateCheckinOnOpal($response['data'], $PatientId, $wsSite);
	print(implode($result['data']));
}
else print('Error: No appointments were successfully checked into or no appointments exist today');

//
// OPALCHECKIN CLASS
//=============================================
class OpalCheckin{

    /**
     * Updates OpalDB with the checkin states of the inputted appointments and then sends notifications to the patient
     * @param $success
     * @param $patientId Patient MRN
     * @param $site Hospital Code
     * @return array
     */
	public static function UpdateCheckinOnOpal($success, $patientId, $site){

        //
		// DATABASE CONFIGURATION
		//===============================================

        // Create DB connection
        $conn = new mysqli(OPAL_DB_HOST, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, OPAL_DB_NAME);

        //
        // DETERMINE IF OPAL PATIENT EXISTS BEFORE PROCEEDING
        //======================================================

        // Get the Opal Patient ID using the Aria Serial Number
        $sql = "select PHI.PatientSerNum 
                From Patient_Hospital_Identifier PHI
                where PHI.MRN = '$patientId'
                    and PHI.Hospital_Identifier_Type_Code = '$site'
                ";

        try {
            $patientSerNum = $conn->query($sql);

            // if Opal Patient exists
            if ($patientSerNum->num_rows > 0) {
                foreach ($success as $app) {
                    // Checkin the patient in our Database
                    $sql = "UPDATE Appointment
                            SET Appointment.Checkin = 1
                            WHERE Appointment.AppointmentSerNum = " . $app;
                    $conn->query($sql);
                }

                //
                // SEND NOTIFICATION TO PATIENT ABOUT CHECKIN STATUS
                //========================================================
                $patientSerNum = $patientSerNum->fetch_row();

                $patientSerNum = $patientSerNum[0];

                PatientCheckInPushNotification::sendPatientCheckInNotification($patientSerNum, $success);

                // Return responses
                return self::SuccessResponse($success);
            } else return self::ErrorResponse('Inputted patient is not an Opal Patient');
        } catch(mysqli_sql_exception $e) {
            return self::ErrorResponse($e);
        }
    }

    /**
     * Validates whether or not a patient's appointments were successfully checked in on Aria and/or Medivist and then
     * returns an array of appointments that were successfully checked in
     * @param $patientId Patient MRN
     * @param $Site Hospital Code
     * @return array
     */
    public static function ValidateCheckin($patientId, $Site){
	    // Array that will hold appointmentsernum of appointments that were successfully checked in
	    $success = array();

	    // Get all of the patients appointments that are today
        $apts = self::getTodaysAppointments($patientId, $Site);
        if($apts['failure']) return self::ErrorResponse($apts['error']);
		else $apts = $apts['data'];
        //If aria appointments exist...
        if(count($apts[0]) > 0){

            //Get appointmentsernums of successfully checked in aria appointments
            $validAriaAppointments = self::validateCheckinsWithExternalDB($apts[0], $patientId, $Site, 'Aria');
            if($validAriaAppointments['failure']) return self::ErrorResponse($validAriaAppointments['error']);
            else $validAriaAppointments = $validAriaAppointments['data'];

            // Push appointmentSerNums to success array
            $success = array_merge($success, $validAriaAppointments);

        }

        //If medivisit appointments exist...
        if(count($apts[1]) > 0){

            //Get appointmentsernums of successfully checked in medivist appointments
            $validMediAppointments = self::validateCheckinsWithExternalDB($apts[1], $patientId, $Site, 'Medi');
            if($validMediAppointments['failure']) return self::ErrorResponse($validMediAppointments['error']);
            else $validMediAppointments = $validMediAppointments['data'];

            // Push appointmentSerNums to success array
            $success = array_merge($success, $validMediAppointments);
        }


        return self::SuccessResponse($success);
    }

    //
    // PRIVATE METHODS
    //===========================================

    /**
     * Gets a list of all appointments of patient on a given day from Aria and Medivisit
     * @param $patientId Patient MRN
     * @param $Site Hospital Code
     * @return array of appointments
     */
    private static function getTodaysAppointments($patientId, $Site){

        // Create DB connection
        $conn = new mysqli(OPAL_DB_HOST, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, OPAL_DB_NAME);

        // Get current patients appointments from OpalDB that exist in aria
        $sqlAria = "
            Select A.AppointmentSerNum, A.SourceSystemID
            From Patient_Hospital_Identifier PHI, Appointment A
            Where PHI.MRN = '$patientId'
                And PHI.Hospital_Identifier_Type_Code = '$Site'
                And PHI.PatientSerNum = A.PatientSerNum
                And A.SourceDatabaseSerNum = 1
                And DATE_FORMAT(A.ScheduledStartTime, '%Y-%m-%d') = DATE_FORMAT(NOW() - INTERVAL 0 DAY, '%Y-%m-%d');
        ";

        // Get current patients appointments from OpalDB that exist in medivisit
        $sqlMediVisit = "
            Select A.AppointmentSerNum, A.SourceSystemID
            From Patient_Hospital_Identifier PHI, Appointment A
            Where PHI.MRN = '$patientId'
                And PHI.Hospital_Identifier_Type_Code = '$Site'
                And PHI.PatientSerNum = A.PatientSerNum
                And A.SourceDatabaseSerNum = 2
                And DATE_FORMAT(A.ScheduledStartTime, '%Y-%m-%d') = DATE_FORMAT(NOW() - INTERVAL 0 DAY, '%Y-%m-%d');
        ";
         try{
             $apts = array();
             $aria = array();
             $medi = array();

             $resultAria = $conn->query($sqlAria);

             while ($row = $resultAria->fetch_assoc()) {
                 $aria[] = $row;
             }

             $resultMedi = $conn->query($sqlMediVisit);

             while ($row = $resultMedi->fetch_assoc()) {
                 $medi[] = $row;
             }

             $apts[] = $aria;
             $apts[] = $medi;

             return self::SuccessResponse($apts);
         }catch(mysqli_sql_exception $e) {
             return self::ErrorResponse($e);
         }
    }

    /**
     * Checks whether opalDB appointments exist in either aria or medivisit and returns array of verified appointments
     * @param $appts
     * @param $patientId Patient MRN
     * @param $Site Hospital Code
     * @param $location used internally if it is an Aira system or Medi (ORMS) system
     * @return array
     */
    private static function validateCheckinsWithExternalDB($appts, $patientId, $Site, $location){
        $success = array();

        //Get Aria ser num of each checked in appointment in Aria
        try{
            $ext_appts = ($location == 'Aria') ?  self::getCheckedInAriaAppointments($patientId, $Site) : self::getCheckedInMediAppointments($patientId, $Site);
            $ext_appts = $ext_appts['data'];

        } catch (Exception $e) {
            return self::ErrorResponse($e);
        }

        // Cross verify opalDB appointments with external DB appointments
        foreach ($appts as $apt){
            if(in_array($apt['SourceSystemID'], $ext_appts)){
                $success[] = $apt['AppointmentSerNum'];
            }
        }


        return self::SuccessResponse($success);
    }

    /**
     * Gets a list of checked in appointments in Aria
     * @param $patientId Patient MRN
     * @param $Site Hospital Code
     * @return array
     * @throws Exception
     */
    private static function getCheckedInAriaAppointments($patientId, $Site){
        $host_db_link = new PDO(ARIA_DB_DSN, ARIA_DB_USERNAME, ARIA_DB_PASSWORD);

        // The first subquery gets the list of todays Schedule Activity of a patient
        // The top query gets the list of Schedule Activity Serial Number that exist in the patient location table (indicate that the patient have successfully checked in)

        // ONLY get the list of schedule Activity Serial Number if the site is RVH
        if ($Site == "RVH") {
            $sql = "SELECT ScheduledActivitySer AS AppointmentSerNum
            FROM VARIAN.dbo.PatientLocation
            WHERE ScheduledActivitySer IN
              (SELECT ScheduledActivity.ScheduledActivitySer
              FROM VARIAN.dbo.Patient, VARIAN.dbo.ScheduledActivity
              WHERE Patient.PatientSer = ScheduledActivity.PatientSer
                AND Patient.PatientId = '$patientId'
                AND LEFT(CONVERT(VARCHAR, ScheduledActivity.ScheduledStartTime, 120), 10) = LEFT(CONVERT(VARCHAR, getdate() - 0, 120), 10)
              )
            AND CheckedInFlag = 1";
        } else { // if the site is not RVH, then return an empty query results
                 // in theory, PatientLocationSer is an auto increment so it should never be a negative number
            $sql = "SELECT ScheduledActivitySer AS AppointmentSerNum 
                    FROM VARIAN.dbo.PatientLocation
                    WHERE PatientLocationSer = -9999999"; 
        }

        $host_db_link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = $host_db_link->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $query->execute();
        $resultAria = $query->fetchAll(PDO::FETCH_ASSOC);


        $apts = array();

        foreach($resultAria as $row){
            $apts[] = $row['AppointmentSerNum'];
        }

        return self::SuccessResponse($apts);
    }

    /**
     * Gets a list of all checked in appointments on MediVisit
     * @param $patientId Patient MRN
     * @param $Site Hospital Code
     * @return array
     */
    private static function getCheckedInMediAppointments($patientId, $Site){

        // Create DB connection to WaitingRoomManagement

				// YM 2018-09-07 - Use Opal DB instead of WaitingRoomManagement because
				//		now the WaitingRoomManagement might be a FEDERATED engine or not
        // $conn = new mysqli(WRM_DB_HOST, WRM_DB_USERNAME, WRM_DB_PASSWORD, WRM_DB_NAME);
				$conn = new mysqli(OPAL_DB_HOST, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, OPAL_DB_NAME);

        // Gets the list of Schedule Aria Appointments that have successfully checked in
				$opalDatabaseName = OPAL_DB_NAME;
				$wrmDatabaseName = WRM_DB_NAME_FED;

                // **********************************************************************
                // TODO: ORMS doesn't have site yet, so query needs to wait for changes
                //      For now only process RVH MRNs for now
                // **********************************************************************
                if ($Site == "RVH") {
                    $sql = "Select PMH.AppointmentSerNum
                    From $wrmDatabaseName.PatientLocation PMH, $wrmDatabaseName.Patient P, $wrmDatabaseName.MediVisitAppointmentList MVA
                    Where P.PatientSerNum = MVA.PatientSerNum
                        And P.PatientId = '$patientId'
                        And MVA.AppointmentSerNum = PMH.AppointmentSerNum
                        and MVA.AppointSys <> 'Aria'
                        And DATE_FORMAT(ArrivalDateTime, '%Y-%m-%d') = DATE_FORMAT(NOW() - INTERVAL 0 DAY, '%Y-%m-%d')
                    ;";
                } else {
                    $sql = "select AppointmentSerNum
                    from $wrmDatabaseName.MediVisitAppointmentList
                    where AppointmentSerNum < 0
                ;";
                }
        try{
            $resultMedi = $conn->query($sql);

            $medi = array();

            while ($row = $resultMedi->fetch_assoc()) {
                $medi[] = $row['AppointmentSerNum'];
            }
        } catch (mysqli_sql_exception $e) {
            return self::ErrorResponse($e);
        }


        return self::SuccessResponse($medi);
    }

    public static function SuccessResponse($data){
        return array("success"=>true, "failure"=>false, "data"=>$data);
    }

    public static function ErrorResponse($err){
        return array("success"=>false, "failure"=>true, "error"=>$err);
    }

}
?>
