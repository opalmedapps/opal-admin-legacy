<?php
header('Content-Type: application/javascript');
include_once("../config.php");
$speciality = strip_tags($_POST['speciality']);

$sms = new Sms();
$smsTypeList = $sms->getTypeMessage($speciality);

echo json_encode($smsTypeList);