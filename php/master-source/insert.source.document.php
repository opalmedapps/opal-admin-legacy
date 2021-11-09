<?php

include_once("../config.php");

$sourceDiag = new MasterSourceDocument(); // Object
$results = $sourceDiag->insertSourceDocuments($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);