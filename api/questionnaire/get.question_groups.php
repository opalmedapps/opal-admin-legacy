<?php
    /* To get a list of existing question groups */
    include_once('questionnaire.inc');

	// Retrieve form params
    $callback = $_GET['callback'];
	$userid = $_GET['userid'];
	
    $questionGroup = new QuestionGroup(); // Object

	// Call function
    $questionGroupList = $questionGroup->getQuestionGroups($userid);

    // Callback to http request
    print $callback.'('.json_encode($questionGroupList).')';
?>