<?php

include_once("../config.php");

$sourceDiag = new MasterSourceDiagnosis(); // Object
$results = $sourceDiag->markAsDeletedSourceDiagnoses($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);