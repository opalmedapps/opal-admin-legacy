<?php
/**
 * User: Dominic Bourdua
 * Date: 6/4/2019
 * Time: 7:31 AM
 */

include_once('questionnaire.inc');

$callback = strip_tags($_POST['callback']);
$templateQuestionId = strip_tags($_POST['templateQuestionId']);
$OAUserId = strip_tags($_POST['OAUserId']);

$templateQuestion = new TemplateQuestion($OAUserId);
$templateQuestionDetails = $templateQuestion->getTemplateQuestionDetails($templateQuestionId);

header('Content-Type: application/javascript');
echo json_encode($templateQuestionDetails);