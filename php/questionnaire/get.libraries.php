<?php

// SPDX-FileCopyrightText: Copyright (C) 2017 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * User: Dominic Bourdua
 * Date: 4/16/2019
 * Time: 1:45 PM
 */

include_once("../config.php");

$questionLibrary = new Library();
$result = $questionLibrary->getLibraries();

header('Content-Type: application/javascript');
echo json_encode($result);
