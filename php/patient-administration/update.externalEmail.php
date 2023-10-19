<?php

include_once("../config.php");
include_once("../classes/NewOpalApiCall.php");

$patientObj = new PatientAdministration(); //Object
$patientObj->updateExternalEmail($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

// Update user email in new opal DB
$username = $_POST['uid'];
$email = $_POST['email'];
$language = strtolower($_POST['language']);

$backendApi = new NewOpalApiCall(
    '/api/users/caregivers/'.$username.'/',
    'PUT',
    $language,
    ['email' => $email],
);

$backendApi->execute();
