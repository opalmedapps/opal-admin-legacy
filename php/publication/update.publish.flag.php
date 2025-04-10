<?php

// SPDX-FileCopyrightText: Copyright (C) 2019 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once('publication.inc');

//$questionnaire_serNum = strip_tags($_POST['questionnaire_serNum']);

$publicationList = $_POST["flagList"];
$publishedQuestionnaire = new Publication();
$publishedQuestionnaire->updatePublicationFlags($publicationList);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
