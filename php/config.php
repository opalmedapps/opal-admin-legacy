<?php

require_once __DIR__."/../vendor/autoload.php";
use Dotenv\Dotenv;


/** @psalm-immutable */
class Config
{
    private static Config $self;

    private function __construct(
        public EnvironmentConfig $environment
    ) {}

    public static function getApplicationSettings(): Config
    {
        return self::$self;
    }

    public static function __init(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        // don't fail if the .env file is not there
        $dotenv->safeload();

        // Ensure that the following environment variables are set
        // Opal Database Settings
        $dotenv->required('OPAL_DB_HOST')->notEmpty();
        $dotenv->required('OPAL_DB_PORT')->notEmpty();
        $dotenv->required('OPAL_DB_NAME')->notEmpty();
        $dotenv->required('OPAL_DB_USER')->notEmpty();
        $dotenv->required('OPAL_DB_PASSWORD')->notEmpty();
        // Questionnaire Database Settings
        $dotenv->required('QUESTIONNAIRE_DB_HOST')->notEmpty();
        $dotenv->required('QUESTIONNAIRE_DB_PORT')->notEmpty();
        $dotenv->required('QUESTIONNAIRE_DB_NAME')->notEmpty();
        $dotenv->required('QUESTIONNAIRE_DB_USER')->notEmpty();
        $dotenv->required('QUESTIONNAIRE_DB_PASSWORD')->notEmpty();
        $dotenv->required('QUESTIONNAIRE_DB_ENABLED')->notEmpty();
        // Aria Database Settings
        $dotenv->required('ARIA_DB_HOST')->notEmpty();
        $dotenv->required('ARIA_DB_PORT')->notEmpty();
        $dotenv->required('ARIA_DB_NAME')->notEmpty();
        $dotenv->required('ARIA_DB_USER')->notEmpty();
        $dotenv->required('ARIA_DB_PASSWORD')->notEmpty();
        $dotenv->required('ARIA_DB_ENABLED')->notEmpty();
        // WRM Database Settings
        $dotenv->required('WRM_DB_HOST')->notEmpty();
        $dotenv->required('WRM_DB_PORT')->notEmpty();
        $dotenv->required('WRM_DB_NAME')->notEmpty();
        $dotenv->required('WRM_DB_USER')->notEmpty();
        $dotenv->required('WRM_DB_PASSWORD')->notEmpty();
        $dotenv->required('WRM_DB_ENABLED')->notEmpty();
        $dotenv->required('FEDERATED_WRM_DB_NAME')->notEmpty();
        // MOSAIQ Database Settings
        $dotenv->required('MOSAIQ_DB_HOST')->notEmpty();
        $dotenv->required('MOSAIQ_DB_PORT')->notEmpty();
        $dotenv->required('MOSAIQ_DB_USER')->notEmpty();
        $dotenv->required('MOSAIQ_DB_PASSWORD')->notEmpty();
        $dotenv->required('MOSAIQ_DB_ENABLED')->notEmpty();
        // New OpalAdmin Settings
        $dotenv->required('NEW_OPALADMIN_HOST')->notEmpty();
        $dotenv->required('NEW_OPALADMIN_TOKEN')->notEmpty();
        // SSL configurations
        $dotenv->required('USE_SSL')->notEmpty();
        $dotenv->required('SSL_CA')->notEmpty();
        // Puhs notification configurations
        $dotenv->required('PUSH_NOTIFICATION_URL')->notEmpty();
        $dotenv->required('PUSH_NOTIFICATION_ANDROID_API_KEY')->notEmpty();
        $dotenv->required('PUSH_NOTIFICATION_ANDROID_URL')->notEmpty();
        $dotenv->required('APPLE_CERT_PASSWORD')->notEmpty();
        $dotenv->required('APPLE_CERT_FILENAME')->notEmpty();
        $dotenv->required('APPLE_CERT_KEY')->notEmpty();
        $dotenv->required('APPLE_URL')->notEmpty();
        $dotenv->required('APPLE_TOPIC')->notEmpty();
        // Firebase settings
        $dotenv->required('FIREBASE_DATABASE_URL')->notEmpty();
        $dotenv->required('FIREBASE_ADMIN_KEY_PATH')->notEmpty();
        // Path configurations
        $dotenv->required('ABS_PATH')->notEmpty();
        $dotenv->required('RELATIVE_URL')->notEmpty();
        $dotenv->required('SHARED_DRIVE_PATH')->notEmpty();
        $dotenv->required('REGISTRATION_URL')->notEmpty();
        // Path configurations for clinical document
        $dotenv->required('ARIA_DOCUMENT_PATH')->notEmpty();
        $dotenv->required('MOSAIQ_DOCUMENT_PATH')->notEmpty();
        $dotenv->required('OFFICE_DOCUMENT_PATH')->notEmpty();

        $_ENV = self::_parseData($_ENV);

        // initialize required configs
        $environment = new EnvironmentConfig(
            opalDbHost: $_ENV["OPAL_DB_HOST"],
            opalDbPort: $_ENV["OPAL_DB_PORT"],
            opalDbName: $_ENV["OPAL_DB_NAME"],
            opalDbUser: $_ENV["OPAL_DB_USER"],
            opalDbPassword: $_ENV["OPAL_DB_PASSWORD"],
            questionnaireDbHost: $_ENV['QUESTIONNAIRE_DB_HOST'],
            questionnaireDbPort: $_ENV['QUESTIONNAIRE_DB_PORT'],
            questionnaireDbName: $_ENV['QUESTIONNAIRE_DB_NAME'],
            questionnaireDbUser: $_ENV['QUESTIONNAIRE_DB_USER'],
            questionnaireDbPassword: $_ENV['QUESTIONNAIRE_DB_PASSWORD'],
            questionnaireDbEnabled: $_ENV['QUESTIONNAIRE_DB_ENABLED'],
            ariaDbHost: $_ENV['ARIA_DB_HOST'],
            ariaDbPort: $_ENV['ARIA_DB_PORT'],
            ariaDbName: $_ENV['ARIA_DB_NAME'],
            ariaDbUser: $_ENV['ARIA_DB_USER'],
            ariaDbPassword: $_ENV['ARIA_DB_PASSWORD'],
            ariaDbEnabled: $_ENV['ARIA_DB_ENABLED'],
            wrmDbHost: $_ENV['WRM_DB_HOST'],
            wrmDbName: $_ENV['WRM_DB_NAME'],
            wrmDbPort: $_ENV['WRM_DB_PORT'],
            wrmDbUser: $_ENV['WRM_DB_USER'],
            wrmDbPassword: $_ENV['WRM_DB_PASSWORD'],
            wrmDbEnabled: $_ENV['WRM_DB_ENABLED'],
            wrmFedDbName: $_ENV['FEDERATED_WRM_DB_NAME'],
            mosaiqDbHost: $_ENV['MOSAIQ_DB_HOST'],
            mosaiqDbPort: $_ENV['MOSAIQ_DB_PORT'],
            mosaiqDbUser: $_ENV['MOSAIQ_DB_USER'],
            mosaiqDbPassword: $_ENV['MOSAIQ_DB_PASSWORD'],
            mosaiqDbEnabled: $_ENV['MOSAIQ_DB_ENABLED'],
            newOpaladminHost: $_ENV['NEW_OPALADMIN_HOST'],
            newOpaladminToken: $_ENV['NEW_OPALADMIN_TOKEN'],
            useSsl: $_ENV['USE_SSL'],
            sslCa: $_ENV['SSL_CA'],
            pushNotificationUrl: $_ENV['PUSH_NOTIFICATION_URL'],
            pushNotificationAndroidApiKey: $_ENV['PUSH_NOTIFICATION_ANDROID_API_KEY'],
            pushNotificationAndroidUrl: $_ENV['PUSH_NOTIFICATION_ANDROID_URL'],
            appleCertPassword: $_ENV['APPLE_CERT_PASSWORD'],
            appleCertFilename: $_ENV['APPLE_CERT_FILENAME'],
            appleCertKey: $_ENV['APPLE_CERT_KEY'],
            appleTopic: $_ENV['APPLE_TOPIC'],
            appleUrl: $_ENV['APPLE_URL'],
            firebaseDatabaseUrl: $_ENV['FIREBASE_DATABASE_URL'],
            firebaseAdminKeyPath: $_ENV['FIREBASE_ADMIN_KEY_PATH'],
            absPath: $_ENV['ABS_PATH'],
            relativeUrl: $_ENV['RELATIVE_URL'],
            sharedDrivePath: $_ENV['SHARED_DRIVE_PATH'],
            registrationUrl: $_ENV['REGISTRATION_URL'],
            ariaDocumentPath: $_ENV['ARIA_DOCUMENT_PATH'],
            mosaiqDocumentPath: $_ENV['MOSAIQ_DOCUMENT_PATH'],
            officeDocumentPath: $_ENV['OFFICE_DOCUMENT_PATH']
        );

        self::$self = new self(
            environment: $environment,
        );
    }

