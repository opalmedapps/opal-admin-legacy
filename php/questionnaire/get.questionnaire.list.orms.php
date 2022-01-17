<?php

include_once("../config.php");

$ormsList = new Questionnaire();
$result = $ormsList->getQuestionnaireListOrms($_POST);

header('Content-Type: application/javascript');
echo json_encode($result);