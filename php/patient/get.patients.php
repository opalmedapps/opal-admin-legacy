<?php
include_once("../config.php");

$patient = new Patient(); // Object
$existingPatientList = $patient->getPatients();

header('Content-Type: application/javascript');
echo json_encode($existingPatientList);