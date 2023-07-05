<?php

    function filter_credentials($key) {
        return $key == 'enabled';
    }

    include_once('../config.php');

    /**
     * Filter config to only include enabled state of databases and AD login.
     */
    $restrictedConfig = array();
    $restrictedConfig['login']['activeDirectory']['enabled'] =  config::getApplicationSettings()->environment->loginActiveDirectoryEnabled;
    $restrictedConfig['pathConfig']['registration_url'] =  config::getApplicationSettings()->environment->registrationUrl;
    $restrictedConfig['newOpalAdminHost']=  config::getApplicationSettings()->environment->newOpaladminHost;


    $restrictedConfig['databaseConfig']['opal']['host'] = config::getApplicationSettings()->environment->opalDbHost;
    $restrictedConfig['databaseConfig']['opal']['port'] = config::getApplicationSettings()->environment->opalDbPort;
    $restrictedConfig['databaseConfig']['opal']['username'] = config::getApplicationSettings()->environment->opalDbUser;
    $restrictedConfig['databaseConfig']['opal']['password'] = config::getApplicationSettings()->environment->opalDbPassword;
    $restrictedConfig['databaseConfig']['opal']['name'] = config::getApplicationSettings()->environment->opalDbName;

    // Check if the database is enabled in the environment file to include it in the api service
    if (config::getApplicationSettings()->environment->questionnaireDbEnabled == 1){
        $restrictedConfig['databaseConfig']['questionnaire2019']['host'] = config::getApplicationSettings()->environment->questionnaireDbHost;
        $restrictedConfig['databaseConfig']['questionnaire2019']['port'] = config::getApplicationSettings()->environment->questionnaireDbPort;
        $restrictedConfig['databaseConfig']['questionnaire2019']['username'] = config::getApplicationSettings()->environment->questionnaireDbUser;
        $restrictedConfig['databaseConfig']['questionnaire2019']['password'] = config::getApplicationSettings()->environment->questionnaireDbPassword;
        $restrictedConfig['databaseConfig']['questionnaire2019']['name'] = config::getApplicationSettings()->environment->questionnaireDbName;
    }
    if (config::getApplicationSettings()->environment->ariaDbEnabled == 1){
        $restrictedConfig['databaseConfig']['aria']['host'] = config::getApplicationSettings()->environment->ariaDbHost;
        $restrictedConfig['databaseConfig']['aria']['port'] = config::getApplicationSettings()->environment->ariaDbPort;
        $restrictedConfig['databaseConfig']['aria']['username'] = config::getApplicationSettings()->environment->ariaDbUser;
        $restrictedConfig['databaseConfig']['aria']['password'] = config::getApplicationSettings()->environment->ariaDbPassword;
        $restrictedConfig['databaseConfig']['aria']['name'] = config::getApplicationSettings()->environment->ariaDbName;
    }
    if (config::getApplicationSettings()->environment->wrmDbEnabled == 1){
        $restrictedConfig['databaseConfig']['wrm']['host'] = config::getApplicationSettings()->environment->wrmDbHost;
        $restrictedConfig['databaseConfig']['wrm']['port'] = config::getApplicationSettings()->environment->wrmDbPort;
        $restrictedConfig['databaseConfig']['wrm']['username'] = config::getApplicationSettings()->environment->wrmDbUser;
        $restrictedConfig['databaseConfig']['wrm']['password'] = config::getApplicationSettings()->environment->wrmDbPassword;
        $restrictedConfig['databaseConfig']['wrm']['name'] = config::getApplicationSettings()->environment->wrmDbName;
    }
    if (config::getApplicationSettings()->environment->mosaiqDbEnabled == 1){
        $restrictedConfig['databaseConfig']['mosaiq']['host'] = config::getApplicationSettings()->environment->mosaiqDbHost;
        $restrictedConfig['databaseConfig']['mosaiq']['port'] = config::getApplicationSettings()->environment->mosaiqDbPort;
        $restrictedConfig['databaseConfig']['mosaiq']['username'] = config::getApplicationSettings()->environment->mosaiqDbUser;
        $restrictedConfig['databaseConfig']['mosaiq']['password'] = config::getApplicationSettings()->environment->mosaiqDbPassword;
        $restrictedConfig['databaseConfig']['mosaiq']['name'] = config::getApplicationSettings()->environment->mosaiqDbName;
    }
    
    header('Content-Type: application/json');
    echo json_encode($restrictedConfig);

?>
