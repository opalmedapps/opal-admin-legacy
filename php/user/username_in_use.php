<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$username = strip_tags($_POST['username']);
$userObj = new User(); // Object
$count = $userObj->usernameExists($username);

header('Content-Type: application/javascript');
echo json_encode(array("count"=>$count));
