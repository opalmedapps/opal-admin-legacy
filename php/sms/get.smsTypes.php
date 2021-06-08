<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms(); // Object
$smsTypeList = $sms->getTypeMessage($_POST);

echo json_encode($smsTypeList);