<?php
include_once("../config.php");

$diagnosis = new Diagnosis();
$results = $diagnosis->getDiagnosisTranslations();

header('Content-Type: application/javascript');
echo json_encode($results);