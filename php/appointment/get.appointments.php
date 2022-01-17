<?php

include_once("../config.php");
$data = json_decode(file_get_contents('php://input'), true);
$appointment = new Appointment(); // Object
$response = $appointment->getAppointment($data);
header('Content-Type: application/javascript');
print json_encode($response); // Return response

?>