<?php

include_once('../config.php');

/**
 * Filter config to only include enabled state of databases and AD login.
 */
$restrictedConfig = array();
$restrictedConfig['login']['activeDirectory']['enabled'] =  AD_LOGIN_ACTIVE;
$restrictedConfig['newOpalAdminHost'] =  NEW_OPALADMIN_HOST_EXTERNAL;
$restrictedConfig['ormsHost'] = ORMS_HOST;

// opaldb and questionnairedb are always enabled
$restrictedConfig['databaseConfig']['opal']['enabled'] = 1;
$restrictedConfig['databaseConfig']['questionnaire2019']['enabled'] = 1;

header('Content-Type: application/json');
echo json_encode($restrictedConfig);
