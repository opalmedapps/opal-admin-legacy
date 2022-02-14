<?php
include_once("../config.php");

$staff = new TriggerStaff();
$staff->updateStaff($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);