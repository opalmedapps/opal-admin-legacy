<?php
include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

//$patientObj = new PatientAdministration(); //Object
//$response = $patientObj->getPatientSecurityQuestions($_POST);
//print json_encode($_POST); // Return response

// Get patient security questions
$username = $_POST['username'];
$language = strtolower($_POST['lan']);

$backendApi = new NewOpalApiCall(
    '/api/caregivers/'.$username.'/security-questions/',
    'GET',
    $language,
    [],
    );

$response = $backendApi->execute(); // response is string json

header('Content-Type: application/javascript');
print $response; // Return response
