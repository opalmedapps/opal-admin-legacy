<?php
include_once("../config.php");

$testResult = new TestResult();
$groups = $testResult->getTestResultGroups();

header('Content-Type: application/javascript');
echo json_encode($groups);