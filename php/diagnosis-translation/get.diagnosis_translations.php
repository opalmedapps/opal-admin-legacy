<?php
header('Content-Type: application/javascript');
/* To get a list of existing diagnosis translations */
include_once('diagnosis-translation.inc');

$Diagnosis = new Diagnosis; // Object
$existingDiagnosisTranslationList = $Diagnosis->getExistingDiagnosisTranslations();

echo json_encode($existingDiagnosisTranslationList);