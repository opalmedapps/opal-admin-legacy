<?php
include_once("../config.php");

$alert = new Alert();
$alert->insertAlert($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);