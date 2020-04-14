<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$ssn = strip_tags($_POST['ssn']);
$id = strip_tags($_POST['id']);
$patientObj = new Patient; // Object
$patientResponse = $patientObj->findPatient($ssn, $id);

//INSERT INTO AUDIT LOGS TABLE

echo json_encode($patientResponse);
