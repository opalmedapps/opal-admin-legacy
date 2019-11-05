<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$email = $_POST['email'];
$patientObj = new Patient; // Object
$Response = $patientObj->emailAlreadyInUse($email);

echo json_encode($Response);