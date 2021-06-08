<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms(); // Object
$response = $sms->updateSmsMessage($_POST);

echo json_encode($response);
