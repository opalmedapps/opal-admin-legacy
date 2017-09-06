<?php
    /* To get a list of distinct answer type categories */
    include_once('questionnaire.inc');

	// Retrieve form param
    $callback = $_GET['callback'];

    $answerType = new AnswerType(); // Object

	// Call function
    $answerTypeCategoryList = $answerType->getAnswerTypeCategories();

    // Callback to http request
    print $callback.'('.json_encode($answerTypeCategoryList).')';
?>