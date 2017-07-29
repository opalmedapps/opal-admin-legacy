<?php
    // get list of questionnaires existed
    include_once('questionnaire.inc');

    $callback = $_GET['callback'];
    $userid = $_GET['userid'];

    $answerType = new AnswerType();

    $answerTypeList = $answerType->getAnswerTypes($userid);

    // Callback to http request
    print $callback.'('.json_encode($answerTypeList).')';
?>