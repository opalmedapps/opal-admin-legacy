<?php
    /* To get questionnaire details */
    include_once('questionnaire.inc');

	// Retrieve form params
    $callback = $_GET['callback'];
    $serNum = $_GET['serNum'];

    $questionnaire = new Questionnaire(); // Object

	// Call function
    $questionnaireDetails = $questionnaire->getQuestionnaireDetails($serNum);

    // Callback to http request
    print $callback.'('.json_encode($questionnaireDetails).')';
?>