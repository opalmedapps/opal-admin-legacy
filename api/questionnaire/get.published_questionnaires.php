<?php
/**
 * User: Dominic Bourdua
 * Date: 6/12/2019
 * Time: 8:27 AM
 */

include_once('questionnaire.inc');

$OAUserId = strip_tags($_POST['OAUserId']);
$questionnaire = new PublishedQuestionnaire($OAUserId);
$questionnairesList = $questionnaire->getPublishedQuestionnaires();

header('Content-Type: application/javascript');
echo json_encode($questionnairesList);