<?php

	/* Configuration module used by various php files */

    //set off all error for security purposes
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);	

	//define some constants
    define( "DB_USERNAME", "root" );
	define( "DB_PASSWORD", "service" );
	define( "HOST", "172.26.66.41" );
	define( "PORT", "22" );
	define( "HOST_USERNAME", "webdb" );
	define( "HOST_PASSWORD", "service" );
    // ARIA
	define( "ARIA_DB", "dblib:host=172.16.220.56:1433\\database" );
	define( "ARIA_USERNAME", "reports" );
	define( "ARIA_PASSWORD", "reports" );
    // WaitRoomManagement
    define( "WRM_DSN", "mysql:host=localhost;dbname=WaitingRoomManagement_dev" ); 
	define( "WRM_USERNAME", "root" );
	define( "WRM_PASSWORD", "service" );
	define( "WRM_HOST", "172.26.66.41" );
	define( "WRM_PORT", "22" );

	// environment-specific variables (DEV)
    define( "DB_DSN", "mysql:host=localhost;dbname=OpalDB_AJ_Sandbox;charset=utf8" ); 
	define( "DB_NAME", "OpalDB_AJ_Sandbox" ); 
	define( "ABS_PATH", "/var/www/devDocuments/opalAdmin/" );
	define( "ABS_URL", "/devDocuments/opalAdmin/" );
	define( "PERL_PATH", "/usr/lib/cgi-bin/dev/opalAdmin/"); 
	define( "PERL_REGEX", "/\/usr\/lib\/cgi-bin\/dev\/opalAdmin\/");
	define( "ABS_REGEX", "/\/var\/www\/devDocuments\/opalAdmin\/php\/cron\/update_crontab.php "); 
    define( "SESSION_KEY_NAME", "OA_DEV_username");
    define( "SESSION_KEY_LOGIN", "OA_DEV_loginAttempt");
    define( "SESSION_KEY_REGISTER", "OA_DEV_registerAttempt");
    define( "SESSION_KEY_USERID", "OA_DEV_userid");
	
	//include the classes
	include_once( ABS_PATH . "php/classes/User.php" );
	include_once( ABS_PATH . "php/classes/Alias.php" );
	include_once( ABS_PATH . "php/classes/Post.php" );
	include_once( ABS_PATH . "php/classes/EduMaterial.php" );
	include_once( ABS_PATH . "php/classes/HospitalMap.php" );
	include_once( ABS_PATH . "php/classes/Notification.php" );
	include_once( ABS_PATH . "php/classes/Filter.php" );
	include_once( ABS_PATH . "php/classes/Cron.php" );
	include_once( ABS_PATH . "php/classes/CrontabManager.php" );
	include_once( ABS_PATH . "php/classes/Patient.php" );
	include_once( ABS_PATH . "php/classes/TestResult.php" );

?>
