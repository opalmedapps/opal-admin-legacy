<?php
header('Content-Type: application/javascript');
include_once('sms.inc');

$messageUpdates	= $_POST['UpdateInformation'];

$sms = new Sms();
$response = $sms->updateSmsMessage($messageUpdates,'English');
$response += $sms->updateSmsMessage($messageUpdates,'French');

echo json_encode($response);
