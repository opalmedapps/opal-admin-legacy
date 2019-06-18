<?php
include_once('questionnaire.inc');

// Retrieve form param
$callback = strip_tags($_GET['callback']);
$OAUserId = strip_tags($_GET["OAUserId"]);

$questionnaire = new Questionnaire($OAUserId);
$questionnaireList = $questionnaire->getFinalizedQuestionnaires();

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionnaireList).')';
?>
