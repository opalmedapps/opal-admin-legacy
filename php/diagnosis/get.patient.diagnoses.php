<?php
include_once("../config.php");

$diagnosisObject = new Diagnosis();
$diagnoses = $diagnosisObject->getPatientDiagnoses($_POST);

header('Content-Type: application/javascript');
echo json_encode($diagnoses);