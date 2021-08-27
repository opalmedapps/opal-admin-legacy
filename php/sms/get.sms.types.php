<?php
include_once("../config.php");

$sms = new Sms();
$smsTypeList = $sms->getSmsType($_POST);

header('Content-Type: application/javascript');
echo json_encode($smsTypeList);