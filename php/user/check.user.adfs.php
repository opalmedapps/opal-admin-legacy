<?php

// SPDX-FileCopyrightText: Copyright (C) 2024 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

$userObj = new User();

$is_ad_user_exist = $userObj->isADUserExist($_POST);
header('Content-Type: application/javascript');

// true if user exists in AD system, false otherwise.
echo json_encode(array("is_exist"=>$is_ad_user_exist));