    /**
     * Function to convert all empty strings in an assoc array into nulls
     * @param array<string|string[]> $arr
     * @return mixed[]
     */
    private static function _parseData(array $arr): array
    {
        foreach($arr as &$val)
        {
            $val = is_array($val) ? self::_parseData($val) : $val;
            $val = ($val !== "") ? $val : null;
        }

        return $arr;
    }
}

/** @psalm-immutable */
class EnvironmentConfig
{
    public function __construct(
        public string $opalDbHost,
        public string $opalDbPort,
        public string $opalDbName,
        public string $opalDbUser,
        public string $opalDbPassword,
        public string $questionnaireDbHost,
        public string $questionnaireDbPort,
        public string $questionnaireDbName,
        public string $questionnaireDbUser,
        public string $questionnaireDbPassword,
        public string $questionnaireDbEnabled,
        public string $ariaDbHost,
        public string $ariaDbPort,
        public string $ariaDbName,
        public string $ariaDbUser,
        public string $ariaDbPassword,
        public string $ariaDbEnabled,
        public string $wrmDbHost,
        public string $wrmDbName,
        public string $wrmDbPort,
        public string $wrmDbUser,
        public string $wrmDbPassword,
        public string $wrmDbEnabled,
        public string $wrmFedDbName,
        public string $mosaiqDbHost,
        public string $mosaiqDbPort,
        public string $mosaiqDbUser,
        public string $mosaiqDbPassword,
        public string $mosaiqDbEnabled,
        public string $newOpaladminHost,
        public string $newOpaladminToken,
        public string $useSsl,
        public string $sslCa,
        public string $pushNotificationUrl,
        public string $pushNotificationAndroidApiKey,
        public string $pushNotificationAndroidUrl,
        public string $appleCertPassword,
        public string $appleCertFilename,
        public string $appleCertKey,
        public string $appleTopic,
        public string $appleUrl,
        public string $firebaseDatabaseUrl,
        public string $firebaseAdminKeyPath,
        public string $absPath,
        public string $relativeUrl,
        public string $sharedDrivePath,
        public string $registrationUrl,
        public string $ariaDocumentPath,
        public string $mosaiqDocumentPath,
        public string $officeDocumentPath
    ) {}
}

