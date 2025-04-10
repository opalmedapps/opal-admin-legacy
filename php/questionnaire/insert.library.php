<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once("../config.php");

// Construct array from FORM params
$libraryArray = array(
    'name_EN' => strip_tags($_POST['name_EN']),
    'name_FR' => strip_tags($_POST['name_FR']),
    'private' => strip_tags($_POST['private'])
);

$libraryObj = new Library(); // Object

// Call function
$libraryObj->insertLibrary($libraryArray);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);
