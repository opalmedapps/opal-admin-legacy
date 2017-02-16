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
	define( "OPAL_DB_HOST", "OPAL_DB_HOST_HERE" );
	define( "OPAL_DB_PORT", "OPAL_DB_PORT_HERE" );
	define( "OPAL_DB_NAME", "OPAL_DB_NAME_HERE" );
	define( "OPAL_DB_DSN", "mysql:host=" . OPAL_DB_HOST . ";dbname=" . OPAL_DB_NAME . ";charset=utf8" ); // Required constant
	define( "OPAL_DB_USERNAME", "OPAL_DB_USERNAME_HERE" ); // Required constant
	define( "OPAL_DB_PASSWORD", "OPAL_DB_PASSWORD_HERE" ); // Required constant

    // DEFINE SOURCE SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MicrosoftSQL (MSSQL) setup. Change as needed but the REQUIRED constants are
    // - SOURCE_DB_DSN
    // - SOURCE_DB_USERNAME
    // - SOURCE_DB_PASSWORD
    define( "SOURCE_DB_HOST", "SOURCE_DB_HOST_HERE" );
    define( "SOURCE_DB_PORT", "SOURCE_DB_PORT_HERE");
	define( "SOURCE_DB_DSN", "dblib:host=" . SOURCE_DB_HOST . ":" . SOURCE_DB_PORT . "\\database" ); // Required constant
	define( "SOURCE_DB_USERNAME", "SOURCE_DB_USERNAME_HERE" ); // Required constant
	define( "SOURCE_DB_PASSWORD", "SOURCE_DB_PASSWORD_HERE" ); // Required constant

	// Environment-specific variables 
	define( "FRONTEND_ABS_PATH", "FRONTEND_ABS_PATH_HERE" );
	define( "FRONTEND_REL_URL", "FRONTEND_REL_URL_HERE" );
	define( "BACKEND_ABS_PATH", "BACKEND_ABS_PATH_HERE" ); 
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
