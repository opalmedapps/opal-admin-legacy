<?php

include_once("../config.php");

$sourceDiag = new MasterSourceAlias(); // Object
$results = $sourceDiag->markAsDeletedSourceAliases($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);