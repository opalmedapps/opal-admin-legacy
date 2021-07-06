<?php

/*
* PHP global settings:
*/
session_start();

// Set the time ze for the Eastern Time Zone (ET)
date_default_timezone_set("America/Toronto");

// Turn on all errors except for notices
error_reporting(E_ALL & ~E_NOTICE ^ E_WARNING);
ini_set('display_startup_errors', 1);
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
define( "ADMIN_REGISTRATION_URL", $config['pathConfig']['registration_url'] );

define("ALIAS_TYPE_APPOINTMENT_TEXT", 'Appointment');
define("ALIAS_TYPE_DOCUMENT_TEXT", 'Document');
define("ALIAS_TYPE_TASK_TEXT", 'Task');

define("ALIAS_TYPE_TASK", 1);
define("ALIAS_TYPE_APPOINTMENT", 2);
define("ALIAS_TYPE_DOCUMENT", 3);

/*
 * Module ID of each module in the opalAdmin
 * */
define("MODULE_ALIAS", 1);
define("MODULE_POST", 2);
define("MODULE_EDU_MAT", 3);
define("MODULE_HOSPITAL_MAP", 4);
define("MODULE_NOTIFICATION", 5);
define("MODULE_TEST_RESULTS", 6);
define("MODULE_QUESTIONNAIRE", 7);
define("MODULE_PUBLICATION", 8);
define("MODULE_DIAGNOSIS_TRANSLATION", 9);
define("MODULE_CRON_LOG", 10);
define("MODULE_PATIENT", 11);
define("MODULE_USER", 12);
define("MODULE_STUDY", 13);
define("MODULE_EMAIL", 14);
define("MODULE_CUSTOM_CODE", 15);
define("MODULE_ROLE", 16);
define("MODULE_ALERT", 17);
define("MODULE_AUDIT", 18);
define("MODULE_TRIGGER", 19);
define("MODULE_MASTER_SOURCE", 20);
define("LOCAL_SOURCE_ONLY", -1);

define("MODULE_PUBLICATION_TRIGGER",array(MODULE_QUESTIONNAIRE, MODULE_ALERT, MODULE_EDU_MAT, MODULE_POST));

define("DELETED_RECORD", 1);
define("NON_DELETED_RECORD", 0);
define("NON_FINAL_RECORD", 0);
define("FINAL_RECORD", 1);
define("PRIVATE_RECORD", 1);
define("PUBLIC_RECORD", 0);
define("ACTIVE_RECORD", 1);
define("INACTIVE_RECORD", 0);
define("HUMAN_USER", 1);
define("SYSTEM_USER", 2);

define("PURPOSE_RESEARCH", 2);
define("PURPOSE_CONSENT", 4);
define("RESPONDENT_PATIENT", 1);

define("TRIGGER_EVENT_PUBLISH", 1);

define("OPAL_QUESTIONNAIRE_COMPLETED_FLAG",1);

require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."general-sql.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."questionnaire-sql.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."opal-sql.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."questionnaire-sql-queries.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."opal-sql-queries.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."aria-sql.php");
require_once(FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."orms-sql.php");

// Include the classes
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Module.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "User.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Alias.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Audit.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Post.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "EduMaterial.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "HospitalMap.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Notification.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Cron.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "CrontabManager.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Patient.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TestResult.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Install.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Email.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "QuestionnaireModule.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Publication.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceModule.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceAlias.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceAppointment.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceTask.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceDocument.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceDiagnosis.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceTestResult.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "CustomCode.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Study.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Alert.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Role.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Question.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TemplateQuestion.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Library.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Diagnosis.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Application.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "HelpSetup.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseAccess.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseQuestionnaire.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseOpal.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseAria.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseOrms.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseDisconnected.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Trigger.php" );

// Push Notification FCM and APN credientials.
define( "API_KEY" , $config['pushNotificationConfig']['android']['apiKey'] );
define( "ANDROID_URL" , $config['pushNotificationConfig']['android']['androidURL'] );
define( "CERTIFICATE_PASSWORD" , $config['pushNotificationConfig']['apple']['certificate']['password'] );
define( "CERTIFICATE_FILE" , BACKEND_ABS_PATH . 'php' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $config['pushNotificationConfig']['apple']['certificate']['filename'] );
define( "APNS_TOPIC" , $config['pushNotificationConfig']['apple']['certificate']['topic'] );
define( "CERTIFICATE_KEY" , BACKEND_ABS_PATH . 'php' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $config['pushNotificationConfig']['apple']['certificate']['key'] );
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
define("ORMS_SOURCE_DB", 2);
define("MOSAIQ_SOURCE_DB", 3);
define("LOCAL_SOURCE_DB", -1);

define("USER_SALT", $config["login"]["salt"]);
define("ACTIVE_DIRECTORY", $config["login"]["activeDirectory"]);
define("ACTIVE_DIRECTORY_SETTINGS", $config["login"]["activeDirectory"]["settings"]);
define("AD_LOGIN_ACTIVE", ACTIVE_DIRECTORY["enabled"]);

define("ACCESS_READ", 1);
define("ACCESS_READ_WRITE", 3);
define("ACCESS_READ_WRITE_DELETE", 7);

define("PUBLICATION_PUBLISH_DATE", 9);
define("GUEST_ACCOUNT", 29);

define("ACCESS_GRANTED", "GRANTED");
define("ACCESS_DENIED", "DENIED");
define("ENCRYPTED_DATA", "ENCRYPTED DATA");
define("UNKNOWN_USER", "UNKNOWN USER");

define("MAXIMUM_RECORDS_BATCH", 500);

define("ALIAS_TYPE_TASK",1);
define("ALIAS_TYPE_APPOINTMENT",2);
define("ALIAS_TYPE_DOCUMENT",3);

/*
 * List of HTTP status codes
 * */
define("HTTP_STATUS_SUCCESS",200);
define("HTTP_STATUS_INTERNAL_SERVER_ERROR",500);
define("HTTP_STATUS_BAD_REQUEST_ERROR",400);
define("HTTP_STATUS_NOT_AUTHENTICATED_ERROR",401);
define("HTTP_STATUS_FORBIDDEN_ERROR",403);
define("HTTP_STATUS_SESSION_TIMEOUT_ERROR",419);
define("HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR",422);
define("HTTP_STATUS_LOGIN_TIMEOUT_ERROR",440);
define("HTTP_STATUS_HTTP_TO_HTTPS_ERROR",497);

if(!$ignoreSecuredConnection) {
    if($_SERVER["HTTPS"] != "on") {
        HelpSetup::returnErrorMessage(HTTP_STATUS_HTTP_TO_HTTPS_ERROR, "Connection not secured.");
        exit();
    }
}

define("ABVR_FRENCH_LANGUAGE", "FR");
define("ABVR_ENGLISH_LANGUAGE", "EN");
// all language abbreviations in opal admin
define("OPAL_ADMIN_LANGUAGES",array(ABVR_FRENCH_LANGUAGE, ABVR_ENGLISH_LANGUAGE));


/*
 * PHP Sessions config
 * */
define("PHP_SESSION_TIMEOUT", 7200);