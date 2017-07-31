<?php
    // get list of questionnaires existed
    include_once('questionnaire.inc');

    $callback = $_GET['callback'];

    $answerType = new AnswerType();

    $atCategoryList = $answerType->getAtCategory();

    // Callback to http request
    print $callback.'('.json_encode($atCategoryList).')';
?>