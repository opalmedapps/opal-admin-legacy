<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$questionnaireId = strip_tags($_POST['ID']);
$questionnaireObj = new Questionnaire();

$response = $questionnaireObj->deleteQuestionnaire($questionnaireId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);