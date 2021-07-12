<?php
include_once("../config.php");

$answerTypeObj = new TemplateQuestion(); // Object
$answerTypeObj->updateTemplateQuestion($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);