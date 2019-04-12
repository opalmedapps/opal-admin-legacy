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
    'options' => ($_POST['options']),
);

$questionTypeArray = array(
    "typeId" => "1",
    "name_EN" => "This is a test",
    "name_FR" => "Ceci est un test",
    "private" => "0",
    "userId" => "20",
    "options" => array(
        array(
            "text_EN" => "Yes",
            "text_FR" => "Oui",
            "position" => "2",
            "last_updated_by" => "20",
            "created_by" => "20",
        ),
        array(
            "text_EN" => "No",
            "text_FR" => "Non",
            "position" => "4",
            "last_updated_by" => "20",
            "created_by" => "20",
        ),
        array(
            "text_EN" => "Maybe",
            "text_FR" => "Peut-être",
            "position" => "6",
            "last_updated_by" => "20",
            "created_by" => "20",
        ),
        array(
            "text_EN" => "Do not want to answer",
            "text_FR" => "Ne veut pas répondre",
            "position" => "8",
            "last_updated_by" => "20",
            "created_by" => "20",
        ),
    ),
);

$answerTypeObj = new QuestionType($questionTypeArray["userId"]); // Object

// Call function
$answerTypeObj->insertQuestionType($questionTypeArray);
?>
