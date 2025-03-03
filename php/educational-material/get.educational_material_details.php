<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get details on a particular educational material */
include_once('educational-material.inc');

$serial = strip_tags($_POST['serial']);
$eduMat = new EduMaterial; // Object
$eduMatDetails = $eduMat->getEducationalMaterialDetails($serial);

echo json_encode($eduMatDetails);