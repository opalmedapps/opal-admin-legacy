<?php

//=============================================================================================================
// OpalCheckIn.php -- PHP class that contains functions needed to properly check in a patient in Opal DB
//=============================================================================================================

//
// INCLUDES
//=============================================

// Get the database configurations
include_once "database.inc";	

// Used to send push notification to all of the user devices
require_once('PatientCheckInPushNotification.php');


//
// PROCESS INCOMING REQUEST
//============================================
$PatientId = $_GET["PatientId"];

$success = OpalCheckin::ValidateCheckin($PatientId);
return OpalCheckin::UpdateCheckinOnOpal($success, $PatientId);


//
// OPALCHECKIN CLASS
//=============================================
class OpalCheckin{

	public static function UpdateCheckinOnOpal($success, $patientId){

        //
		// DATABASE CONFIGURATION
		//===============================================

        // Create DB connection 
        $conn = new mysqli(OPAL_DB_HOST, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, OPAL_DB_NAME);

        //
        // DETERMINE IF OPAL PATIENT EXISTS BEFORE PROCEEDING
        //======================================================

        // Get the Opal Patient ID using the Aria Serial Number
        $sql = "Select Patient.PatientSerNum
				From Patient
				Where PatientId = " . $patientId;
        $patientSerNum = $conn->query($sql);

        // if Opal Patient exists
        if ($patientSerNum->num_rows > 0) {
            foreach ($success as $app){
                // Checkin the patient in our Database
                $sql = "Update Appointment 
                        Set Appointment.Checkin = 1
                        Where Appointment.AppointmentSerNum = " . $app;
                $conn->query($sql);
            }

            //
            // SEND NOTIFICATION TO PATIENT ABOUT CHECKIN STATUS
            //========================================================
            $patientSerNum = $patientSerNum->fetch_row();
            $patientSerNum = $patientSerNum[0];

            $responses = PatientCheckInPushNotification::sendPatientCheckInNotification($patientSerNum, $success);

            // Return responses
            return json_encode($responses);
        } else {
            return json_encode('Patient is not an Opal Patient');
        }
    }

    public static function ValidateCheckin($patientId){
	    // Array that will hold appointmentsernum of appointments that were successfully checked in
	    $success = array();

	    // Get all of the patients appointments that are today
        $apts = self::getTodaysAppointments($patientId);

        //If aria appointments exist...
        if(count($apts[0]) > 0){

            //Get appointmentsernums of successfully checked in aria appointments
            $validAriaAppointments = self::validateCheckinsWithExternalDB($apts[0], $patientId, 'Aria');

            // Push appointmentSerNums to success array
            $success = array_merge($success, $validAriaAppointments);
        }

        //If medivisit appointments exist...
        if(count($apts[1]) > 0){

            //Get appointmentsernums of successfully checked in medivist appointments
            $validMediAppointments = self::validateCheckinsWithExternalDB($apts[1], $patientId, 'Medi');

            // Push appointmentSerNums to success array
            $success = array_merge($success, $validMediAppointments);
        }

        return $success;
    }

    //
    // PRIVATE METHODS
    //===========================================

