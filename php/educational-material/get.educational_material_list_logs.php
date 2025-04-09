<?php

// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get list logs on a particular educational material */
include_once('educational-material.inc');

$serials = json_decode($_POST['serials']);
$eduMat = new EduMaterial; // Object
$educationalMaterialLogs = $eduMat->getEducationalMaterialListLogs($serials);

echo json_encode($educationalMaterialLogs);
