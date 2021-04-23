<?php
	header('Content-Type: application/javascript');
  /* Script to call sendCallPatientNotification given the following GET requests. */
  require_once('HospitalPushNotification.php');
  
  // determine patientId or MRN
  $patientId = HospitalPushNotification::getPatientIDorMRN(isset($_GET["patientid"]) ? $_GET["patientid"] : "---NA---", isset($_GET["mrn"]) ? $_GET["mrn"] : "---NA---");

  $room_EN        = $_GET['room_EN'];
  $room_FR        = $_GET['room_FR'];
  $apptSourceUID  = $_GET['appointment_ariaser'];

  // $wsSite is the site of the hospital code (should be three digit)
  // If $wsSite is empty, then default it to RVH because it could be from a legacy call
  $wsSite = isset($_GET["site"]) ? $_GET["site"] : "RVH";

  // Combine room info
  $room = array(
    'room_EN'   => $room_EN,
    'room_FR'   => $room_FR
  );

  // Call API
  // Repeating the PatientID because this function is designed to work with legacy and multisite
  $responses = HospitalPushNotification::sendCallPatientNotification($patientId, $room, $apptSourceUID, $patientId, $wsSite);

  // Return responses
  print json_encode($responses);
?>
