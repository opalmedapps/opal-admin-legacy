<?php
include_once("../config.php");

$doctor = new TriggerDoctor();

$result = $doctor->updatePatientDoctor($_POST);
header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);