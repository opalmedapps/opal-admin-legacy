<?php
include_once("../config.php");

$resource = new Resource();
$resource->insertResource($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);