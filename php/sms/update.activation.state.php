<?php
include_once("../config.php");

$sms = new Sms();
$sms->updateActivationState($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
