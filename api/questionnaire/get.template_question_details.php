<?php
/**
 * User: Dominic Bourdua
 * Date: 6/4/2019
 * Time: 7:31 AM
 */

include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$templateQuestionId = strip_tags($_GET['templateQuestionId']);
$OAUserId = strip_tags($_GET['OAUserId']);

$templateQuestion = new TemplateQuestion($OAUserId);
$templateQuestionDetails = $templateQuestion->getTemplateQuestionDetails($templateQuestionId);

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($templateQuestionDetails).')';