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

// DEFINE QUESTIONNAIRE 2019 SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MySQL setup.
define( "QUESTIONNAIRE_DB_2019_HOST", $config['databaseConfig']['questionnaire2019']['host'] );
define( "QUESTIONNAIRE_DB_2019_PORT", $config['databaseConfig']['questionnaire2019']['port'] );
define( "QUESTIONNAIRE_DB_2019_NAME", $config['databaseConfig']['questionnaire2019']['name'] );
define( "QUESTIONNAIRE_DB_2019_DSN", "mysql:host=" . QUESTIONNAIRE_DB_2019_HOST . ";port=" . QUESTIONNAIRE_DB_2019_PORT . ";dbname=" . QUESTIONNAIRE_DB_2019_NAME . ";charset=utf8" );
define( "QUESTIONNAIRE_DB_2019_USERNAME", $config['databaseConfig']['questionnaire2019']['username'] );
define( "QUESTIONNAIRE_DB_2019_PASSWORD", $config['databaseConfig']['questionnaire2019']['password'] );
define("FRENCH_LANGUAGE","1");
define("ENGLISH_LANGUAGE","2");

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
define( "WRM_DB_NAME_FED", $config['databaseConfig']['wrm']['nameFED'] );
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
define( "FRONTEND_ABS_PATH", str_replace("/", DIRECTORY_SEPARATOR, $config['pathConfig']['abs_path'] ));
define( "FRONTEND_REL_URL", str_replace("/", DIRECTORY_SEPARATOR, $config['pathConfig']['relative_url'] ));
define( "BACKEND_ABS_PATH", FRONTEND_ABS_PATH . "publisher/" );
define( "BACKEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", BACKEND_ABS_PATH) );
define( "FRONTEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", FRONTEND_ABS_PATH) );
define( "UPLOAD_ABS_PATH", FRONTEND_ABS_PATH . "uploads/" );
define( "UPLOAD_REL_PATH", FRONTEND_REL_URL . "uploads/" );

//Definition of all questionnaires table from the questionnaire DB
define("ANSWER_CHECK_BOX_TABLE","answerCheckbox");
define("ANSWER_QUESTIONNAIRE_TABLE","answerQuestionnaire");
define("ANSWER_RADIO_BUTTON_TABLE","answerRadioButton");
define("ANSWER_SECTION_TABLE","answerSection");
define("ANSWER_SLIDER_TABLE","answerSlider");
define("ANSWER_TABLE","answer");
define("ANSWER_TEXT_BOX_TABLE","answerTextBox");
define("CHECK_BOX_OPTION_TABLE","checkboxOption");
define("CHECK_BOX_TABLE","checkbox");
define("DATE_TABLE","date");
define("DEFINITION_TABLE","definitionTable");
define("DICTIONARY_TABLE","dictionary");
define("LABEL_TABLE","label");
define("LABEL_OPTION_TABLE","labelOption");
define("LANGUAGE_TABLE","language");
define("LEGACY_TYPE_TABLE","legacyType");
define("LEGACY_STATUS_TABLE","legacyStatus");
define("LIBRARY_TABLE","library");
define("LIBRARY_QUESTION_TABLE","libraryQuestion");
define("PATIENT_TABLE","patient");
define("QUESTIONNAIRE_TABLE","questionnaire");
define("QUESTION_TABLE","question");
define("QUESTION_SECTION_TABLE","questionSection");
define("RADIO_BUTTON_TABLE","radioButton");
define("RADIO_BUTTON_OPTION_TABLE","radioButtonOption");
define("SLIDER_TABLE","slider");
define("SECTION_TABLE","section");
define("TEXT_BOX_TABLE","textBox");
define("TIME_TABLE","time");
define("TRIGGER_WORD_TABLE","triggerWord");
define("TYPE_TABLE","type");
define("TYPE_TEMPLATE_TABLE","typeTemplate");
define("TYPE_TEMPLATE_CHECKBOX_TABLE","typeTemplateCheckbox");
define("TYPE_TEMPLATE_CHECKBOX_OPTION_TABLE","typeTemplateCheckboxOption");
define("TYPE_TEMPLATE_DATE","typeTemplateDate");
define("TYPE_TEMPLATE_LABEL_TABLE","typeTemplateLabel");
define("TYPE_TEMPLATE_LABEL_OPTION_TABLE","typeTemplateLabelOption");
define("TYPE_TEMPLATE_RADIO_BUTTON_TABLE","typeTemplateRadioButton");
define("TYPE_TEMPLATE_RADIO_BUTTON_OPTION_TABLE","typeTemplateRadioButtonOption");
define("TYPE_TEMPLATE_DATE_TABLE","typeTemplateDate");
define("TYPE_TEMPLATE_SLIDER_TABLE","typeTemplateSlider");
define("TYPE_TEMPLATE_TEXTBOX_TABLE","typeTemplateTextBox");
define("TYPE_TEMPLATE_TIME_TABLE","typeTemplateTime");
define("TYPE_TEMPLATE_TRIGGER_WORD","typeTemplateTriggerWord");

// Include the classes
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
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire". DIRECTORY_SEPARATOR . "Questionnaire.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire". DIRECTORY_SEPARATOR . "Question.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire". DIRECTORY_SEPARATOR . "QuestionGroup.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire". DIRECTORY_SEPARATOR . "Tag.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire". DIRECTORY_SEPARATOR . "Category.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire". DIRECTORY_SEPARATOR . "QuestionType.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire". DIRECTORY_SEPARATOR . "Library.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "LegacyQuestionnaire.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Diagnosis.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Application.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Encrypt.php");
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "HelpSetup.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseAccess.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseQuestionnaire.php" );
include_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseOpal.php" );
//include_once( FRONTEND_ABS_PATH . "php/classes/Upload.php");

// Push Notification FCM and APN credientials.
define( "API_KEY" , $config['pushNotificationConfig']['android']['apiKey'] );
define( "CERTIFICATE_PASSWORD" , $config['pushNotificationConfig']['apple']['certificate']['password'] );
define( "CERTIFICATE_FILE" , BACKEND_ABS_PATH . 'php' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . $config['pushNotificationConfig']['apple']['certificate']['filename'] );

?>
