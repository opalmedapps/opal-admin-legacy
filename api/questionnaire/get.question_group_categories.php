<?php
    /* To get a list of question group categories */
    include_once('questionnaire.inc');

	// Retrieve form param
    $callback = $_GET['callback'];

    $category = new Category(); // Object

	// Call function
    $categoryList = $category->getQuestionGroupCategories();

    // Callback to http request
    print $callback.'('.json_encode($categoryList).')';
?>