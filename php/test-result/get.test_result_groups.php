<?php

header('Content-Type: application/javascript');
include_once('test-result.inc');

$testResult = new TestResult; // Object
$groups = $testResult->getTestResultGroups();
echo json_encode($groups);