<?php

// use config file to get the env variables
use config;

// DEFINE Waiting Room Management SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MySQL setup.
define( "WRM_DB_ENABLED", (intval(config::getApplicationSettings()->environment->wrmDbEnabled) == 0?false:true));
define( "WRM_DB_HOST", config::getApplicationSettings()->environment->wrmDbHost);
define( "WRM_DB_PORT", config::getApplicationSettings()->environment->wrmDbPort);
define( "WRM_DB_NAME", config::getApplicationSettings()->environment->wrmDbName);
define( "WRM_DB_NAME_FED", config::getApplicationSettings()->environment->wrmFedDbName);
define( "WRM_DB_DSN", "mysql:host=" . WRM_DB_HOST . ";port=" . WRM_DB_PORT . ";dbname=" . WRM_DB_NAME . ";charset=utf8" );
define( "WRM_DB_USERNAME", config::getApplicationSettings()->environment->wrmDbUser);
define( "WRM_DB_PASSWORD", config::getApplicationSettings()->environment->wrmDbPassword);
define( "WRM_API_URL", $config['databaseConfig']['wrm']['api']['url'] );
define( "WRM_API_METHOD", $config['databaseConfig']['wrm']['api']['method'] );
define( "WRM_API_CONFIG", $config['databaseConfig']['wrm']['api']['config'] );
define( "ORMS_API_BASE_URL", $config['databaseConfig']['wrm']['api']['url'] );
define( "ORMS_API_CONFIG", $config['databaseConfig']['wrm']['api']['config'] );

define("ORMS_MEDIVISIT_APPOINTMENT_LIST_TABLE", "MediVisitAppointmentList");

define("ORMS_SQL_GET_APPOINTMENT_FOR_ALIAS","
    SELECT DISTINCT mval.AppointmentCode AS code, mval.ResourceDescription AS expression 
    FROM ".ORMS_MEDIVISIT_APPOINTMENT_LIST_TABLE." mval
    WHERE mval.AppointSys in ('Medivisit','Impromptu','ImpromptuOrtho','InstantAddOn')
    ORDER BY mval.AppointmentCode, mval.ResourceDescription
");
