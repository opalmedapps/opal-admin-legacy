<?php
header('Content-Type: application/javascript');
include_once('diagnosis-translation.inc');

$diagnosis = new Diagnosis; // Object
$results = $diagnosis->getEducationalMaterials();

echo json_encode($results);