<?php
/* To insert a newly-created answer type into our database */
include_once('questionnaire.inc');

// Construct array from FORM params
$answerTypeObj = new TemplateQuestion(); // Object
$answerTypeObj->insertTemplateQuestion($_POST);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);