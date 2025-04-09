<?php

// SPDX-FileCopyrightText: Copyright (C) 2019 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once('publication.inc');

$publication = new Publication();
$result = $publication->getPublicationModulesUser();

header('Content-Type: application/javascript');
echo json_encode($result);
