<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$site = strip_tags($_POST['site']);
$mrn = strip_tags($_POST['mrn']);
$patientObj = new Patient; // Object
$patientResponse = $patientObj->checkPatientExist($site, $mrn);
echo json_encode($patientResponse);