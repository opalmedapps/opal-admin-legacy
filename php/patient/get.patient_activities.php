<?php
include_once("../config.php");

$patient = new Patient(); // Object
$patientActivityList = $patient->getPatientActivities();

header('Content-Type: application/javascript');
echo json_encode($patientActivityList);