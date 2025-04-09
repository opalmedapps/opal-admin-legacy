<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
  /* Script to send push notifications given the following POST requests. */

  include_once('HospitalPushNotification.php');

  // print "Title: " . $_POST['message_title'] . "\n\n";

  $messageTitle       = HospitalPushNotification::sanitizeInput($_POST['message_title']);
  $messageText        = HospitalPushNotification::sanitizeInput($_POST['message_text']);
  $deviceType         = HospitalPushNotification::sanitizeInput($_POST['device_type']);
  $registrationID     = HospitalPushNotification::sanitizeInput($_POST['registration_id']);

  // Call API to send push notification
  $response = HospitalPushNotification::sendNotification($deviceType, $registrationID, $messageTitle, $messageText);

  // Return response
  print json_encode($response);

?>
