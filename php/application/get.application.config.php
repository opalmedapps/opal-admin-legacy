<?php

    function filter_credentials($key) {
        return $key == 'enabled';
    }
    
    include_once('../config.php');
    
    /**
     * Filter config to only include enabled state of databases and AD login.
     */
    $restrictedConfig = array();
    $restrictedConfig['databaseConfig'] = $config['databaseConfig'];
    $restrictedConfig['login']['activeDirectory']['enabled'] =  $config['login']['activeDirectory']['enabled'];
    $restrictedConfig['pathConfig']['registration_url'] =  $config['pathConfig']['registration_url'];
    
    foreach ($restrictedConfig['databaseConfig'] as $key => $value) {
        if (is_array($value)) {
            $newValue = array_filter($value, 'filter_credentials', ARRAY_FILTER_USE_KEY);
            $restrictedConfig['databaseConfig'][$key] = $newValue;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($restrictedConfig);

?>
