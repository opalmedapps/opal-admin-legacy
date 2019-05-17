<?php
include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$userId = strip_tags($_GET['userId']);

$questionnaire = new Questionnaire($userId);
$questionnairesList = $questionnaire->getQuestionnaires();

header('Content-Type: application/javascript');
print $callback.'('.json_encode($questionnairesList).')';
?>
