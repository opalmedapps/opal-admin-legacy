<?php
/**
 * User: Dominic Bourdua
 * Date: 6/12/2019
 * Time: 8:27 AM
 */
include_once('questionnaire.inc');
header('Content-Type: application/javascript');

$callback = strip_tags($_GET['callback']);
$OAUserId = strip_tags($_GET['OAUserId']);

$questionnaire = new PublishedQuestionnaire($OAUserId);
$questionnairesList = $questionnaire->getPublishedQuestionnaires();

print $callback.'('.json_encode($questionnairesList).')';
?>
