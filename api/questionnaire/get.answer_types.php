<?php
    /* To get a list of answer types */
    include_once('questionnaire.inc');

	// Retrieve FORM params
    $callback = $_GET['callback'];
    $userid = $_GET['userid'];

    $answerType = new AnswerType(); // Object

	// Call function
    $answerTypeList = $answerType->getAnswerTypes($userid);

    // Callback to http request
    print $callback.'('.json_encode($answerTypeList).')';
?>