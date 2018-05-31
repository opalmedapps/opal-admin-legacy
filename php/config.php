<?php

	/* 
	 * PHP global settings:
	 */

    // Turn on all errors except for notices
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);	

	// Get directory path of this file
	$pathname 	= __DIR__;
	// Strip php directory 
	$abspath 	= str_replace('php', '', $pathname);

	// Specify location of config file
	$json = file_get_contents($abspath . 'config.json');

	// Decode json to variable
	$config = json_decode($json, true);

	// DEFINE OPAL SERVER/DATABASE CREDENTIALS HERE
	// NOTE: This works for a MySQL setup. 
	define( "OPAL_DB_HOST", $config['databaseConfig']['opal']['host'] );
	define( "OPAL_DB_PORT", $config['databaseConfig']['opal']['port'] );
	define( "OPAL_DB_NAME", $config['databaseConfig']['opal']['name'] );
	define( "OPAL_DB_DSN", "mysql:host=" . OPAL_DB_HOST . ";port=" . OPAL_DB_PORT . ";dbname=" . OPAL_DB_NAME . ";charset=utf8" ); 
	define( "OPAL_DB_USERNAME", $config['databaseConfig']['opal']['username'] ); 
	define( "OPAL_DB_PASSWORD", $config['databaseConfig']['opal']['password'] ); 

	// DEFINE LEGACY QUESTIONNAIRE SERVER/DATABASE CREDENTIALS HERE
	// NOTE: This works for a MySQL setup. 
	define( "QUESTIONNAIRE_DB_HOST", $config['databaseConfig']['questionnaire']['host'] );
	define( "QUESTIONNAIRE_DB_PORT", $config['databaseConfig']['questionnaire']['port'] );
	define( "QUESTIONNAIRE_DB_NAME", $config['databaseConfig']['questionnaire']['name'] );
	define( "QUESTIONNAIRE_DB_DSN", "mysql:host=" . QUESTIONNAIRE_DB_HOST . ";port=" . QUESTIONNAIRE_DB_PORT . ";dbname=" . QUESTIONNAIRE_DB_NAME . ";charset=utf8" ); 
	define( "QUESTIONNAIRE_DB_USERNAME", $config['databaseConfig']['questionnaire']['username'] ); 
	define( "QUESTIONNAIRE_DB_PASSWORD", $config['databaseConfig']['questionnaire']['password'] );

    // DEFINE ARIA SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MicrosoftSQL (MSSQL) setup. 
    define( "ARIA_DB_HOST", $config['databaseConfig']['aria']['host'] );
    define( "ARIA_DB_PORT", $config['databaseConfig']['aria']['port']);
	define( "ARIA_DB_DSN", "dblib:host=" . ARIA_DB_HOST . ":" . ARIA_DB_PORT . "\\database" . ";charset=utf8"); 
	define( "ARIA_DB_USERNAME", $config['databaseConfig']['aria']['username'] ); 
	define( "ARIA_DB_PASSWORD", $config['databaseConfig']['aria']['password'] ); 

    // DEFINE Waiting Room Management SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MySQL setup.
	define( "WRM_DB_HOST", $config['databaseConfig']['wrm']['host'] );
	define( "WRM_DB_PORT", $config['databaseConfig']['wrm']['port'] );
	define( "WRM_DB_NAME", $config['databaseConfig']['wrm']['name'] );    
    define( "WRM_DB_DSN", "mysql:host=" . WRM_DB_HOST . ";port=" . WRM_DB_PORT . ";dbname=" . WRM_DB_NAME . ";charset=utf8" ); 
	define( "WRM_DB_USERNAME", $config['databaseConfig']['wrm']['username'] );
	define( "WRM_DB_PASSWORD", $config['databaseConfig']['wrm']['password'] );

	// DEFINE MOSAIQ SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MicrosoftSQL (MSSQL) setup. 
    define( "MOSAIQ_DB_HOST", $config['databaseConfig']['mosaiq']['host'] );
    define( "MOSAIQ_DB_PORT", $config['databaseConfig']['mosaiq']['port'] );
	define( "MOSAIQ_DB_DSN", "dblib:host=" . MOSAIQ_DB_HOST . ":" . MOSAIQ_DB_PORT . "\\database" . ";charset=utf8" ); 
	define( "MOSAIQ_DB_USERNAME", $config['databaseConfig']['mosaiq']['username'] ); 
	define( "MOSAIQ_DB_PASSWORD", $config['databaseConfig']['mosaiq']['password'] ); 

	// Environment-specific variables 
	define( "FRONTEND_ABS_PATH", $config['pathConfig']['abs_path'] );
	define( "FRONTEND_REL_URL", $config['pathConfig']['relative_url'] );
	define( "BACKEND_ABS_PATH", FRONTEND_ABS_PATH . "publisher/" ); 
	define( "BACKEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", BACKEND_ABS_PATH) );
	define( "FRONTEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", FRONTEND_ABS_PATH) ); 
	define( "UPLOAD_ABS_PATH", FRONTEND_ABS_PATH . "uploads/" );
	define( "UPLOAD_REL_PATH", FRONTEND_REL_URL . "uploads/" );
	
	// Include the classes
	include_once( FRONTEND_ABS_PATH . "php/classes/User.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Database.php" );
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
	include_once( FRONTEND_ABS_PATH . "php/classes/Install.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Email.php" );
	include_once( FRONTEND_ABS_PATH . "php/classes/Questionnaire/Questionnaire.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Questionnaire/Question.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Questionnaire/QuestionGroup.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Questionnaire/Tag.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Questionnaire/Category.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Questionnaire/AnswerType.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Questionnaire/Library.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/LegacyQuestionnaire.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Diagnosis.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Application.php");
	include_once( FRONTEND_ABS_PATH . "php/classes/Encrypt.php");
	//include_once( FRONTEND_ABS_PATH . "php/classes/Upload.php");

    // Push Notification FCM and APN credientials.
    define( "API_KEY" , $config['pushNotificationConfig']['android']['apiKey'] );
    define( "CERTIFICATE_PASSWORD" , $config['pushNotificationConfig']['apple']['certificate']['password'] );
    define( "CERTIFICATE_FILE" , BACKEND_ABS_PATH . 'php/certificates/' . $config['pushNotificationConfig']['apple']['certificate']['filename'] );

?>
