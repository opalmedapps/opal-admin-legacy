<?php

/*
* PHP global settings:
*/

// Set the time ze for the Eastern Time Zone (ET)
date_default_timezone_set("America/Toronto");

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

$localHostAddr = array('127.0.0.1','localhost','::1');

// DEFINE LEGACY QUESTIONNAIRE SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MySQL setup.
define( "QUESTIONNAIRE_DB_HOST", $config['databaseConfig']['questionnaire']['host'] );
define( "QUESTIONNAIRE_DB_PORT", $config['databaseConfig']['questionnaire']['port'] );
define( "QUESTIONNAIRE_DB_NAME", $config['databaseConfig']['questionnaire']['name'] );
define( "QUESTIONNAIRE_DB_DSN", "mysql:host=" . QUESTIONNAIRE_DB_HOST . ";port=" . QUESTIONNAIRE_DB_PORT . ";dbname=" . QUESTIONNAIRE_DB_NAME . ";charset=utf8" );
define( "QUESTIONNAIRE_DB_USERNAME", $config['databaseConfig']['questionnaire']['username'] );
define( "QUESTIONNAIRE_DB_PASSWORD", $config['databaseConfig']['questionnaire']['password'] );

// DEFINE MOSAIQ SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MicrosoftSQL (MSSQL) setup.
define( "MOSAIQ_DB_HOST", $config['databaseConfig']['mosaiq']['host'] );
define( "MOSAIQ_DB_PORT", $config['databaseConfig']['mosaiq']['port'] );
define( "MOSAIQ_DB_DSN", "dblib:host=" . MOSAIQ_DB_HOST . ":" . MOSAIQ_DB_PORT . "\\database" . ";charset=utf8" );
define( "MOSAIQ_DB_USERNAME", $config['databaseConfig']['mosaiq']['username'] );
define( "MOSAIQ_DB_PASSWORD", $config['databaseConfig']['mosaiq']['password'] );

// Environment-specific variables
define( "FRONTEND_ABS_PATH", str_replace("/", DIRECTORY_SEPARATOR, $config['pathConfig']['abs_path'] ));
define( "FRONTEND_REL_URL", str_replace("/", DIRECTORY_SEPARATOR, $config['pathConfig']['relative_url'] ));
define( "BACKEND_ABS_PATH", FRONTEND_ABS_PATH . "publisher/" );
define( "BACKEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", BACKEND_ABS_PATH) );
define( "FRONTEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", FRONTEND_ABS_PATH) );
define( "UPLOAD_ABS_PATH", FRONTEND_ABS_PATH . "uploads/" );
define( "UPLOAD_REL_PATH", FRONTEND_REL_URL . "uploads/" );

require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."general-sql.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."questionnaire-sql.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."opal-sql.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."aria-sql.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."orms-sql.php");

// Include the classes
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "OpalProject.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "User.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Database.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Alias.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Post.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "EduMaterial.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "HospitalMap.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Notification.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Filter.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Cron.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "CrontabManager.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Patient.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TestResult.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Install.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Email.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "QuestionnaireModule.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "PublishedQuestionnaire.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Publication.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Question.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TemplateQuestion.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Library.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Diagnosis.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Application.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Encrypt.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "HelpSetup.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseAccess.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseQuestionnaire.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseOpal.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseAria.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseOrms.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseDisconnected.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "CronJob.php" );
//include_once( FRONTEND_ABS_PATH . "php/classes/Upload.php");

// Push Notification FCM and APN credientials.
define( "API_KEY" , $config['pushNotificationConfig']['android']['apiKey'] );
define( "ANDROID_URL" , $config['pushNotificationConfig']['android']['androidURL'] );
define( "CERTIFICATE_PASSWORD" , $config['pushNotificationConfig']['apple']['certificate']['password'] );
define( "CERTIFICATE_FILE" , BACKEND_ABS_PATH . 'php' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $config['pushNotificationConfig']['apple']['certificate']['filename'] );
define( "IOS_URL" , $config['pushNotificationConfig']['apple']['appleURL'] );

/*
 * Question type definition for the legacy questionnaire and the new questionnaire 2019
 * */
define("CHECKBOXES", 1);
define("SLIDERS", 2);
define("TEXT_BOX", 3);
define("RADIO_BUTTON", 4);
define("LABEL", 5);
define("TIME", 6);
define("DATE", 7);
define("LEGACY_MC", 1);
define("LEGACY_MINMAX", 2);
define("LEGACY_SA", 3);
define("LEGACY_CHECKBOX", 4);
define("LEGACY_YESNO", 9);
define("DEFAULT_TYPE", TEXT_BOX);

define("ARIA_SOURCE_DB", 1);
define("MEDIVISIT_SOURCE_DB", 2);
define("MOSAIQ_SOURCE_DB", 3);

/*
 * Cron Job configuration
 * */
define("SETTING_CRONJOB",1);
define("ROLE_CRONJOB",8);
define("OAUSER_CRONJOB",23);

/*
 * Module ID of each module in the opalAdmin
 * */
define("MODULE_POST", 2);
define("MODULE_EDU_MAT", 3);
define("MODULE_QUESTIONNAIRE", 7);

define("PUBLICATION_PUBLISH_DATE", 9);

/*
 * List of HTTP status codes
 * */
define("HTTP_STATUS_SUCCESS",200);
define("HTTP_STATUS_INTERNAL_SERVER_ERROR",500);

/*
 * Miscellaneous constants
 * */
define("DEFAULT_FIELD", "");