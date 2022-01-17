<?php
include_once("../config.php");

$sms = new Sms();
$smsEventList = $sms->getMessages($_POST);

header('Content-Type: application/javascript');
echo json_encode($smsEventList);
