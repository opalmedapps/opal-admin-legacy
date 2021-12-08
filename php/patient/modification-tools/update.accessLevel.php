<?php

header('Content-Type: application/javascript');
include_once("../../config.php");

$patientObj = new Patient();
$response = $patientObj->updatePatientAccessLevel($_POST);

print json_encode($response);