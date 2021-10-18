<?php

include_once("../config.php");

$sourceDiag = new MasterSourceTask(); // Object
$results = $sourceDiag->markAsDeletedSourceTasks($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);