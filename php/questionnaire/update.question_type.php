<?php
/**
 * User: Dominic Bourdua
 * Date: 6/4/2019
 * Time: 10:28 AM
 */

include_once('questionnaire.inc');

$answerTypeObj = new TemplateQuestion(strip_tags($_POST["OAUserId"])); // Object
$answerTypeObj->updateTemplateQuestion($_POST);

header('Content-Type: application/javascript');
$response['code'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);