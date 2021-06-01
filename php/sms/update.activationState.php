<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms();
$response = $sms->updateActivationState($_POST);

echo json_encode($response);