/*
* PHP global settings:
*/
session_start();
// initialize environment variables from .env file
Config::__init();

// Set the time ze for the Eastern Time Zone (ET)
date_default_timezone_set("America/Toronto");

// Turn on all errors except for notices
error_reporting(E_ALL & ~E_NOTICE ^ E_WARNING);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
ini_set("error_log", __DIR__."/../php-error.log");

const LOCALHOST_ADDRESS = array('127.0.0.1','localhost','::1');

const DEFAULT_CRON_OAUSERID = 23;
const DEFAULT_CRON_USERNAME = "cronjob";
const DEFAULT_CRON_ROLE = 1;
const UNDEFINED_SMS_APPOINTMENT_CODE = "UNDEFINED";

const CHECKED_IN = 1;
const NOT_CHECKED_IN = 0;

const LIMIT_DAYS_AUDIT_SYSTEM_BACKUP = 5;

define("OPAL_CHECKIN_CALL", "https://" . $_SERVER['HTTP_HOST'] . "/opalAdmin/publisher/php/OpalCheckIn.php");

// Define SSL setting for database connection strings and path to cert file
define ("USE_SSL", Config::getApplicationSettings()->environment->useSsl);
define ("SSL_CA", Config::getApplicationSettings()->environment->sslCa);

// DEFINE MOSAIQ SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MicrosoftSQL (MSSQL) setup.
define( "MOSAIQ_DB_HOST", Config::getApplicationSettings()->environment->mosaiqDbHost);
define( "MOSAIQ_DB_PORT", Config::getApplicationSettings()->environment->mosaiqDbPort);
define( "MOSAIQ_DB_DSN", "dblib:host=" . MOSAIQ_DB_HOST . ":" . MOSAIQ_DB_PORT . "\\database" . ";charset=utf8" );
define( "MOSAIQ_DB_USERNAME", Config::getApplicationSettings()->environment->mosaiqDbUser);
define( "MOSAIQ_DB_PASSWORD", Config::getApplicationSettings()->environment->mosaiqDbPassword);

