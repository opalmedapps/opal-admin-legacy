<?php
/**
 * User: Dominic Bourdua
 * Date: 6/4/2019
 * Time: 7:31 AM
 */

include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$questionId = strip_tags($_GET['questionTypeId']);
$OAUserId = strip_tags($_GET['OAUserId']);

$question = new QuestionType($OAUserId);
$questionTypeDetails = $question->getQuestionTypeDetails($questionId);

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionTypeDetails).')';