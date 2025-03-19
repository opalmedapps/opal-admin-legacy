<?php
include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

$patientObj = new PatientAdministration(); //Object, check firebase credential
//$response = $patientObj->getAllSecurityQuestions();

// Get all security questions
$language = strtolower($_POST['language']);

$backendApi = new NewOpalApiCall(
    '/api/security-questions/',
    'GET',
    $language,
    [],
    );

$response = $backendApi->execute(); // response is string json

header('Content-Type: application/javascript');
print $response; // Return response