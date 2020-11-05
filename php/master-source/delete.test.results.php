<?php

include_once("../config.php");

$sourceDiag = new MasterSourceTestResult(); // Object
$results = $sourceDiag->markAsDeletedSourceTestResults($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);