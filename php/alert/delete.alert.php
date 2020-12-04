<?php
include_once("../config.php");

$alertId = strip_tags($_POST['alertId']);

$alert = new Alert(); // Object
$response = $alert->deleteAlert($alertId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);