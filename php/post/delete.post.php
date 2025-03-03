<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$postId = strip_tags($_POST["serial"]);

$postObject = new Post();
$postObject->deletePost($postId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);