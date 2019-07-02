<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$language = strip_tags($_POST['lang']);
$patientObj = new Patient; // Object
$securityQuestions = $patientObj->getSecurityQuestions($language);

echo json_encode($securityQuestions);