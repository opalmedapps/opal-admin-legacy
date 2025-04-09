<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$serial = strip_tags($_POST["serial"]);
$type = strip_tags($_POST["type"]);

$post = new Post();
$postLogs = $post->getPostChartLogs($serial, $type);

header('Content-Type: application/javascript');
echo json_encode($postLogs);
