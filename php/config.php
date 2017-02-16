<?php

	/* 
	 * PHP global settings:
	 */

    // Turn on all errors except for notices
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);	

	// DEFINE HOST SERVER/DATABASE CREDENTIALS HERE
	// NOTE: This works for a MySQL setup. Change as needed but the REQUIRED constants are
	// - OPAL_DB_DSN
	// - OPAL_DB_USERNAME
	// - OPAL_DB_PASSWORD
	define( "OPAL_DB_HOST", "172.26.66.41" );
	define( "OPAL_DB_PORT", "22" );
	define( "OPAL_DB_NAME", "OpalDB_AJ_Sandbox" );
	define( "OPAL_DB_DSN", "mysql:host=" . OPAL_DB_HOST . ";dbname=" . OPAL_DB_NAME . ";charset=utf8" ); // Required constant
	define( "OPAL_DB_USERNAME", "readonly" ); // Required constant
	define( "OPAL_DB_PASSWORD", "readonly" ); // Required constant

    // DEFINE SOURCE SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MicrosoftSQL (MSSQL) setup. Change as needed but the REQUIRED constants are
    // - SOURCE_DB_DSN
    // - SOURCE_DB_USERNAME
    // - SOURCE_DB_PASSWORD
    define( "SOURCE_DB_HOST", "172.16.220.56" );
    define( "SOURCE_DB_PORT", "1433");
	define( "SOURCE_DB_DSN", "dblib:host=" . SOURCE_DB_HOST . ":" . SOURCE_DB_PORT . "\\database" ); // Required constant
	define( "SOURCE_DB_USERNAME", "reports" ); // Required constant
	define( "SOURCE_DB_PASSWORD", "reports" ); // Required constant

    // WaitRoomManagement (specific to our hospital's setup)
    define( "WRM_DSN", "mysql:host=localhost;dbname=WaitingRoomManagement_dev" ); 
	define( "WRM_USERNAME", "root" );
	define( "WRM_PASSWORD", "service" );
	define( "WRM_HOST", "172.26.66.41" );
	define( "WRM_PORT", "22" );

	// Environment-specific variables 
	define( "FRONTEND_ABS_PATH", "/var/www/devDocuments/opalAdmin/" );
	define( "FRONTEND_REL_URL", "/devDocuments/opalAdmin/" );
	define( "BACKEND_ABS_PATH", "/usr/lib/cgi-bin/dev/opalAdmin/" ); 
	define( "BACKEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", BACKEND_ABS_PATH) );
	define( "FRONTEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", FRONTEND_ABS_PATH) ); 
	
	// Include the classes
	include_once( FRONTEND_ABS_PATH . "php/classes/User.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Alias.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Post.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/EduMaterial.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/HospitalMap.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Notification.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Filter.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Cron.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/CrontabManager.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Patient.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/TestResult.php" );

?>
