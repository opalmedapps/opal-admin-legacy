<?php
include_once("../config.php");

$patientObj = new Patient(); //Object
$response = $patientObj->getPatientSecurityQuestions($_POST);

header('Content-Type: application/javascript');
print json_encode($response); // Return response
