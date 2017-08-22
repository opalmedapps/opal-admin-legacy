<?php
	include_once('questionnaire.inc');

	$serNum = $_POST['serNum'];

	$questionObj = new Question;

	$response = $questionObj->deleteQuestion($serNum);
	
    print json_encode($response); // Return response
?>