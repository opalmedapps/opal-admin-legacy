<?php
	header('Content-Type: application/javascript');
  /* Script to send push notifications given the following POST requests. */

  $patientId    = $_GET['patientId']; 
  $title_EN     = $_GET['title_EN'];
  $title_FR     = $_GET['title_FR'];
  $message_EN   = $_GET['message_EN'];
  $message_FR   = $_GET['message_FR'];

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
  $response = customPushNotification::sendPatientNotification($patientId, $messages);
  
  // Return response
  // print json_encode($response);

?>
