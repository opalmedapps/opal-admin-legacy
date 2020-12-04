<?php
header('Content-Type: application/javascript');
include_once('test-result.inc');

$testResult = new TestResult; // Object
$results = $testResult->getEducationalMaterials();

echo json_encode($results);