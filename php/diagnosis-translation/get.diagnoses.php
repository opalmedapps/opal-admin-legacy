<?php
include_once("../config.php");

$diagnosisObject = new Diagnosis();
$diagnoses = $diagnosisObject->getDiagnoses();

header('Content-Type: application/javascript');
echo json_encode($diagnoses);