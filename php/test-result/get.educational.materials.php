<?php
include_once("../config.php");

$testResult = new TestResult; // Object
$results = $testResult->getEducationalMaterials();

header('Content-Type: application/javascript');
echo json_encode($results);