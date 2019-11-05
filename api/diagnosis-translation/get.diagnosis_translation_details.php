<?php
header('Content-Type: application/javascript');

include_once('diagnosis-translation.inc');

$serial = $_POST['serial'];
$Diagnosis = new Diagnosis; // Object
$diagnosisTranslationDetails = $Diagnosis->getDiagnosisTranslationDetails($serial);

echo json_encode($diagnosisTranslationDetails);