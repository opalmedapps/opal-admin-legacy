<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$questionId = strip_tags($_POST['questionId']);

$question = new Question();
$questionDetails = $question->getQuestionDetails($questionId);
unset($questionDetails['question']);
unset($questionDetails["tableName"]);
unset($questionDetails["subTableName"]);
unset($questionDetails["display"]);
unset($questionDetails["definition"]);
unset($questionDetails["question"]);

header('Content-Type: application/javascript');
echo json_encode($questionDetails);
?>
