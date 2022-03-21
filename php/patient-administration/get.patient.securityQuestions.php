<?php
include_once("../config.php");

$patientObj = new PatientAdministration(); //Object
$response = $patientObj->getPatientSecurityQuestions($_POST);

header('Content-Type: application/javascript');
print json_encode($response); // Return response
