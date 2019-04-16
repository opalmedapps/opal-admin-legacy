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
}

if($questionTypeArray["typeId"] == "" || $questionTypeArray["name_EN"] == "" || $questionTypeArray["name_FR"] == "" || $questionTypeArray["userId"] == "")
{
    header('Content-Type: application/javascript');
    $response['value'] = false;
    $response['message'] = 500;
    $response['details'] = "Invalid question type format";
    echo json_encode($response);
    die();
}

$answerTypeObj = new QuestionType($questionTypeArray["userId"]); // Object

// Call function
$answerTypeObj->insertQuestionType($questionTypeArray);

header('Content-Type: application/javascript');
$response['value'] = true;
$response['message'] = 200;
echo json_encode($response);


?>
