<?php

header('Content-Type: application/javascript');
include_once('patient.inc');
$OAUserId = strip_tags($_POST['userId']);

$patient = new Patient($OAUserId); // Object
$existingPatientList = $patient->getPatients();

echo json_encode($existingPatientList);
