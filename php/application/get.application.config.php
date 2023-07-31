<?php

    include_once('../config.php');

    /**
     * Filter config to only include enabled state of databases and AD login.
     */
    $restrictedConfig = array();
    $restrictedConfig['login']['activeDirectory']['enabled'] =  $_ENV["AD_ENABLED"];
    // REGISTRATION_PATH is a global variable defined in config.php
    $restrictedConfig['pathConfig']['registration_url'] =  REGISTRATION_PATH;
    $restrictedConfig['newOpalAdminHost']=  $_ENV["NEW_OPALADMIN_HOST_EXTERNAL"];

    // opaldb always enabled
    $restrictedConfig['databaseConfig']['opal']['enabled'] = 1;

    // Check if the database is enabled in the environment file to include it in the api service
    if ($_ENV["QUESTIONNAIRE_DB_ENABLED"] == 1){
        $restrictedConfig['databaseConfig']['questionnaire2019']['enabled'] = $_ENV["QUESTIONNAIRE_DB_ENABLED"];
    }
    
    header('Content-Type: application/json');
    echo json_encode($restrictedConfig);

?>