// Environment-specific variables
define( "FRONTEND_ABS_PATH", str_replace("/", DIRECTORY_SEPARATOR, Config::getApplicationSettings()->environment->absPath ));
define( "FRONTEND_REL_URL", str_replace("/", DIRECTORY_SEPARATOR, Config::getApplicationSettings()->environment->relativeUrl));
define( "BACKEND_ABS_PATH", FRONTEND_ABS_PATH . "publisher/" );
define( "BACKEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", BACKEND_ABS_PATH) );
define( "FRONTEND_ABS_PATH_REGEX", "/" . str_replace("/", "\\/", FRONTEND_ABS_PATH) );
define( "UPLOAD_ABS_PATH", FRONTEND_ABS_PATH . "uploads/" );
define( "UPLOAD_REL_PATH", FRONTEND_REL_URL . "uploads/" );
define( "ADMIN_REGISTRATION_URL", Config::getApplicationSettings()->environment->registrationUrl);
define( "CLINICAL_DOC_PATH", Config::getApplicationSettings()->environment->sharedDrivePath . "clinical/documents/");

// Define Firebase variables
define( "FIREBASE_DATABASEURL", Config::getApplicationSettings()->environment->firebaseDatabaseUrl);
define( "FIREBASE_SERVICEACCOUNT", Config::getApplicationSettings()->environment->firebaseAdminKeyPath);

define("ALIAS_TYPE_APPOINTMENT_TEXT", 'Appointment');
define("ALIAS_TYPE_DOCUMENT_TEXT", 'Document');
define("ALIAS_TYPE_TASK_TEXT", 'Task');

define("ALIAS_TYPE_TASK", 1);
define("ALIAS_TYPE_APPOINTMENT", 2);
define("ALIAS_TYPE_DOCUMENT", 3);

// Push Notification FCM and APN credientials.
define( "API_KEY" , Config::getApplicationSettings()->environment->pushNotificationAndroidApiKey);
define( "ANDROID_URL" , Config::getApplicationSettings()->environment->pushNotificationAndroidUrl);
define( "CERTIFICATE_PASSWORD" , Config::getApplicationSettings()->environment->appleCertPassword);
define( "CERTIFICATE_FILE" , BACKEND_ABS_PATH . 'php' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . Config::getApplicationSettings()->environment->appleCertFilename);
define( "APNS_TOPIC" , Config::getApplicationSettings()->environment->appleTopic);
define( "CERTIFICATE_KEY" , BACKEND_ABS_PATH . 'php' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR . Config::getApplicationSettings()->environment->appleCertKey);
define( "IOS_URL" , Config::getApplicationSettings()->environment->appleUrl);

const RESOURCE_LEVEL_READY = 1;
const RESOURCE_LEVEL_IN_PROCESS = 2;
const APPOINTMENT_LEVEL_READY = 1;
const APPOINTMENT_LEVEL_IN_PROCESS = 2;

const DEFAULT_API_CONFIG = array(
    CURLOPT_COOKIESESSION=>true,
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_FOLLOWLOCATION=>true,
    CURLOPT_POST=>true,
    CURLOPT_SSL_VERIFYPEER=>false,
    CURLOPT_HEADER=>true,
);

const PUSH_NOTIFICATION_CONFIG = array(
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_FOLLOWLOCATION=>true,
    CURLOPT_HEADER=>true
);

