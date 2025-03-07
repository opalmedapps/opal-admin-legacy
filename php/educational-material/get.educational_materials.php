<?php

// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

header('Content-Type: application/javascript');
/* To get a list of existing educational materials */
include_once('educational-material.inc');

$eduMat = new EduMaterial; // Object
$existingEduMatList = $eduMat->getEducationalMaterials();

echo json_encode($existingEduMatList);