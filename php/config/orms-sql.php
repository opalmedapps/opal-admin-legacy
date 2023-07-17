<?php

// use config file to get the env variables
use config;

// DEFINE Waiting Room Management SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MySQL setup.
define( "WRM_DB_ENABLED", (intval($_ENV["WRM_DB_ENABLED"]) == 0?false:true));
define( "WRM_DB_HOST", $_ENV["WRM_DB_HOST"]);
define( "WRM_DB_PORT", $_ENV["WRM_DB_PORT"]);
define( "WRM_DB_NAME", $_ENV["WRM_DB_NAME"]);
define( "WRM_DB_NAME_FED", $_ENV["FEDERATED_WRM_DB_NAME"]);
define( "WRM_DB_DSN", "mysql:host=" . WRM_DB_HOST . ";port=" . WRM_DB_PORT . ";dbname=" . WRM_DB_NAME . ";charset=utf8" );
define( "WRM_DB_USERNAME",  $_ENV["WRM_DB_USER"]);
define( "WRM_DB_PASSWORD",  $_ENV["WRM_DB_PASSWORD"]);
define("ORMS_MEDIVISIT_APPOINTMENT_LIST_TABLE", "MediVisitAppointmentList");
define("ORMS_SQL_GET_APPOINTMENT_FOR_ALIAS","
    SELECT DISTINCT mval.AppointmentCode AS code, mval.ResourceDescription AS expression 
    FROM ".ORMS_MEDIVISIT_APPOINTMENT_LIST_TABLE." mval
    WHERE mval.AppointSys in ('Medivisit','Impromptu','ImpromptuOrtho','InstantAddOn')
    ORDER BY mval.AppointmentCode, mval.ResourceDescription
");
