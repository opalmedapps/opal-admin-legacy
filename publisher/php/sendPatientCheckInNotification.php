<?php
	header('Content-Type: application/javascript');
	/* Script to send a notification to a patient that checked in.  */

	// Check if the patient ID is not empty
	// NOTE: $patientId is actually PatientSerNum. Do not know why it is using patientId
	if (strlen(trim($patientId)) > 0){

		include_once('PatientCheckInPushNotification.php');

		// Call API
		$responses = PatientCheckInPushNotification::sendPatientCheckInNotification($patientId);

		// Return responses
		print json_encode($responses);
	}

?>
