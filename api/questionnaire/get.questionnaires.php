<?php
    /* To get a list of existing questionnaires */
    include_once('questionnaire.inc');

	// Retrieve form params
    $callback = $_GET['callback'];
	$userid = $_GET['userid'];

    $questionnaire = new Questionnaire(); // object

	// Call function
    $questionnairesList = $questionnaire->getQuestionnaires($userid);

    // Callback to http request
    print $callback.'('.json_encode($questionnairesList).')';
?>