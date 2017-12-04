<?php

	/* 
	 * PHP global settings:
	 */

    // Turn on all errors except for notices
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('display_errors', 1);	

	// DEFINE HOST SERVER/DATABASE CREDENTIALS HERE
	// NOTE: This works for a MySQL setup. 
	define( "OPAL_DB_HOST", "172.26.66.41" );
	define( "OPAL_DB_PORT", "3306" );
	define( "OPAL_DB_NAME", "OpalDB_PREPROD" );
	define( "OPAL_DB_DSN", "mysql:host=" . OPAL_DB_HOST . ";port=" . OPAL_DB_PORT . ";dbname=" . OPAL_DB_NAME . ";charset=utf8" ); 
	define( "OPAL_DB_USERNAME", "readonly" ); 
	define( "OPAL_DB_PASSWORD", "readonly" ); 

	// DEFINE LEGACY QUESTIONNAIRE SERVER/DATABASE CREDENTIALS HERE
	// NOTE: This works for a MySQL setup. 
	define( "QUESTIONNAIRE_DB_HOST", "172.26.66.41" );
	define( "QUESTIONNAIRE_DB_PORT", "3306" );
	define( "QUESTIONNAIRE_DB_NAME", "QuestionnaireDB" );
	define( "QUESTIONNAIRE_DB_DSN", "mysql:host=" . QUESTIONNAIRE_DB_HOST . ";port=" . QUESTIONNAIRE_DB_PORT . ";dbname=" . QUESTIONNAIRE_DB_NAME . ";charset=utf8" ); 
	define( "QUESTIONNAIRE_DB_USERNAME", "readonly" ); 
	define( "QUESTIONNAIRE_DB_PASSWORD", "readonly" );

    // DEFINE ARIA SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MicrosoftSQL (MSSQL) setup. 
    define( "ARIA_DB_HOST", "172.16.220.56" );
    define( "ARIA_DB_PORT", "1433");
	define( "ARIA_DB_DSN", "dblib:host=" . ARIA_DB_HOST . ":" . ARIA_DB_PORT . "\\database" ); 
	define( "ARIA_DB_USERNAME", "reports" ); 
	define( "ARIA_DB_PASSWORD", "reports" ); 

    // DEFINE Waiting Room Management SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MySQL setup.
	define( "WRM_DB_HOST", "172.26.66.41" );
	define( "WRM_DB_PORT", "3306" );
	define( "WRM_DB_NAME", "WaitRoomManagement" );    
    define( "WRM_DB_DSN", "mysql:host=" . WRM_DB_HOST . ";port=" . WRM_DB_PORT . ";dbname=" . WRM_DB_NAME ); 
	define( "WRM_DB_USERNAME", "readonly" );
	define( "WRM_DB_PASSWORD", "readonly" );

	// DEFINE MOSAIQ SERVER/DATABASE CREDENTIALS HERE
    // NOTE: This works for a MicrosoftSQL (MSSQL) setup. 
    define( "MOSAIQ_DB_HOST", "MOSAIQ_DB_HOST_HERE" );
    define( "MOSAIQ_DB_PORT", "MOSAIQ_DB_PORT_HERE");
	define( "MOSAIQ_DB_DSN", "dblib:host=" . MOSAIQ_DB_HOST . ":" . MOSAIQ_DB_PORT . "\\database" ); 
	define( "MOSAIQ_DB_USERNAME", "MOSAIQ_DB_USERNAME_HERE" ); 
	define( "MOSAIQ_DB_PASSWORD", "MOSAIQ_DB_PASSWORD_HERE" ); 

	// Environment-specific variables 
	define( "FRONTEND_ABS_PATH", "/var/www/devDocuments/opalAdmin/" );
	define( "FRONTEND_REL_URL", "/devDocuments/opalAdmin/" );
	define( "BACKEND_ABS_PATH", FRONTEND_ABS_PATH . "publisher/" ); 
	define( "BACKEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", BACKEND_ABS_PATH) );
	define( "FRONTEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", FRONTEND_ABS_PATH) ); 
	
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

    // Push Notification FCM and APN credientials.
    define( "API_KEY" , "AIzaSyC08_s4--jQSGJw6cdwlq67T_0ZCLBkCwA" );
    define( "CERTIFICATE_PASSWORD" , "" );
    define( "CERTIFICATE_FILE" , BACKEND_ABS_PATH . 'php/certificates/apns-dev-cert.pem' );


?>
