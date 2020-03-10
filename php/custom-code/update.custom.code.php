<?php

include_once('custom.code.inc');

$OAUserId = strip_tags(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $_POST['OAUserId']));
$sessionId = strip_tags(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $_POST['sessionid']));

$customCode = new CustomCode($OAUserId, $sessionId);
$customCode->updateCustomCode($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);