    /**
     * @name getTodaysAppointments
     * @param $patientId
     * @return array of appointments
     */
    private static function getTodaysAppointments($patientId){

        // Create DB connection  **** CURRENTLY OPAL_DB POINTS TO PRE_PROD ****
        $conn = new mysqli(OPAL_DB_HOST, OPAL_DB_USERNAME, OPAL_DB_PASSWORD, OPAL_DB_NAME);

        // Get current patients appointments from OpalDB that exist in aria
        $sqlAria = "
                Select Appointment.AppointmentSerNum, Appointment.AppointmentAriaSer
                From Patient, Appointment
                Where Patient.patientId = " . $patientId . "
                    And Patient.PatientSerNum = Appointment.PatientSerNum
                    And Appointment.SourceDatabaseSerNum = 1
                    And DATE_FORMAT(Appointment.ScheduledStartTime, '%Y-%m-%d') = DATE_FORMAT(NOW() - INTERVAL 0 DAY, '%Y-%m-%d');";

        // Get current patients appointments from OpalDB that exist in medivisit
        $sqlMediVisit = "
                Select Appointment.AppointmentSerNum, Appointment.AppointmentAriaSer
                From Patient, Appointment
                Where Patient.patientId = " . $patientId . "
                    And Patient.PatientSerNum = Appointment.PatientSerNum
                    And Appointment.SourceDatabaseSerNum = 2
                    And DATE_FORMAT(Appointment.ScheduledStartTime, '%Y-%m-%d') = DATE_FORMAT(NOW() - INTERVAL 0 DAY, '%Y-%m-%d');";
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


             return $apts;
         }catch(mysqli_sql_exception $e) {
             return array("success"=>0,"failure"=>1,"error"=>$e);
         }
    }

    /**
     * @name ValidateCheckinsWithExternalDB
     * @desc Checks whether opalDB appointments exist in either aria or medivist and returns array of verified appointments
     * @param $appts
     * @param $patientId
     * @param $location
     * @return array
     */
    private static function validateCheckinsWithExternalDB($appts, $patientId, $location){
        $success = array();

        //Get Aria ser num of each checked in appointment in Aria
        $ext_appts = ($location == 'Aria') ?  self::getCheckedInAriaAppointments($patientId) : self::getCheckedInMediAppointments($patientId);

        // Cross verify opalDB appointments with external DB appointments
        foreach ($appts as $apt){
            if(in_array($apt['AppointmentAriaSer'], $ext_appts)){
                $success[] = $apt['AppointmentSerNum'];
            }
        }
        return $success;
    }

    private static function getCheckedInAriaAppointments($patientId){

        // Create DB connection  **** CURRENTLY OPAL_DB POINTS TO ARIA ****
        $conn = mssql_connect(ARIA_DB_HOST, ARIA_DB_USERNAME, ARIA_DB_PASSWORD);

        // Check connections
        if (!$conn) {
            die('Something went wrong while connecting to MSSQL');
        }

        // The first subquery gets the list of todays Schedule Activity of a patient
        // The top query gets the list of Schedule Activity Serial Number that exist in the patient location table (indicate that the patient have successfully checked in)
        $sql = "Select ScheduledActivitySer as AppointmentSerNum
                From variansystem.dbo.PatientLocation
                Where ScheduledActivitySer IN
                  (Select ScheduledActivity.ScheduledActivitySer
                  From variansystem.dbo.Patient, variansystem.dbo.ScheduledActivity
                  Where Patient.PatientSer = ScheduledActivity.PatientSer
                    AND Patient.PatientId = '" . $patientId . "'
                    AND left(convert(varchar, ScheduledActivity.ScheduledStartTime, 120), 10) = left(convert(varchar, getdate() - 0, 120), 10) 
                  )
                AND CheckedInFlag = 1";

        $resultAria = mssql_query($sql);

        $apts = array();

        while($row = mssql_fetch_array($resultAria )){
            $apts[] = $row['AppointmentSerNum'];
        }

        return $apts;
    }

    private static function getCheckedInMediAppointments($patientId){

        // Create DB connection to WaitingRoomManagement
        $conn = new mysqli(WRM_DB_HOST, WRM_DB_USERNAME, WRM_DB_PASSWORD, WRM_DB_NAME);

        // Gets the list of Schedule Aria Appointments that have successfully checked in
        $sql = "Select PMH.AppointmentSerNum
                From PatientLocationMH PMH, Patient P, MediVisitAppointmentList MVA
                Where P.PatientSerNum = MVA.PatientSerNum
                    And P.PatientId = " . $patientId . "
                    And MVA.AppointmentSerNum = PMH.AppointmentSerNum
                    And CheckinVenueName like '%Waiting Room%'
                    And DATE_FORMAT(ArrivalDateTime, '%Y-%m-%d') = DATE_FORMAT(NOW() - INTERVAL 0 DAY, '%Y-%m-%d');";

        $resultMedi = $conn->query($sql);

        $medi = array();

        while ($row = $resultMedi->fetch_assoc()) {
            $medi[] = $row['AppointmentSerNum'];
        }

        return $medi;
    }

}
?>


