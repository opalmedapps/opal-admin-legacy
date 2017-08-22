<?php
    // get list of questionnaires existed
    include_once('questionnaire.inc');

    $callback = $_GET['callback'];
    $questionSerNum = $_GET['questionSerNum'];

    $question = new Question();

    $questionDetails = $question->getQuestionDetails($questionSerNum);

    // Callback to http request
    print $callback.'('.json_encode($questionDetails).')';
?>