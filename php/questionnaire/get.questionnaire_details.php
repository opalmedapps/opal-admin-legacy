<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$questionnaireId = strip_tags($_POST['questionnaireId']);

$questionnaire = new Questionnaire();
$questionnaireDetails = $questionnaire->getQuestionnaireDetails($questionnaireId);
unset($questionnaireDetails["category"]);
unset($questionnaireDetails["createdBy"]);
unset($questionnaireDetails["creationDate"]);
unset($questionnaireDetails["lastUpdated"]);
unset($questionnaireDetails["updatedBy"]);
unset($questionnaireDetails["parentId"]);
unset($questionnaireDetails["optionalFeedback"]);
unset($questionnaireDetails["version"]);

header('Content-Type: application/javascript');
echo json_encode($questionnaireDetails);
?>
