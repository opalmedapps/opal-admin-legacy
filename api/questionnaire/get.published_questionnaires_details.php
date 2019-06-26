<?php
/**
 * User: Dominic Bourdua
 * Date: 6/19/2019
 * Time: 1:31 PM
 */

include_once('questionnaire.inc');

$publishedQuestionnaireId = strip_tags($_POST['publishedQuestionnaireId']);
$OAUserId = strip_tags($_POST['OAUserId']);

$questionnaire = new PublishedQuestionnaire($OAUserId);
$questionnairesList = $questionnaire->getPublishedQuestionnaireDetails($publishedQuestionnaireId);

header('Content-Type: application/javascript');
echo json_encode($questionnairesList);