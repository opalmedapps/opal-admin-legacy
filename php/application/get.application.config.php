<?php

// SPDX-FileCopyrightText: Copyright (C) 2022 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

include_once('../config.php');

/**
 * Filter config to only include enabled state of databases and AD login.
 */
$restrictedConfig = array();
$restrictedConfig['login']['activeDirectory']['enabled'] =  AD_LOGIN_ACTIVE;
$restrictedConfig['newOpalAdminHost'] =  NEW_OPALADMIN_HOST_EXTERNAL;

if (ORMS_ENABLED) {
    $restrictedConfig['ormsHost'] = ORMS_HOST;
}

// opaldb and questionnairedb are always enabled
$restrictedConfig['databaseConfig']['opal']['enabled'] = 1;
$restrictedConfig['databaseConfig']['questionnaire2019']['enabled'] = 1;

header('Content-Type: application/json');
echo json_encode($restrictedConfig);
