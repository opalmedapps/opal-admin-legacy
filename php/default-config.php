<?php

	/* 
	 * PHP global settings:
	 */

    // Turn on all errors except for notices
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);	

	// DEFINE HOST SERVER/DATABASE CREDENTIALS HERE
	// NOTE: This works for a MySQL setup. Change as needed but the REQUIRED constants are
	// - HOST_DB_DSN
	// - HOST_DB_USERNAME
	// - HOST_DB_PASSWORD
	define( "HOST_SERVER_IP", "" );
	define( "HOST_SERVER_PORT", "" );
	define( "HOST_DB_NAME", "" );
	define( "HOST_DB_DSN", "mysql:host=" . HOST_SERVER_IP . ";dbname=" . HOST_DB_NAME . ";charset=utf8" ); // Required constant
	define( "HOST_DB_USERNAME", "" ); // Required constant
	define( "HOST_DB_PASSWORD", "" ); // Required constant

    // DEFINE SOURCE SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MicrosoftSQL (MSSQL) setup. Change as needed but the REQUIRED constants are
    // - SOURCE_DB_DSN
    // - SOURCE_DB_USERNAME
    // - SOURCE_DB_PASSWORD
    define( "SOURCE_SERVER_IP", "" );
    define( "SOURCE_SERVER_PORT", "");
	define( "SOURCE_DB_DSN", "dblib:host=" . SOURCE_SERVER_IP . ":" . SOURCE_SERVER_PORT . "\\database" ); // Required constant
	define( "SOURCE_DB_USERNAME", "" ); // Required constant
	define( "SOURCE_DB_PASSWORD", "" ); // Required constant

	// Environment-specific variables 
	define( "FRONTEND_ABS_PATH", "" );
	define( "FRONTEND_REL_URL", "" );
	define( "BACKEND_ABS_PATH", "" ); 
	define( "BACKEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", BACKEND_ABS_PATH) );
	define( "FRONTEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", FRONTEND_ABS_PATH) ); 
    define( "SESSION_KEY_NAME", "OA_DEV_username" );
    define( "SESSION_KEY_LOGIN", "OA_DEV_loginAttempt" );
    define( "SESSION_KEY_REGISTER", "OA_DEV_registerAttempt" );
    define( "SESSION_KEY_USERID", "OA_DEV_userid" );
	
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
