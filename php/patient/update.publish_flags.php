<?php
include_once("../config.php");

$patient = new Patient();
$patient->updatePublishFlags($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);