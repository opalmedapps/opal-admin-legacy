<?php
/* To insert a newly-created answer type into our database */
include_once('questionnaire.inc');

// Construct array from FORM params
$answerTypeArray = array(
    'ID' => strip_tags($_POST['ID']),
    'private' => strip_tags($_POST['private']),
    'userId' => strip_tags($_POST['userId']),
    'options' => strip_tags($_POST['options'])

);

$answerTypeObj = new QuestionType; // Object

// Call function
$answerTypeObj->insertAnswerType($answerTypeArray);
?>
