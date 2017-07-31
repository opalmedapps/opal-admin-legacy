<?php
    // get list of questionnaires existed
    include_once('questionnaire.inc');

    $callback = $_GET['callback'];
	$userid = $_GET['userid'];

    $questionnaire = new Questionnaire();

    $questionnairesList = $questionnaire->getQuestionnaire($userid);

    // Callback to http request
    print $callback.'('.json_encode($questionnairesList).')';
?>