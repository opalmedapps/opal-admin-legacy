<?php
/* To insert a newly-created answer type into our database */
include_once('questionnaire.inc');

// Construct array from FORM params
$questionTypeArray = array(
    'typeId' => strip_tags($_POST['ID']),
    'name_EN' => strip_tags($_POST['name_EN']),
    'name_FR' => strip_tags($_POST['name_FR']),
    'private' => strip_tags($_POST['private']),
    'userId' => strip_tags($_POST['userId']),
    'options' => $_POST['options'],
);

if ($questionTypeArray["typeId"] == SLIDERS)
{
    $questionTypeArray["MinCaption_EN"] = strip_tags($_POST["MinCaption_EN"]);
    $questionTypeArray["MinCaption_FR"] = strip_tags($_POST["MinCaption_FR"]);
    $questionTypeArray["MaxCaption_EN"] = strip_tags($_POST["MaxCaption_EN"]);
    $questionTypeArray["MaxCaption_FR"] = strip_tags($_POST["MaxCaption_FR"]);
    $questionTypeArray["minValue"] = strip_tags($_POST["minValue"]);
    $questionTypeArray["maxValue"] = strip_tags($_POST["maxValue"]);
    $questionTypeArray["increment"] = strip_tags($_POST["increment"]);

    if($questionTypeArray["MinCaption_EN"] == "" || $questionTypeArray["MinCaption_FR"] || $questionTypeArray["MaxCaption_EN"] || $questionTypeArray["MaxCaption_FR"] || intval($questionTypeArray["minValue"]) <= 0 || intval($questionTypeArray["maxValue"]) <= 0 || intval($questionTypeArray["increment"]) <= 0)
        HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question type format");
}

if($questionTypeArray["typeId"] == "" || $questionTypeArray["name_EN"] == "" || $questionTypeArray["name_FR"] == "" || $questionTypeArray["userId"] == "")
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question type format");

if (($questionTypeArray["typeId"] == CHECKBOXES || $questionTypeArray["typeId"] || RADIO_BUTTON) && count($questionTypeArray["options"]) <= 0)
    HelpSetup::returnErrorMessage(HTTP_STATUS_INTERNAL_SERVER_ERROR, "Invalid question type format");

$answerTypeObj = new QuestionType($questionTypeArray["userId"]); // Object

// Call function
$answerTypeObj->insertQuestionType($questionTypeArray);

header('Content-Type: application/javascript');
$response['message'] = HTTP_STATUS_SUCCESS;
echo json_encode($response);


?>
