<?php

include_once("../config.php");

$appointment = new Appointment();

$appointment->deleteAppointment($_POST);
header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);