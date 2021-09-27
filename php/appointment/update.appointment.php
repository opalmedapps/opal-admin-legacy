<?php
include_once("../config.php");

$appointment = new Appointment();

$result = $appointment->insertAppointment($_POST);
header('Content-Type: application/json');
http_response_code(HTTP_STATUS_SUCCESS);