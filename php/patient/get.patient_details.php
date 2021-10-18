<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$serial = strip_tags($_POST['serial']);
$patientObj = new Patient; // Object
$patientDetails = $patientObj->getPatientDetails($serial);

echo json_encode($patientDetails);