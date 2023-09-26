<?php

include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

//$patientObj = new PatientAdministration(); //Object
//$response = $patientObj->updatePatientSecurityAnswer($_POST);

// Update patient data_access in new opal DB
$username = $_POST['username'];
$language = strtolower($_POST['lan']);
$answer_id = $_POST['question_id'];

$backendApi = new NewOpalApiCall(
    '/api/caregivers/'.$username.'/security-questions/'.$answer_id.'/',
    'PUT',
    $language,
    ['data_access' => $data_access],
    );

$backendApi->execute();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
