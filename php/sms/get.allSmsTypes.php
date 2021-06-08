<?php

header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms(); // Object
$smsTypeList = $sms->getAllTypeMessage();

echo json_encode($smsTypeList);
