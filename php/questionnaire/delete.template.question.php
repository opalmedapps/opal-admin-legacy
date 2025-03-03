<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

// Retrieve FORM param
$templateQuestionId = strip_tags($_POST['ID']);

// Call function
$templateQuestionObj = new TemplateQuestion(); // Object

$response = $templateQuestionObj->deleteTemplateQuestion($templateQuestionId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);