<?php
include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$questionnaireId = strip_tags($_GET['questionnaireId']);
$userId = strip_tags($_GET['userId']);

$questionnaire = new Questionnaire($userId);
$questionnaireDetails = $questionnaire->getQuestionnaireDetails($questionnaireId);
unset($questionnaireDetails["category"]);
unset($questionnaireDetails["createdBy"]);
unset($questionnaireDetails["creationDate"]);
unset($questionnaireDetails["lastUpdated"]);
unset($questionnaireDetails["updatedBy"]);
unset($questionnaireDetails["parentId"]);
unset($questionnaireDetails["optionalFeedback"]);
unset($questionnaireDetails["version"]);

header('Content-Type: application/javascript');
echo $callback.'('.json_encode($questionnaireDetails).')';
?>
