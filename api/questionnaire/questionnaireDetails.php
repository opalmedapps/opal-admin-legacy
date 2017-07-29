<?php
    // get list of questionnaires existed
    include_once('questionnaire.inc');

    $callback = $_GET['callback'];
    $serNum = $_GET['serNum'];

    $questionnaire = new Questionnaire();

    $questionnaireDetails = $questionnaire->getQuestionnaireDetails($serNum);

    // Callback to http request
    print $callback.'('.json_encode($questionnaireDetails).')';
?>