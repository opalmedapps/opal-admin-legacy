<?php
/**
 * User: Dominic Bourdua
 * Date: 6/19/2019
 * Time: 1:31 PM
 */

include_once('questionnaire.inc');
header('Content-Type: application/javascript');

$callback = strip_tags($_GET['callback']);
$publishedQuestionnaireId = strip_tags($_GET['publishedQuestionnaireId']);
$OAUserId = strip_tags($_GET['OAUserId']);

$questionnaire = new PublishedQuestionnaire($OAUserId);
$questionnairesList = $questionnaire->getPublishedQuestionnaireDetails($publishedQuestionnaireId);

print $callback.'('.json_encode($questionnairesList).')';
