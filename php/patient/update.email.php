<?php

header('Content-Type: application/javascript');
include_once("../config.php");

$patientObj = new Patient(); //Object

$patientObj->updatePatientEmail($_POST);
http_response_code(HTTP_STATUS_SUCCESS);