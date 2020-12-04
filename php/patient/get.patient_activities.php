<?php

header('Content-Type: application/javascript');
include_once('patient.inc');

$patient = new Patient; // Object
$patientActivityList = $patient->getPatientActivities();

echo json_encode($patientActivityList);