<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$patient = new Patient; // Object
$existingPatientList = $patient->getPatients();

echo json_encode($existingPatientList);