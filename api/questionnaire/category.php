<?php
    // get list of questionnaires existed
    include_once('questionnaire.inc');

    $callback = $_GET['callback'];

    $category = new Category();

    $categoryList = $category->getCategory();

    // Callback to http request
    print $callback.'('.json_encode($categoryList).')';
?>