const APPLE_PUSH_NOTIFICATION_CONFIG = array(
    CURLOPT_URL=> IOS_URL . "%%REGISTRATION_ID_HERE%%",
    CURLOPT_HTTP_VERSION=>3,
    CURLOPT_HTTPHEADER=>["apns-topic: ".APNS_TOPIC],
    CURLOPT_SSLCERT=>CERTIFICATE_FILE,
    CURLOPT_SSLKEY=>CERTIFICATE_KEY,
    CURLOPT_SSLKEYPASSWD=>CERTIFICATE_PASSWORD,
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_TIMEOUT=>5,
    CURLOPT_CONNECTTIMEOUT=>5,
);

define("APPLE_PUSH_NOTIFICATION_POSTFIELDS_CONFIG", json_encode(array(
    'aps' => array(
        'alert' => array(
            'title' => '%%TITLE_HERE%%',
            'body' => '%%BODY_HERE%%',
        ),
        'sound' => 'default'
    ))));

define("ANDROID_PUSH_NOTIFICATION_POSTFIELDS_CONFIG", json_encode(array(
    'registration_ids' => array("%%REGISTRATION_ID_HERE%%"),
    'data' => array(
        'notId' => date("His"),
        'title' => "%%TITLE_HERE%%",
        'body' => "%%BODY_HERE%%",
        'channelId' => 'opal',
        'payload' => array(
            'aps' => array(
                'category' => 'opal'
            )
        )
    )
)));

const ANDROID_PUSH_NOTIFICATION_CONFIG = array(
    CURLOPT_URL=>ANDROID_URL,
    CURLOPT_POST=>true,
    CURLOPT_HTTPHEADER=>array(
        'Authorization: key=' . API_KEY,
        'Content-Type: application/json'
    ),
    CURLOPT_RETURNTRANSFER=>true,
    CURLOPT_SSL_VERIFYPEER=>false,
    CURLOPT_TIMEOUT=>5,
    CURLOPT_CONNECTTIMEOUT=>5,
);

const PUSH_NOTIFICATION_RESTRICTED = 1;
const PUSH_NOTIFICATION_NON_RESTRICTED = 0;
const PUSH_NOTIFICATION_DEFAULT_STATE = PUSH_NOTIFICATION_RESTRICTED;
const PUSH_NOTIFICATION_DEFAULT_START_HOUR = "08:00:00";
const PUSH_NOTIFICATION_DEFAULT_END_HOUR = "20:00:00";
const APPLE_PHONE_DEVICE = 0;
const ANDROID_PHONE_DEVICE = 1;
const SUPPORTED_PHONE_DEVICES = array(APPLE_PHONE_DEVICE, ANDROID_PHONE_DEVICE);
const MAX_PUSH_NOTIFICATION_PER_STATUS_TO_SEND = 100;
const PUSH_NOTIFICATION_NO_STATUS = 0;
const TIMEOUT_EXECUTION_TIME_IN_SECONDS = 25;

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
define("MODULE_RESOURCE", 21);
define("MODULE_SMS", 22);
define("MODULE_PATIENT_ADMINISTRATION", 23);
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
const USER_ACCESS_DENIED = "0";

define("PURPOSE_CLINICAL", 1);
define("PURPOSE_RESEARCH", 2);
define("PURPOSE_CONSENT", 4);
define("RESPONDENT_PATIENT", 1);

define("TRIGGER_EVENT_PUBLISH", 1);

define("OPAL_QUESTIONNAIRE_COMPLETED_FLAG",1);
define("OPAL_ANSWER_QUESTIONNAIRE_COMPLETED_FLAG",2);

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

// Definition of patient consent status for studies
const CONSENT_STATUS_INVITED = 1;
const CONSENT_STATUS_OPAL_CONSENTED = 2;
const CONSENT_STATUS_OTHER_CONSENTED = 3;
const CONSENT_STATUS_DECLINED = 4;

// Define regular expression pattern constant
const REGEX_CAPITAL_LETTER = '/[A-Z]/';
const REGEX_LOWWER_CASE_LETTER = '/[a-z]/';
const REGEX_SPECIAL_CHARACTER = '/\W|_{1}/';
const REGEX_NUMBER = '/[0-9]/';
const REGEX_MRN = '/^[0-9]*$/i';

