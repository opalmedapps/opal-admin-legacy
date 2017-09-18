<?php
    /* To get question details */
    include_once('questionnaire.inc');

	// Retrieve form params
    $callback = $_GET['callback'];
    $questionSerNum = $_GET['questionSerNum'];

    $question = new Question(); // Object

	// Call function
    $questionDetails = $question->getQuestionDetails($questionSerNum);

    // Callback to http request
    print $callback.'('.json_encode($questionDetails).')';
?>