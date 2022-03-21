<?php

include_once("../config.php");

$patientObj = new PatientAdministration(); //Object
$patientObj->updatePatientAccessLevel($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);