<?php

include_once("../config.php");

$appointment = new Appointment();

$appointment->updateAppointmentStatus($_POST);
header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);