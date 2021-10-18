<?php
include_once("../config.php");

$sourceDiag = new MasterSourceTestResult(); // Object
$results = $sourceDiag->getSourceTestResults();

header('Content-Type: application/javascript');
echo json_encode($results);