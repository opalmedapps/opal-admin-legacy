<?php
include_once("../config.php");

// Construct array from FORM params
$answerTypeObj = new TemplateQuestion(); // Object
$answerTypeObj->insertTemplateQuestion($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);