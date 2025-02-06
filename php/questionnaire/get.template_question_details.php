<?php

// SPDX-FileCopyrightText: Copyright (C) 2019 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * User: Dominic Bourdua
 * Date: 6/4/2019
 * Time: 7:31 AM
 */

include_once("../config.php");

$templateQuestionId = strip_tags($_POST['templateQuestionId']);

$templateQuestion = new TemplateQuestion();
$templateQuestionDetails = $templateQuestion->getTemplateQuestionDetails($templateQuestionId);

header('Content-Type: application/javascript');
echo json_encode($templateQuestionDetails);