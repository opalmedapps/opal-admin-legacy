<?php
header('Content-Type: application/javascript');

include_once('diagnosis-translation.inc');

$diagnosisObject = new Diagnosis; // Object
$diagnoses = $diagnosisObject->getDiagnoses();

echo json_encode($diagnoses);