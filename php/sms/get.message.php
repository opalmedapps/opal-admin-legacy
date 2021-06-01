<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms();
$smsMessage = $sms->getMessage($_POST);

echo json_encode($smsMessage);
