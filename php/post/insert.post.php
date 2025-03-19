<?php
include_once("../config.php");

$postObject = new Post();
$postObject->insertPost($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);