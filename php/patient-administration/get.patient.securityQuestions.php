<?php
include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

$patientObj = new PatientAdministration(); //Object. check firebase credential
//$response = $patientObj->getPatientSecurityQuestions($_POST);

// Get patient security questions
$username = $_POST['username'];
$language = strtolower($_POST['language']);

$backendApi = new NewOpalApiCall(
    '/api/caregivers/'.$username.'/security-questions/',
    'GET',
    $language,
    [],
    );

$response = $backendApi->execute(); // response is string json
print $response; // Return response
