<?php
include_once('questionnaire.inc');

$callback = strip_tags($_GET['callback']);
$questionnaireId = strip_tags($_GET['questionnaireId']);
$OAUserId = strip_tags($_GET['OAUserId']);

$questionnaire = new Questionnaire($OAUserId);
$questionnaireDetails = $questionnaire->getQuestionnaireDetails($questionnaireId);
$questionnaireDetails["OAUserId"] = $questionnaireDetails["OAUserID"];
unset($questionnaireDetails["OAUserID"]);
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
