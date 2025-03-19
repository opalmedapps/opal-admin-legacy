<?php

include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

$patientObj = new PatientAdministration(); //Object, check firebase credential
//$response = $patientObj->updatePatientSecurityAnswer($_POST);

print json_encode($_POST);

// Update patient data_access in new opal DB
$username = $_POST['username'];
$language = strtolower($_POST['lan']);
$question = $_POST['question'];
$answer = $_POST['answer'];
$answer_id = $_POST['answer_id'];

$backendApi = new NewOpalApiCall(
    '/api/caregivers/'.$username.'/security-questions/'.$answer_id.'/',
    'PUT',
    $language,
    ['question' => $question, 'answer' => $answer],
    );

$backendApi->execute();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
