<?php

include_once("../config.php");
$appointment = new Appointment(); // Object
$response = $appointment->getAppointment($_POST);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>