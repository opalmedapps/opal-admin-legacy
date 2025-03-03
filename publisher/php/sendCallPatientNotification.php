<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
  /* Script to call sendCallPatientNotification given the following GET requests. */
  require_once('HospitalPushNotification.php');

  // determine patientId or MRN
  $wsPatientID    = HospitalPushNotification::sanitizeInput(isset($_GET["patientid"]) ? $_GET["patientid"] : "---NA---");
  $wsMRN          = HospitalPushNotification::sanitizeInput(isset($_GET["mrn"]) ? $_GET["mrn"] : "---NA---");
  $patientId      = HospitalPushNotification::getPatientIDorMRN($wsPatientID, $wsMRN);

  // Meesage and appointment ID
  $room_EN        = HospitalPushNotification::sanitizeInput(isset($_GET['room_EN']) ? $_GET['room_EN'] : "");
  $room_FR        = HospitalPushNotification::sanitizeInput(isset($_GET['room_FR']) ? $_GET['room_FR'] : "");
  $apptSourceUID  = HospitalPushNotification::sanitizeInput(isset($_GET['appointment_ariaser']) ? $_GET['appointment_ariaser'] : "");

  // $wsSite is the site of the hospital code (should be three digit)
  // If $wsSite is empty, then default it to RVH because it could be from a legacy call
  $wsSite         = HospitalPushNotification::sanitizeInput(isset($_GET["site"]) ? $_GET["site"] : "RVH");

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