// Define patient information type constant array
const PATIENT_LANGUAGE_ARRAY = array("EN", "FR");
const PATIENT_SEX_ARRAY = array("Male", "Female", "Unknown", "Other");

const QR_CODE_NAME_PATH = FRONTEND_ABS_PATH.'images' . DIRECTORY_SEPARATOR . 'hospital-maps' . DIRECTORY_SEPARATOR . 'qrCodes' . DIRECTORY_SEPARATOR .'%%FILENAME%%.png';

require_once FRONTEND_ABS_PATH . 'php'. DIRECTORY_SEPARATOR. 'lib'.DIRECTORY_SEPARATOR.'phpqrcode'.DIRECTORY_SEPARATOR.'qrlib.php';

require_once FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."general-sql.php";
require_once FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."questionnaire-sql.php";
require_once FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."opal-sql.php";
require_once FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."questionnaire-sql-queries.php";
require_once FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."opal-sql-queries.php";
require_once FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."aria-sql.php";
require_once FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."orms-sql.php";

// Include composer dependency
require_once( FRONTEND_ABS_PATH . "vendor". DIRECTORY_SEPARATOR . "autoload.php");

// Include the classes
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "OpalProject.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Module.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "User.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Alias.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Audit.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Post.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "CronJob.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Resource.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "EduMaterial.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "HospitalMap.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Notification.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Cron.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "CrontabManager.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Patient.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TestResult.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Install.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Email.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "QuestionnaireModule.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Questionnaire.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Publication.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceModule.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceAlias.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceAppointment.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceTask.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceDocument.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceDiagnosis.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "MasterSourceTestResult.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "CustomCode.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Study.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Trigger.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Alert.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Role.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Question.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TemplateQuestion.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Library.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Diagnosis.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Application.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "HelpSetup.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseAccess.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseQuestionnaire.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseOpal.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseAria.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseOrms.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "DatabaseDisconnected.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Trigger.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Appointment.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "ApiCall.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "AndroidApiCall.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "AppleApiCall.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "Sms.php" );
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TriggerDocument.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TriggerDoctor.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "TriggerStaff.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "FirebaseOpal.php");
require_once( FRONTEND_ABS_PATH . "php". DIRECTORY_SEPARATOR . "classes". DIRECTORY_SEPARATOR . "PatientAdministration.php");

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

/*
 * List of HTTP status codes
 * */
define("HTTP_STATUS_SUCCESS",200);
define("HTTP_STATUS_INTERNAL_SERVER_ERROR",500);
define("HTTP_STATUS_BAD_GATEWAY",502);
define("HTTP_STATUS_BAD_REQUEST_ERROR",400);
define("HTTP_STATUS_NOT_AUTHENTICATED_ERROR",401);
define("HTTP_STATUS_FORBIDDEN_ERROR",403);
define("HTTP_STATUS_NOT_FOUND",404);
define("HTTP_STATUS_SESSION_TIMEOUT_ERROR",419);
define("HTTP_STATUS_UNPROCESSABLE_ENTITY_ERROR",422);
define("HTTP_STATUS_LOGIN_TIMEOUT_ERROR",440);
define("HTTP_STATUS_HTTP_TO_HTTPS_ERROR",497);

define("ABVR_FRENCH_LANGUAGE", "FR");
define("ABVR_ENGLISH_LANGUAGE", "EN");
// all language abbreviations in opal admin
define("OPAL_ADMIN_LANGUAGES",array(ABVR_FRENCH_LANGUAGE, ABVR_ENGLISH_LANGUAGE));


/*
 * PHP Sessions config
 * */
define("PHP_SESSION_TIMEOUT", 7200);

/*
 * Appointment Status
 * */
const APPOINTMENT_STATUS_CODE_OPEN = "Open";
const APPOINTMENT_STATUS_CODE_PROGRESS = "In Progress";
const APPOINTMENT_STATUS_CODE_CANCELLED = "Cancelled";
const APPOINTMENT_STATUS_CODE_COMPLETED = "Completed";
const APPOINTMENT_STATUS_CODE_DELETED = "Deleted";
const APPOINTMENT_STATE_CODE_ACTIVE = "Active";
const APPOINTMENT_STATE_CODE_DELETED = "Deleted";

