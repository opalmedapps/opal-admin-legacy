<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$serials = json_decode(strip_tags($_POST['serials']));
$type = ( strip_tags($_POST['type']) === 'undefined' ) ? null : strip_tags($_POST['type']);

$post = new Post(); // Object
$postLogs = $post->getPostListLogs($serials, $type);

header('Content-Type: application/javascript');
echo json_encode($postLogs);