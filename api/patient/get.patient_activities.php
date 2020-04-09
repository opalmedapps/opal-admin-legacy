<?php

header('Content-Type: application/javascript');
include_once('patient.inc');
$OAUserId =  $_POST['userId'];

$patient = new Patient($OAUserId); // Object
$patientActivityList = $patient->getPatientActivities();

echo json_encode($patientActivityList);
