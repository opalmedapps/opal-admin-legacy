<?php
	header('Content-Type: application/javascript');
  /* Script to send push notifications given the following POST requests. */

  // Used to determine which to use (PatientId or MRN)
  require_once('HospitalPushNotification.php');

  // determine patientId or MRN
  $wsPatientID    = HospitalPushNotification::sanitizeInput(isset($_GET["patientid"]) ? $_GET["patientid"] : "---NA---");
  $wsMRN          = HospitalPushNotification::sanitizeInput(isset($_GET["mrn"]) ? $_GET["mrn"] : "---NA---");
  $patientId      = HospitalPushNotification::getPatientIDorMRN($wsPatientID, $wsMRN);

  // $wsSite is the site of the hospital code (should be three digit)
  // If $wsSite is empty, then default it to RVH because it could be from a legacy call
  $wsSite         = HospitalPushNotification::sanitizeInput(isset($_GET["site"]) ? $_GET["site"] : "RVH");

  // Title and message
  $title_EN     = HospitalPushNotification::sanitizeInput(isset($_GET['title_EN']) ? $_GET['title_EN'] : "");
  $title_FR     = HospitalPushNotification::sanitizeInput(isset($_GET['title_FR']) ? $_GET['title_FR'] : "");
  $message_EN   = HospitalPushNotification::sanitizeInput(isset($_GET['message_EN']) ? $_GET['message_EN'] : "");
  $message_FR   = HospitalPushNotification::sanitizeInput(isset($_GET['message_FR']) ? $_GET['message_FR'] : "");

  // Combine message English and French
  $messages = array(
    'title_EN'    => $title_EN,
    'title_FR'    => $title_FR,
    'message_EN'  => $message_EN,
    'message_FR'  => $message_FR
  );

  include_once('customPushNotification.php');

  #print a message and close the connection so that the client does not wait
  ob_start();
  echo "DONE";
  header('Connection: close');
  header('Content-Length: '.ob_get_length());
  ob_end_flush();
  ob_flush();
  flush();

  // Call API to send push notification
  $response = customPushNotification::sendPatientNotification($patientId, $wsSite, $messages);
  
  // Return response
  // print json_encode($response);

?>
