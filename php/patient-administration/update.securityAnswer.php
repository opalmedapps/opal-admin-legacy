<?php

include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

$patientObj = new PatientAdministration(); //Object
//$response = $patientObj->updatePatientSecurityAnswer($_POST);

// Update patient security questions and answers in new opal DB
$username = $_POST['username'];
$language = strtolower($_POST['language']);
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
