<?php
header('Content-Type: application/javascript');
include_once("../config.php");

$sms = new Sms(); // Object
$smsEventList = $sms->getMessages($_POST);

echo json_encode($smsEventList);
