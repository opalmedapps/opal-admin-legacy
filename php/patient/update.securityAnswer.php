<?php

include_once("../config.php");

$patientObj = new Patient(); //Object
$response = $patientObj->updatePatientSecurityAnswer($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
