<?php
include_once("../../config.php");

$patientObj = new Patient(); //Object
$response = $patientObj->getAllSecurityQuestions();

header('Content-Type: application/javascript');
print json_encode($response); // Return response