<?php
include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$OAUserId = strip_tags($_GET['OAUserId']);

$questionnaire = new Questionnaire($OAUserId);
$questionnairesList = $questionnaire->getQuestionnaires();

header('Content-Type: application/javascript');
print $callback.'('.json_encode($questionnairesList).')';
?>
