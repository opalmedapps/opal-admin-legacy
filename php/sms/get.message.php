<?php

include_once('sms.inc');
$type = strip_tags($_POST['type']);
$event = strip_tags($_POST['event']);
$language = strip_tags($_POST['language']);

$sms = new Sms();
$sms->getMessage();

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
