<?php
header('Content-Type: application/javascript');
/* To delete a question */
include_once('questionnaire.inc');

// Retrieve FORM param
$serNum = strip_tags($_POST['ID']);
$userId = strip_tags($_POST['userId']);

$userId = 20;
$serNum = 885;

// Call function
$questionObj = new Question($userId); // Object
$response = $questionObj->deleteQuestion($serNum);

print json_encode($response); // Return response
?>
