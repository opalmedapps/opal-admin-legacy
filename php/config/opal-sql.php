<?php
/**
 * User: Dominic Bourdua
 * Date: 4/11/2019
 * Time: 2:37 PM
 */

// DEFINE OPAL SERVER/DATABASE CREDENTIALS HERE
// NOTE: This works for a MySQL setup.
define( "OPAL_DB_HOST", $config['databaseConfig']['opal']['host'] );
define( "OPAL_DB_PORT", $config['databaseConfig']['opal']['port'] );
define( "OPAL_DB_NAME", $config['databaseConfig']['opal']['name'] );
define( "OPAL_DB_DSN", "mysql:host=" . OPAL_DB_HOST . ";port=" . OPAL_DB_PORT . ";dbname=" . OPAL_DB_NAME . ";charset=utf8" );
define( "OPAL_DB_USERNAME", $config['databaseConfig']['opal']['username'] );
define( "OPAL_DB_PASSWORD", $config['databaseConfig']['opal']['password'] );

// DEFINE OPAL SERVER/DATABASE CREDENTIALS FOR GUEST ACCOUNT HERE
// NOTE: This works for a MySQL setup.
define( "OPAL_DB_HOST_GUEST", $config['databaseConfig']['opalGuest']['host'] );
define( "OPAL_DB_PORT_GUEST", $config['databaseConfig']['opalGuest']['port'] );
define( "OPAL_DB_NAME_GUEST", $config['databaseConfig']['opalGuest']['name'] );
define( "OPAL_DB_DSN_GUEST", "mysql:host=" . OPAL_DB_HOST_GUEST . ";port=" . OPAL_DB_PORT_GUEST . ";dbname=" . OPAL_DB_NAME_GUEST . ";charset=utf8" );
define( "OPAL_DB_USERNAME_GUEST", $config['databaseConfig']['opalGuest']['username'] );
define( "OPAL_DB_PASSWORD_GUEST", $config['databaseConfig']['opalGuest']['password'] );

//Definition of all the tables from the opalDB database
define("OPAL_OAUSER_TABLE","OAUser");
define("OPAL_OAUSER_ROLE_TABLE","OAUserRole");
define("OPAL_OAUSER_ACTIVITY_LOG_TABLE","OAActivityLog");
define("OPAL_ALIAS_EXPRESSION_MH_TABLE","AliasExpressionMH");
define("OPAL_DIAGNOSIS_TRANSLATION_MH_TABLE","DiagnosisTranslationMH");
define("OPAL_DIAGNOSIS_CODE_MH_TABLE","DiagnosisCodeMH");
define("OPAL_QUESTIONNAIRE_CONTROL_TABLE","QuestionnaireControl");
define("OPAL_QUESTIONNAIRE_MH_TABLE","QuestionnaireMH");
define("OPAL_EMAIL_CONTROL_MH_TABLE","EmailControlMH");
define("OPAL_HOSPITAL_MAP_MH_TABLE","HospitalMapMH");
define("OPAL_POST_CONTROL_MH_TABLE","PostControlMH");
define("OPAL_NOTIFICATION_CONTROL_MH_TABLE","NotificationControlMH");
define("OPAL_QUESTIONNAIRE_CONTROL_MH_TABLE","QuestionnaireControlMH");
define("OPAL_TEST_RESULT_CONTROL_MH_TABLE","TestResultControlMH");
define("OPAL_TEST_RESULT_EXP_MH_TABLE","TestResultExpressionMH");
define("OPAL_FILTERS_TABLE","Filters");
define("OPAL_FILTERS_MH_TABLE","FiltersMH");
define("OPAL_FREQUENCY_EVENTS_TABLE","FrequencyEvents");
define("OPAL_MODULE_TABLE","module");
define("OPAL_AUDIT_TABLE","audit");
define("OPAL_CATEGORY_MODULE_TABLE","categoryModule");
define("OPAL_MODULE_PUBLICATION_SETTING_TABLE","modulePublicationSetting");
define("OPAL_PUBLICATION_SETTING_TABLE","publicationSetting");
define("OPAL_POST_TABLE","PostControl");
define("OPAL_TX_TEAM_MESSAGE_TABLE","TxTeamMessage");
define("OPAL_ANNOUNCEMENT_TABLE","Announcement");
define("OPAL_PATIENTS_FOR_PATIENTS_TABLE","PatientsForPatients");
define("OPAL_EDUCATION_MATERIAL_TABLE","EducationalMaterialControl");
define("OPAL_EDUCATION_MATERIAL_TOC_TABLE","EducationalMaterialTOC");
define("OPAL_PHASE_IN_TREATMENT_TABLE","PhaseInTreatment");
define("OPAL_ANNOUNCEMENT_MH_TABLE","AnnouncementMH");
define("OPAL_TXT_TEAM_MSG_MH_TABLE","TxTeamMessageMH");
define("OPAL_PATIENTS_FOR_PATIENTS_MH_TABLE","PatientsForPatientsMH");
define("OPAL_EDUCATION_MATERIAL_MH_TABLE","EducationalMaterialMH");
define("OPAL_TASK_MH_TABLE","TaskMH");
define("OPAL_DOCUMENT_MH_TABLE","DocumentMH");
define("OPAL_APPOINTMENT_MH_TABLE","AppointmentMH");
define("OPAL_TEST_RESULT_MH_TABLE","TestResultMH");
define("OPAL_EMAIL_LOG_MH_TABLE","EmailLogMH");
define("OPAL_NOTIFICATION_MH_TABLE","NotificationMH");
define("OPAL_NOTIFICATION_CONTROL_TABLE","NotificationControl");
define("OPAL_NOTIFICATION_TYPES_TABLE","NotificationTypes");
define("OPAL_EMAIL_CONTROL","EmailControl");
define("OPAL_EMAIL_TYPE","EmailType");
define("OPAL_CRON_LOG_TABLE","CronLog");
define("OPAL_SETTING_TABLE","setting");
define("OPAL_MASTER_SOURCE_ALIAS_TABLE","masterSourceAlias");
define("OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE","masterSourceDiagnosis");
define("OPAL_MASTER_SOURCE_TEST_RESULT_TABLE","masterSourceTestResult");
define("OPAL_ALIAS_EXPRESSION_TABLE","AliasExpression");
define("OPAL_DOCTOR_TABLE","Doctor");
define("OPAL_RESOURCE_NAME_TABLE","ResourceName");
define("OPAL_STATUS_ALIAS_TABLE","StatusAlias");
define("OPAL_ALIAS_TABLE","Alias");
define("OPAL_DIAGNOSIS_TRANSLATION_TABLE","DiagnosisTranslation");
define("OPAL_PATIENT_TABLE","Patient");
define("OPAL_TEST_RESULT_EXPRESSION_TABLE","TestResultExpression");
define("OPAL_DIAGNOSIS_CODE_TABLE","DiagnosisCode");
define("OPAL_LOGIN_VIEW","v_login");
define("OPAL_USER_ACTIVITY_LOG_TABLE","OAActivityLog");
define("OPAL_ROLE_TABLE","Role");
define("OPAL_ALIAS_MH_TABLE","AliasMH");
define("OPAL_STUDY_TABLE","study");
define("OPAL_OA_ROLE_TABLE","oaRole");
define("OPAL_OA_ROLE_MODULE_TABLE","oaRoleModule");
define("OPAL_SOURCE_DATABASE_TABLE","SourceDatabase");
define("OPAL_HOSPITAL_MAP_TABLE","HospitalMap");
define("OPAL_ALERT_TABLE","alert");
define("OPAL_TRIGGER_TABLE","jsonTrigger");

//Definition of the primary keys of the opalDB database
define("OPAL_POST_PK","PostControlSerNum");


/*
 * Listing of all SQL queries for the Opal database
 * */
define("SQL_OPAL_SELECT_USER_INFO",
    "SELECT OAUserSerNum AS OAUserId,
    Username AS username,
    Language as language,
    oaRoleId as userRole
    FROM ".OPAL_OAUSER_TABLE."
    WHERE OAUserSerNum = :OAUserSerNum"
);

define("SQL_OPAL_LIST_QUESTIONNAIRES_FROM_QUESTIONNAIRE_CONTROL",
    "SELECT COUNT(*) AS total
    FROM ".OPAL_QUESTIONNAIRE_CONTROL_TABLE."
    WHERE QuestionnaireDBSerNum IN ( :questionnaireList )"
);

define("SQL_OPAL_GET_PUBLISHED_QUESTIONNAIRES",
    "SELECT DISTINCT
    qc.QuestionnaireControlSerNum AS serial,
    qc.QuestionnaireDBSerNum AS db_serial,
    qc.QuestionnaireName_EN AS name_EN,
    qc.QuestionnaireName_FR AS name_FR,
    qc.PublishFlag AS publish,
    0 AS changed
    FROM ".OPAL_QUESTIONNAIRE_CONTROL_TABLE." qc;"
);

define("SQL_OPAL_GET_FILTERS_BY_CONTROL_TABLE_SERNUM",
    "SELECT DISTINCT 
    FilterType AS type,
    FilterId AS id
    FROM 
    ".OPAL_FILTERS_TABLE."
    WHERE ControlTableSerNum = :ControlTableSerNum
    AND ControlTable = :ControlTable
    AND FilterType != ''
    AND FilterId != '';"
);

define("SQL_OPAL_DELETE_FILTERS",
    "DELETE FROM ".OPAL_FILTERS_TABLE."
    WHERE FilterId = :FilterId
    AND FilterType = :FilterType
    AND ControlTableSerNum = :ControlTableSerNum
    AND ControlTable = :ControlTable;"
);

define("SQL_OPAL_UPDATE_FILTERSMH",
    "UPDATE ".OPAL_FILTERS_MH_TABLE."
    SET 
    LastUpdatedBy = :LastUpdatedBy,
    SessionId = :SessionId
    WHERE FilterId = :FilterId
    AND FilterType = :FilterType
    AND ControlTableSerNum = :ControlTableSerNum
    AND ControlTable = :ControlTable
    ORDER BY DateAdded DESC 
    LIMIT 1;"
);

define("SQL_OPAL_DELETE_FREQUENCY_EVENTS_TABLE",
    "DELETE FROM ".OPAL_FREQUENCY_EVENTS_TABLE." 
    WHERE ControlTable = :ControlTable
    AND ControlTableSerNum = :ControlTableSerNum;"
);

define("SQL_OPAL_UPDATE_PUBLISHED_QUESTIONNAIRES_STATUS",
    "UPDATE ".OPAL_QUESTIONNAIRE_CONTROL_TABLE."
    SET PublishFlag = :PublishFlag, LastUpdatedBy = :LastUpdatedBy
    WHERE QuestionnaireControlSerNum = :QuestionnaireControlSerNum
    AND (PublishFlag != :PublishFlag);"
);

define("SQL_OPAL_UPDATE_PUBLICATION_STATUS_FLAG",
    "UPDATE %%TABLE_NAME%%
    SET PublishFlag = :PublishFlag, LastUpdatedBy = :LastUpdatedBy, SessionId = :SessionId
    WHERE %%ID_FIELD%% = :ID
    AND (PublishFlag != :PublishFlag);"
);

define("SQL_OPAL_GET_ALL_PUBLICATION_MODULES",
    "SELECT * FROM ".OPAL_MODULE_TABLE." m WHERE m.active = ".ACTIVE_RECORD." AND m.publication = ".ACTIVE_RECORD." ORDER BY m.order;"
);

define("SQL_OPAL_BUILD_PUBLICATION_VIEW",
    "SELECT m.sqlPublicationList, m.sqlPublicationChartLog FROM ".OPAL_MODULE_TABLE." m WHERE m.active = ".ACTIVE_RECORD." AND m.publication = ".ACTIVE_RECORD." ORDER BY m.order"
);

define("SQL_OPAL_BUILD_CUSOM_CODE_VIEW",
    "SELECT m.sqlCustomCode FROM ".OPAL_MODULE_TABLE." m WHERE m.active = ".ACTIVE_RECORD." AND m.customCode = ".ACTIVE_RECORD." ORDER BY m.order"
);

define("SQL_GET_QUERY_CHART_LOG",
    "SELECT sqlPublicationChartLog, sqlPublicationListLog FROM ".OPAL_MODULE_TABLE." m WHERE m.active = ".ACTIVE_RECORD." AND m.publication = ".ACTIVE_RECORD." AND ID = :ID"
);

define("SQL_OPAL_GET_MODULE_BY_ID", "
    SELECT * FROM ".OPAL_MODULE_TABLE." WHERE ID = :ID;
");

define("SQL_OPAL_GET_FILTERS_DETAILS", "
    SELECT DISTINCT FilterType AS type, FilterId AS id
    FROM ".OPAL_FILTERS_TABLE." WHERE ControlTableSerNum = :ControlTableSerNum AND ControlTable = :ControlTable;
");

//define("SQL_OPAL_GET_FILTERS_DETAILS", "
//    SELECT FilterSerNum, FilterType, FilterId FROM ".OPAL_FILTERS_TABLE." WHERE ControlTableSerNum = :ControlTableSerNum AND ControlTable = :ControlTable;
//");

define("SQL_OPAL_GET_PUBLICATION_SETTINGS_ID_PER_MODULE", "
    SELECT publicationSettingId FROM ".OPAL_MODULE_PUBLICATION_SETTING_TABLE." WHERE moduleId = :moduleId;
");

define("SQL_OPAL_GET_ALL_PUBLICATION_MODULES_USER",
    "SELECT m.ID, m.name_EN, m.name_FR, m.iconClass FROM ".OPAL_MODULE_TABLE." m WHERE m.active = ".ACTIVE_RECORD." AND m.publication = ".ACTIVE_RECORD." ORDER BY m.order;"
);

define("SQL_OPAL_GET_ALL_CUSTOM_CODE_MODULES_USER",
    "SELECT m.ID, m.name_EN, m.name_FR, m.iconClass, m.subModule FROM ".OPAL_MODULE_TABLE." m WHERE m.active = ".ACTIVE_RECORD." AND m.customCode = ".ACTIVE_RECORD." ORDER BY m.order;"
);

define("SQL_OPAL_GET_QUESTIONNAIRE_CONTROL_DETAILS",
    "SELECT DISTINCT
    QuestionnaireControlSerNum AS serial,
    QuestionnaireDBSerNum AS db_serial,
    QuestionnaireName_EN AS name_EN,
    QuestionnaireName_FR AS name_FR,
    Intro_EN,
    Intro_FR,
    PublishFlag AS publish
    FROM
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE."
    WHERE QuestionnaireControlSerNum = :QuestionnaireControlSerNum;"
);

define("SQL_OPAL_GET_FREQUENCY_EVENTS",
    "SELECT DISTINCT CustomFlag, MetaKey, MetaValue 
    FROM ".OPAL_FREQUENCY_EVENTS_TABLE."
    WHERE ControlTable = :ControlTable
    AND ControlTableSerNum = :ControlTableSerNum;"
);

define("SQL_OPAL_UPDATE_QUESTIONNAIRE_CONTROL",
    "UPDATE 
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE." 
    SET 
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE.".QuestionnaireName_EN = :QuestionnaireName_EN, 
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE.".QuestionnaireName_FR = :QuestionnaireName_FR,
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE.".LastUpdatedBy = :LastUpdatedBy,
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE.".SessionId = :SessionId
    WHERE 
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE.".QuestionnaireControlSerNum = :QuestionnaireControlSerNum;"
);

define("SQL_OPAL_MARK_AS_DELETED_MASTER_SOURCE", "
    UPDATE %%MASTER_SOURCE_TABLE%% SET deleted = ".DELETED_RECORD.", deletedBy = :deletedBy, updatedBy = :updatedBy
    WHERE ID = :ID AND source = -1; 
");

define("SQL_OPAL_UPDATE_POST_CONTROL",
    "UPDATE ".OPAL_POST_TABLE." SET 
    PublishDate = :PublishDate, 
    LastUpdatedBy = :LastUpdatedBy,
    SessionId = :SessionId
    WHERE 
    PostControlSerNum = :PostControlSerNum;"
);

define("SQL_OPAL_DELETE_REPEAT_END_FROM_FREQUENCY_EVENTS",
    "DELETE FROM ".OPAL_FREQUENCY_EVENTS_TABLE."
    WHERE ControlTable = :ControlTable
    AND ControlTableSerNum = :ControlTableSerNum
    AND MetaKey = 'repeat_end';"
);

define("SQL_OPAL_DELETE_OTHER_METAS_FROM_FREQUENCY_EVENTS",
    "DELETE FROM ".OPAL_FREQUENCY_EVENTS_TABLE."
    WHERE ControlTable = :ControlTable
    AND ControlTableSerNum = :ControlTableSerNum
    AND MetaKey != 'repeat_start'
    AND MetaKey != 'repeat_end';"
);

define("SQL_OPAL_GET_QUESTIONNAIRE_LIST_LOGS","
    SELECT DISTINCT
    qc.QuestionnaireName_EN AS control_name,
    lqmh.QuestionnaireControlSerNum AS control_serial,
    lqmh.QuestionnaireRevSerNum AS revision,
    lqmh.CronLogSerNum AS cron_serial,
    lqmh.PatientSerNum AS patient_serial,
    lqmh.PatientQuestionnaireDBSerNum AS pt_questionnaire_db,
    lqmh.CompletedFlag AS completed,
    lqmh.CompletionDate AS completion_date,
    lqmh.DateAdded AS date_added,
    lqmh.ModificationAction AS mod_action
    FROM ".OPAL_QUESTIONNAIRE_MH_TABLE." lqmh, ".OPAL_QUESTIONNAIRE_CONTROL_TABLE." qc
    WHERE
    lqmh.QuestionnaireControlSerNum = qc.QuestionnaireControlSerNum
    AND lqmh.CronLogSerNum IN (%%IDS%%)
");

define("SQL_OPAL_GET_POSTS", "
    SELECT DISTINCT
    PostControlSerNum AS serial,
    PostType AS type,
    PostName_EN AS name_EN,
    PostName_FR AS name_FR,
    (SELECT COUNT(*) from ".OPAL_FILTERS_TABLE." f WHERE f.ControlTableSerNum = pc.PostControlSerNum and ControlTable = '".OPAL_POST_TABLE."') AS locked
    FROM 
	".OPAL_POST_TABLE." pc
	WHERE deleted != ".DELETED_RECORD.";
");

define("SQL_OPAL_GET_POST_DETAILS", "
    SELECT DISTINCT
    PostControlSerNum AS serial,
    PostType AS type,
    PostName_EN AS name_EN,
    PostName_FR AS name_FR,
    body_EN,
    body_FR,
    (SELECT COUNT(*) from ".OPAL_FILTERS_TABLE." f WHERE f.ControlTableSerNum = pc.PostControlSerNum and ControlTable = '".OPAL_POST_TABLE."') AS locked
    FROM ".OPAL_POST_TABLE." pc
    WHERE PostControlSerNum = :PostControlSerNum;
");

define("SQL_OPAL_GET_SETTINGS", "
    SELECT * FROM ".OPAL_SETTING_TABLE."
    WHERE ID = :ID;
");

define("SQL_OPAL_UPDATE_POST",
    "UPDATE ".OPAL_POST_TABLE."
    SET LastUpdatedBy = :LastUpdatedBy,
    SessionId = :SessionId,
    PostName_EN = :PostName_EN,
    PostName_FR = :PostName_FR,
    PostType = :PostType,
    Body_EN = :Body_EN,
    Body_FR = :Body_FR
    WHERE PostControlSerNum = :PostControlSerNum
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_OPAL_UPDATE_POST_PUBLISH_DATE", "
    UPDATE ".OPAL_POST_TABLE."
    SET LastUpdatedBy = :LastUpdatedBy,
    SessionId = :SessionId,
    PublishDate = :PublishDate
    WHERE PostControlSerNum = :PostControlSerNum
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_OPAL_MARK_RECORD_AS_DELETED", "
    UPDATE %%TABLENAME%% SET deleted = ".DELETED_RECORD.", LastUpdatedBy = :LastUpdatedBy, SessionId = :SessionId
    WHERE %%PRIMARY_KEY%% = :recordId AND deleted = ".NON_DELETED_RECORD.";
");

define("SQL_OPAL_GET_PUBLICATION_SETTINGS_PER_MODULE", "
    SELECT * FROM ".OPAL_PUBLICATION_SETTING_TABLE." ps
    LEFT JOIN ".OPAL_MODULE_PUBLICATION_SETTING_TABLE." mps ON mps.publicationSettingId = ps.ID
    WHERE mps.moduleId = :moduleId;
");

define("SQL_OPAL_GET_PUBLICATION_TRIGGERS_SETTINGS_PER_MODULE", "
    SELECT * FROM ".OPAL_PUBLICATION_SETTING_TABLE." ps
    LEFT JOIN ".OPAL_MODULE_PUBLICATION_SETTING_TABLE." mps ON mps.publicationSettingId = ps.ID
    WHERE mps.moduleId = :moduleId AND isTrigger = 1;
");

define("SQL_OPAL_GET_PUBLICATION_NON_TRIGGERS_SETTINGS_PER_MODULE", "
    SELECT * FROM ".OPAL_PUBLICATION_SETTING_TABLE." ps
    LEFT JOIN ".OPAL_MODULE_PUBLICATION_SETTING_TABLE." mps ON mps.publicationSettingId = ps.ID
    WHERE mps.moduleId = :moduleId AND isTrigger = 0;
");

define("SQL_OPAL_GET_PUBLISH_DATE_TIME", "
    SELECT PublishDate FROM %%TABLE_NAME%% WHERE %%PRIMARY_KEY%% = :primaryKey;
");

define("SQL_OPAL_GET_ANNOUNCEMENT_CHART","
    SELECT DISTINCT anmh.CronLogSerNum AS cron_serial, COUNT(anmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_ANNOUNCEMENT_MH_TABLE." anmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started'
    AND cl.CronLogSerNum = anmh.CronLogSerNum AND anmh.CronLogSerNum IS NOT NULL
    AND anmh.PostControlSerNum = :PostControlSerNum GROUP BY anmh.CronLogSerNum, cl.CronDateTime
    AND cl.CronDateTime >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
    ORDER BY cl.CronDateTime ASC 
");

define("SQL_OPAL_GET_ANNOUNCEMENT_CHART_PER_IDS","
    SELECT DISTINCT pc.PostName_EN AS post_control_name, anmh.AnnouncementRevSerNum AS revision, anmh.CronLogSerNum AS cron_serial,
    anmh.PatientSerNum AS patient_serial, anmh.DateAdded AS date_added, anmh.ReadStatus AS read_status, anmh.ModificationAction AS mod_action
    FROM ".OPAL_ANNOUNCEMENT_MH_TABLE." anmh, ".OPAL_POST_TABLE." pc WHERE pc.PostControlSerNum = anmh.PostControlSerNum AND anmh.CronLogSerNum IN (%%CRON_LOG_IDS%%)
");

define("SQL_OPAL_GET_TTM_CHART_PER_IDS","
    SELECT DISTINCT pc.PostName_EN AS post_control_name, ttmmh.TxTeamMessageRevSerNum AS revision, ttmmh.CronLogSerNum AS cron_serial,
    ttmmh.PatientSerNum AS patient_serial, ttmmh.DateAdded AS date_added, ttmmh.ReadStatus AS read_status, ttmmh.ModificationAction AS mod_action
    FROM ".OPAL_TXT_TEAM_MSG_MH_TABLE." ttmmh, ".OPAL_POST_TABLE." pc WHERE pc.PostControlSerNum = ttmmh.PostControlSerNum AND ttmmh.CronLogSerNum IN (%%CRON_LOG_IDS%%)
");

define("SQL_OPAL_GET_PFP_CHART_PER_IDS","
    SELECT DISTINCT pc.PostName_EN AS post_control_name, pfpmh.PatientsForPatientsRevSerNum AS revision, pfpmh.CronLogSerNum AS cron_serial,
    pfpmh.PatientSerNum AS patient_serial, pfpmh.DateAdded AS date_added, pfpmh.ReadStatus AS read_status, pfpmh.ModificationAction AS mod_action
    FROM ".OPAL_PATIENTS_FOR_PATIENTS_MH_TABLE." pfpmh, ".OPAL_POST_TABLE." pc WHERE pc.PostControlSerNum = pfpmh.PostControlSerNum AND pfpmh.CronLogSerNum IN (%%CRON_LOG_IDS%%)
");

define("SQL_OPAL_GET_TTM_CHART","
    SELECT DISTINCT ttmmh.CronLogSerNum AS cron_serial, COUNT(ttmmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_TXT_TEAM_MSG_MH_TABLE." ttmmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started'
    AND cl.CronLogSerNum = ttmmh.CronLogSerNum AND ttmmh.CronLogSerNum IS NOT NULL
    AND ttmmh.PostControlSerNum = :PostControlSerNum GROUP BY ttmmh.CronLogSerNum, cl.CronDateTime
    AND cl.CronDateTime >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
    ORDER BY cl.CronDateTime ASC
");

define("SQL_OPAL_GET_PFP_CHART","
    SELECT DISTINCT pfpmh.CronLogSerNum AS cron_serial, COUNT(pfpmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_PATIENTS_FOR_PATIENTS_MH_TABLE." pfpmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started'
    AND cl.CronLogSerNum = pfpmh.CronLogSerNum AND pfpmh.CronLogSerNum IS NOT NULL
    AND pfpmh.PostControlSerNum = :PostControlSerNum GROUP BY pfpmh.CronLogSerNum, cl.CronDateTime
    AND cl.CronDateTime >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
    ORDER BY cl.CronDateTime ASC 
");

define("SQL_OPAL_GET_EDUCATIONAL_CHART","
    SELECT DISTINCT emmh.CronLogSerNum AS cron_serial, COUNT(emmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_EDUCATION_MATERIAL_MH_TABLE." emmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started'
    AND cl.CronLogSerNum = emmh.CronLogSerNum AND emmh.CronLogSerNum IS NOT NULL
    AND emmh.EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum GROUP BY emmh.CronLogSerNum, cl.CronDateTime
    AND cl.CronDateTime >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
    ORDER BY cl.CronDateTime ASC 
");

define("OPAL_UPDATE_MASTER_SOURCE", "
    UPDATE %%MASTER_TABLE%% SET code = :code, description = :description, updatedBy = :updatedBy WHERE ID = :ID;
");

define("OPAL_UPDATE_EXTERNAL_ID_MASTER_SOURCE", "
    UPDATE %%MASTER_TABLE%% SET externalId = :externalId WHERE ID = :ID;
");

define("OPAL_GET_PATIENTS_TRIGGERS","
    SELECT DISTINCT PatientId AS id, 'Patient' AS type, 0 AS added, CONCAT(CONCAT(UCASE(SUBSTRING(LastName, 1, 1)), LOWER(SUBSTRING(LastName, 2))), ', ', CONCAT(UCASE(SUBSTRING(FirstName, 1, 1)), LOWER(SUBSTRING(FirstName, 2))), ' (', PatientId, ')') AS name
    FROM ".OPAL_PATIENT_TABLE." ORDER BY PatientSerNum;
");

define("OPAL_GET_DIAGNOSIS_TRIGGERS","
    SELECT DISTINCT DiagnosisTranslationSerNum AS id, Name_EN AS name, Name_FR AS name_FR, 'Diagnosis' AS type, 0 AS 'added'
    FROM ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." WHERE Name_EN != '';
");

define("OPAL_GET_APPOINTMENTS_TRIGGERS","
    SELECT DISTINCT AliasSerNum AS id, AliasName_EN AS name, AliasName_FR AS name_FR, AliasType AS 'type', 0 AS added
    FROM ".OPAL_ALIAS_TABLE." WHERE AliasType = 'Appointment' ORDER BY AliasSerNum;
");

define("OPAL_GET_APPOINTMENT_STATUS_TRIGGERS","
    SELECT DISTINCT Name AS name, Name AS id, 'AppointmentStatus' AS type, 0 AS added FROM ".OPAL_STATUS_ALIAS_TABLE."
    UNION ALL
    SELECT 'Checked In' AS name, 1 AS id, 'CheckedInFlag' AS type, 0 AS added;
");

define("OPAL_GET_DOCTORS_TRIGGERS","
    SELECT DISTINCT max(d.DoctorAriaSer) AS id, trim(d.LastName) AS LastName, trim(d.FirstName) AS FirstName, 'Doctor' AS type, 0 AS added
    FROM ".OPAL_DOCTOR_TABLE." d WHERE d.ResourceSerNum > 0 GROUP BY d.LastName ORDER BY d.LastName, d.FirstName;
");

define("OPAL_GET_TREATMENT_MACHINES_TRIGGERS","
    SELECT DISTINCT ResourceAriaSer AS id, ResourceName AS name, 'Machine' AS 'type', 0 AS 'added' FROM Resource
    WHERE ".OPAL_RESOURCE_NAME_TABLE." LIKE 'STX%' OR  ResourceName LIKE 'TB%' ORDER BY ResourceName;
");

define("OPAL_COUNT_CODE_MASTER_SOURCE","
    SELECT SUM(locked) AS locked FROM (
    SELECT COUNT(*) AS locked FROM " . OPAL_MASTER_SOURCE_ALIAS_TABLE . " msa
    WHERE (msa.code LIKE :code AND msa.description LIKE :description)
    UNION ALL
    SELECT COUNT(*) AS locked FROM " . OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE . " msd
    WHERE (msd.code LIKE :code AND msd.description LIKE :description)
    ) x
");

define("SQL_OPAL_VALIDATE_OAUSER_LOGIN","
    SELECT * FROM ".OPAL_LOGIN_VIEW." WHERE username = :username AND password = :password AND type = " . HUMAN_USER . ";
");

define("SQL_OPAL_VALIDATE_SYSTEM_OAUSER_LOGIN","
    SELECT * FROM ".OPAL_LOGIN_VIEW." WHERE username = :username AND password = :password AND type = " . SYSTEM_USER . ";
");

define("SQL_OPAL_VALIDATE_OAUSER_LOGIN_AD","
    SELECT * FROM ".OPAL_LOGIN_VIEW." WHERE username = :username AND type = " . HUMAN_USER . ";
");

define("OPAL_UPDATE_PASSWORD","
    UPDATE ".OPAL_OAUSER_TABLE." SET Password = :Password WHERE OAUserSerNum = :OAUserSerNum AND Password != :Password;
");

define("OPAL_UPDATE_USER_INFO","
    UPDATE ".OPAL_OAUSER_TABLE." SET Language = :Language, oaRoleId = :oaRoleId WHERE OAUserSerNum = :OAUserSerNum AND (Language != :Language OR oaRoleId != :oaRoleId);
");

define("OPAL_UPDATE_LANGUAGE","
    UPDATE ".OPAL_OAUSER_TABLE." SET Language = :Language WHERE OAUserSerNum = :OAUserSerNum
");

define("OPAL_GET_USER_DETAILS","
    SELECT ou.OAUserSerNum AS serial, ou.Username AS username, ou.oaRoleId, r.name_EN, r.name_FR, type, ou.Language AS language
    FROM ".OPAL_OAUSER_TABLE." ou
    LEFT JOIN ".OPAL_OA_ROLE_TABLE." r ON r.id = ou.oaRoleId
	WHERE ou.OAUserSerNum = :OAUserSerNum
");

define("OPAL_GET_ROLE_DETAILS","
    SELECT * FROM ".OPAL_ROLE_TABLE." WHERE RoleSerNum = :RoleSerNum;
");

define("OPAL_GET_USERS_LIST","
    SELECT ou.OAUserSerNum AS serial, ou.Username AS username, ou.type, r.name_EN, r.name_FR, ou.Language AS language
    FROM ".OPAL_OAUSER_TABLE." ou
    LEFT JOIN ".OPAL_OA_ROLE_TABLE." r ON r.id = ou.oaRoleId
	WHERE ou.deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_COUNT_USERNAME","
    SELECT COUNT(*) AS total FROM ".OPAL_OAUSER_TABLE." WHERE Username = :Username
");

define("OPAL_MARK_USER_AS_DELETED",
    "UPDATE ".OPAL_OAUSER_TABLE." SET deleted = ".DELETED_RECORD." WHERE OAUserSerNum = :recordId
    AND OAUserSerNum != :OAUserId AND deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_ROLES_LIST","
    SELECT DISTINCT RoleSerNum AS serial, RoleName AS name FROM Role ORDER BY RoleName;
");

define("OPAL_GET_USER_LOGIN_DETAILS","
    SELECT DISTINCT oaa.OAUserSerNum AS serial, oaa.DateAdded AS login, oaa2.DateAdded AS logout, oaa.SessionId AS sessionid,
    CONCAT (IF(MOD(HOUR(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)), 24) > 0,
        CONCAT(MOD(HOUR(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)), 24), 'h'), ''),
	    IF(MINUTE(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)) > 0, CONCAT(MINUTE(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)), 'm'), ''),
	    SECOND(TIMEDIFF(oaa2.DateAdded, oaa.DateAdded)), 's') AS session_duration
    FROM ".OPAL_OAUSER_TABLE." oa, ".OPAL_OAUSER_ACTIVITY_LOG_TABLE." oaa LEFT JOIN ".OPAL_OAUSER_ACTIVITY_LOG_TABLE." oaa2
    ON oaa.SessionId = oaa2.SessionId  AND oaa2.Activity = 'Logout' 
    WHERE oaa.`Activity` = 'Login' AND oa.OAUserSerNum = oaa.OAUserSerNum AND oa.OAUserSerNum = :OAUserSerNum ORDER BY oaa.DateAdded DESC;
");

define("OPAL_GET_USER_ALIAS_DETAILS","
    SELECT DISTINCT AliasSerNum AS serial, AliasRevSerNum AS revision, SessionId AS sessionid, AliasType AS `type`, AliasUpdate AS `update`,
    AliasName_EN AS name_EN, AliasName_FR AS name_FR, AliasDescription_EN AS description_EN, AliasDescription_FR AS description_FR,
    EducationalMaterialControlSerNum AS educational_material, SourceDatabaseSerNum AS source_db, ColorTag AS color,
    ModificationAction AS mod_action, DateAdded AS date_added
    FROM ".OPAL_ALIAS_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_ALIAS_EXPRESSIONS","
    SELECT DISTINCT AliasSerNum AS serial, RevSerNum AS revision, SessionId AS sessionid, ExpressionName AS expression,
    Description AS resource_description, ModificationAction AS mod_action, DateAdded AS date_added FROM ".OPAL_ALIAS_EXPRESSION_MH_TABLE." 
    WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_DIAGNOSIS_TRANSLATIONS","
    SELECT DISTINCT DiagnosisTranslationSerNum AS serial, RevSerNum AS revision, SessionId AS sessionid, 
    EducationalMaterialControlSerNum AS educational_material, Name_EN AS name_EN, Name_FR AS name_FR, Description_EN AS description_EN,
    Description_FR AS description_FR, ModificationAction AS mod_action, DateAdded AS date_added FROM ".OPAL_DIAGNOSIS_TRANSLATION_MH_TABLE."
    WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_DIAGNOSIS_CODE","
    SELECT DISTINCT DiagnosisTranslationSerNum AS serial, RevSerNum AS revision, SessionId AS sessionid, SourceUID AS sourceuid,
    DiagnosisCode AS code, Description AS description, ModificationAction AS mod_action, DateAdded AS date_added
    FROM ".OPAL_DIAGNOSIS_CODE_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_EMAIL","
    SELECT DISTINCT EmailControlSerNum AS serial, RevSerNum AS revision, SessionId AS sessionid, Subject_EN AS subject_EN,
    Subject_FR AS subject_FR, Body_EN AS body_EN, Body_FR AS body_FR, ModificationAction AS mod_action, DateAdded AS date_added
    FROM ".OPAL_EMAIL_CONTROL_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_TRIGGER","
    SELECT DISTINCT ControlTableSerNum AS control_serial, ControlTable AS control_table, SessionId AS sessionid, FilterType AS `type`,
    FilterId AS filterid, ModificationAction AS mod_action, DateAdded AS date_added FROM ".OPAL_FILTERS_MH_TABLE."
    WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_HOSPITAL_MAP","
    SELECT DISTINCT HospitalMapSerNum AS serial, RevSerNum AS revision, SessionId AS sessionid, MapUrl AS url, QRMapAlias AS qrcode,
    MapName_EN AS name_EN, MapName_FR AS name_FR, MapDescription_EN AS description_EN, MapDescription_FR AS description_FR,
    ModificationAction AS mod_action, DateAdded AS date_added FROM ".OPAL_HOSPITAL_MAP_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy
    ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_POST","
    SELECT DISTINCT PostControlSerNum AS control_serial, RevSerNum AS revision, SessionId AS sessionid, PostType AS `type`,
    PublishFlag AS publish, Disabled AS disabled, PublishDate AS publish_date, PostName_EN AS name_EN, PostName_FR AS name_FR,
    Body_EN AS body_EN, Body_FR AS body_FR, ModificationAction AS mod_action, DateAdded AS date_added
    FROM ".OPAL_POST_CONTROL_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_NOTIFICATION","
    SELECT DISTINCT NotificationControlSerNum AS control_serial, RevSerNum AS revision, SessionId AS sessionid, 
    NotificationTypeSerNum AS `type`, Name_EN AS name_EN, Name_FR AS name_FR, Description_EN AS description_EN,
    Description_FR AS description_FR, ModificationAction AS mod_action, DateAdded AS date_added
    FROM ".OPAL_NOTIFICATION_CONTROL_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_QUESTIONNAIRE","
    SELECT DISTINCT QuestionnaireControlSerNum AS control_serial, RevSerNum AS revision, SessionId AS sessionid,
    QuestionnaireDBSerNum AS db_serial, QuestionnaireName_EN AS name_EN, QuestionnaireName_FR AS name_FR, Intro_EN AS intro_EN,
    Intro_FR AS intro_FR, PublishFlag AS publish, ModificationAction AS mod_action, DateAdded AS date_added
    FROM ".OPAL_QUESTIONNAIRE_CONTROL_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_TEST_RESULT","
    SELECT DISTINCT TestResultControlSerNum AS control_serial, RevSerNum AS revision, SessionId AS sessionid,
    SourceDatabaseSerNum AS source_db, EducationalMaterialControlSerNum AS educational_material, Name_EN AS name_EN,
    Name_FR AS name_FR, Description_EN AS description_EN, Description_FR AS description_FR, Group_EN AS group_EN,
    Group_FR AS group_FR, PublishFlag AS publish, ModificationAction AS mod_action, DateAdded AS date_added
    FROM ".OPAL_TEST_RESULT_CONTROL_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_USER_TEST_RESULT_EXP","
    SELECT DISTINCT TestResultControlSerNum AS control_serial, RevSerNum AS revision, SessionId AS sessionid,
    ExpressionName AS expression, ModificationAction AS mod_action, DateAdded AS date_added
    FROM ".OPAL_TEST_RESULT_EXP_MH_TABLE." WHERE LastUpdatedBy = :LastUpdatedBy ORDER BY DateAdded DESC;
");

define("OPAL_GET_STUDIES_LIST","
    SELECT ID, code, title, investigator, startDate, endDate, creationDate FROM ".OPAL_STUDY_TABLE."
    WHERE deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_STUDY_DETAILS","
    SELECT ID, code, title, investigator, startDate, endDate FROM ".OPAL_STUDY_TABLE." WHERE ID = :ID AND deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_UPDATE_STUDY","
    UPDATE ".OPAL_STUDY_TABLE." SET code = :code, title = :title, investigator = :investigator, startDate = :startDate,
    endDate = :endDate, updatedBy = :updatedBy WHERE ID = :ID AND deleted = ".NON_DELETED_RECORD."; 
");

define("OPAL_MARK_STUDY_AS_DELETED", "
    UPDATE ".OPAL_STUDY_TABLE." SET deleted = ".DELETED_RECORD.", updatedBy = :updatedBy WHERE ID = :ID;
");

define("OPAL_GET_ROLES", "
    SELECT r.ID, r.name_EN, r.name_FR, (SELECT COUNT(*) FROM ".OPAL_OAUSER_TABLE." u WHERE u.oaRoleId = r.ID) AS total 
    FROM ".OPAL_OA_ROLE_TABLE." r WHERE r.deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_AVAILABLE_ROLES_MODULES", "
    SELECT `ID`, `operation`, `name_EN`, `name_FR` FROM `".OPAL_MODULE_TABLE."` WHERE `active` = ".ACTIVE_RECORD." ORDER BY `order`;
");

define("OPAL_GET_MODULES_OPERATIONS","
    SELECT `ID`, `operation` FROM `".OPAL_MODULE_TABLE."` WHERE `ID` IN (%%MODULESID%%) AND active = ".ACTIVE_RECORD." ORDER BY `ID`;
");

define("OPAL_GET_USER_ROLE_MODULE_ACCESS","
    SELECT `access` FROM `".OPAL_OA_ROLE_MODULE_TABLE."` WHERE oaRoleId = :oaRoleId AND moduleId = ".MODULE_ROLE.";
");

define("OPAL_GET_OA_ROLE_DETAILS","
    SELECT r.ID, r.name_EN, r.name_FR, (SELECT COUNT(*) FROM ".OPAL_OAUSER_TABLE." u WHERE u.oaRoleId = r.ID) AS total
    FROM ".OPAL_OA_ROLE_TABLE." r      
    WHERE r.ID = :ID AND r.deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_OA_ROLE_MODULE","
    SELECT * FROM `".OPAL_OA_ROLE_MODULE_TABLE."` WHERE `oaRoleId` = :oaRoleId;
");

define("OPAL_UPDATE_ROLE", "
    UPDATE ".OPAL_OA_ROLE_TABLE." SET updatedBy = :updatedBy, name_EN = :name_EN, name_FR = :name_FR
    WHERE ID = :ID AND deleted = ".NON_DELETED_RECORD.";"
);

define("OPAL_UPDATE_ROLE_MODULE", "
    UPDATE ".OPAL_OA_ROLE_MODULE_TABLE." SET access = :access WHERE ID = :ID;"
);

define("OPAL_DELETE_OA_ROLE_MODULE_OPTIONS",
    "DELETE FROM ".OPAL_OA_ROLE_MODULE_TABLE." WHERE oaRoleId = :oaRoleId AND moduleId NOT IN (%%MODULEIDS%%);"
);

define("OPAL_FORCE_UPDATE_UPDATEDBY",
    "UPDATE %%TABLENAME%%
    SET updatedBy = :updatedBy, lastUpdated = NOW()
    WHERE ID = :ID
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("OPAL_MARK_ROLE_AS_DELETED", "
    UPDATE ".OPAL_OA_ROLE_TABLE." SET deleted = ".DELETED_RECORD.", updatedBy = :updatedBy , deletedBy = :deletedBy WHERE ID = :ID;
");

define("OPAL_GET_USER_ACCESS","
    SELECT m.ID, m.operation, (CASE WHEN active = 1 THEN COALESCE(p.access,0) WHEN active = 0 THEN 0 END) AS access
    FROM ".OPAL_MODULE_TABLE." m LEFT JOIN ((SELECT * FROM ".OPAL_OA_ROLE_MODULE_TABLE." rm WHERE rm.oaRoleId = :oaRoleId) AS p) ON p.moduleId = m.ID;
");

define("OPAL_GET_USER_ACCESS_REGISTRATION","
    SELECT m.ID, m.operation, (CASE WHEN active = 1 THEN COALESCE(p.access,0) WHEN active = 0 THEN 0 END) AS access
    FROM ".OPAL_MODULE_TABLE." m LEFT JOIN ((SELECT * FROM ".OPAL_OA_ROLE_MODULE_TABLE." rm WHERE rm.oaRoleId = :oaRoleId) AS p) ON p.moduleId = m.ID WHERE m.ID = ".MODULE_PATIENT.";
");

define("OPAL_GET_EDUCATIONAL_MATERIAL","
    SELECT DISTINCT em.EducationalMaterialControlSerNum AS serial, em.EducationalMaterialType_EN AS type_EN, em.EducationalMaterialType_FR AS type_FR, em.Name_EN AS name_EN, em.Name_FR AS name_FR, em.URL_EN AS url_EN, em.URL_FR AS url_FR, phase.PhaseInTreatmentSerNum AS phase_serial, phase.Name_EN AS phase_EN, phase.Name_FR AS phase_FR, em.PublishFlag AS publish, em.ParentFlag AS parentFlag, em.ShareURL_EN AS share_url_EN, em.ShareURL_FR AS share_url_FR, em.LastUpdated AS lastupdated, (SELECT COUNT(*) AS locked FROM ".OPAL_FILTERS_TABLE." f WHERE f.ControlTableSerNum = em.EducationalMaterialControlSerNum and ControlTable = '".OPAL_EDUCATION_MATERIAL_TABLE."') AS locked, (case WHEN em.ParentFlag = 1 then (SELECT COALESCE(round(AVG(emr.RatingValue)), 0) FROM EducationalMaterialRating emr WHERE emr.EducationalMaterialControlSerNum = em.EducationalMaterialControlSerNum) ELSE 0 END) AS rating FROM ".OPAL_EDUCATION_MATERIAL_TABLE." em, ".OPAL_PHASE_IN_TREATMENT_TABLE." phase WHERE phase.PhaseInTreatmentSerNum = em.PhaseInTreatmentSerNum AND em.deleted = 0;
");

define("OPAL_GET_TOCS_EDU_MATERIAL","
    SELECT DISTINCT em.EducationalMaterialControlSerNum AS serial, em.Name_EN AS name_EN, em.Name_FR AS name_FR, toc.OrderNum AS `order`, em.EducationalMaterialType_EN AS type_EN, em.EducationalMaterialType_FR AS type_FR, em.URL_EN AS url_EN, em.URL_FR AS url_FR FROM ".OPAL_EDUCATION_MATERIAL_TOC_TABLE." toc, ".OPAL_EDUCATION_MATERIAL_TABLE." em WHERE toc.EducationalMaterialControlSerNum= em.EducationalMaterialControlSerNum AND toc.ParentSerNum = :ParentSerNum ORDER BY toc.OrderNum
");

define("OPAL_GET_EDU_MATERIAL_DETAILS","
    SELECT DISTINCT em.EducationalMaterialType_EN AS type_EN, em.EducationalMaterialType_FR AS type_FR, em.Name_EN AS name_EN, em.Name_FR AS name_FR, em.EducationalMaterialControlSerNum AS serial, em.PublishFlag AS publish, em.URL_EN AS url_EN, em.URL_FR AS url_FR, phase.PhaseInTreatmentSerNum AS phase_serial, phase.Name_EN AS phase_EN, phase.Name_FR AS phase_FR, em.ShareURL_EN AS share_url_EN, em.ShareURL_FR AS share_url_FR FROM ".OPAL_EDUCATION_MATERIAL_TABLE." em, ".OPAL_PHASE_IN_TREATMENT_TABLE." phase WHERE em.EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum AND phase.PhaseInTreatmentSerNum = em.PhaseInTreatmentSerNum;
");

define("OPAL_GET_EDU_MATERIAL_MH","
    SELECT DISTINCT emc.Name_EN AS material_name, emmh.EducationalMaterialRevSerNum AS revision, emmh.CronLogSerNum AS cron_serial, emmh.PatientSerNum AS patient_serial, emmh.DateAdded AS date_added, emmh.ReadStatus AS read_status, emmh.ModificationAction AS mod_action FROM ".OPAL_EDUCATION_MATERIAL_MH_TABLE." emmh, ".OPAL_EDUCATION_MATERIAL_TABLE." emc WHERE emc.EducationalMaterialControlSerNum = emmh.EducationalMaterialControlSerNum AND emmh.CronLogSerNum IN (%%LIST_IDS%%);
");

define("OPAL_GET_TASK_MH","
    SELECT DISTINCT ae.ExpressionName AS expression_name, ae.Description AS expression_description, tmh.TaskRevSerNum AS revision, tmh.CronLogSerNum AS cron_serial, tmh.PatientSerNum AS patient_serial, sd.SourceDatabaseName AS source_db, tmh.TaskAriaSer AS source_uid, tmh.Status AS status, tmh.State AS state, tmh.DueDateTime AS due_date, tmh.CreationDate AS creation, tmh.CompletionDate AS completed, tmh.DateAdded AS date_added, 'N/A' AS read_status, tmh.ModificationAction AS mod_action FROM ".OPAL_TASK_MH_TABLE." tmh, ".OPAL_ALIAS_EXPRESSION_TABLE." ae, ".OPAL_SOURCE_DATABASE_TABLE." sd WHERE tmh.AliasExpressionSerNum = ae.AliasExpressionSerNum AND tmh.SourceDatabaseSerNum = sd.SourceDatabaseSerNum AND tmh.CronLogSerNum IN (%%LIST_IDS%%);
");

define("OPAL_GET_DOCUMENT_MH","
    SELECT DISTINCT ae.ExpressionName AS expression_name, ae.Description AS expression_description, docmh.DocumentRevSerNum AS revision, docmh.CronLogSerNum AS cron_serial, docmh.PatientSerNum AS patient_serial, sd.SourceDatabaseName AS source_db, docmh.DocumentId AS source_uid, (SELECT LastName FROM Staff Staff1 WHERE Staff1.StaffSerNum = docmh.CreatedBySerNum) AS created_by, docmh.CreatedTimeStamp AS created_time, (SELECT LastName FROM Staff Staff2 WHERE Staff2.StaffSerNum = docmh.ApprovedBySerNum) AS approved_by, docmh.ApprovedTimeStamp AS approved_time, (SELECT LastName FROM Staff Staff3 WHERE Staff3.StaffSerNum = docmh.AuthoredBySerNum) AS authored_by, docmh.DateOfService AS dateofservice, docmh.Revised AS revised, docmh.ValidEntry AS valid, docmh.OriginalFileName AS original_file, docmh.FinalFileName AS final_file, docmh.TransferStatus AS transfer, docmh.TransferLog AS transfer_log, docmh.DateAdded AS date_added, docmh.ReadStatus AS read_status, docmh.ModificationAction AS mod_action FROM ".OPAL_DOCUMENT_MH_TABLE." docmh, ".OPAL_ALIAS_EXPRESSION_TABLE." ae, ".OPAL_SOURCE_DATABASE_TABLE." sd WHERE docmh.AliasExpressionSerNum  = ae.AliasExpressionSerNum AND docmh.SourceDatabaseSerNum = sd.SourceDatabaseSerNum AND docmh.CronLogSerNum IN (%%LIST_IDS%%);
");

define("OPAL_GET_APPOINTMENT_MH","
    SELECT DISTINCT ae.ExpressionName AS expression_name, ae.Description AS expression_description, apmh.AppointmentRevSerNum AS revision, apmh.CronLogSerNum AS cron_serial, apmh.PatientSerNum AS patient_serial, sd.SourceDatabaseName AS source_db, apmh.AppointmentAriaSer AS source_uid, apmh.Status AS status, apmh.State AS state, apmh.ScheduledStartTime AS scheduled_start, apmh.ScheduledEndTime AS scheduled_end, apmh.ActualStartDate AS actual_start, apmh.ActualEndDate AS actual_end, apmh.RoomLocation_EN AS room_EN, apmh.RoomLocation_FR AS room_FR, apmh.Checkin AS checkin, apmh.DateAdded AS date_added, apmh.ReadStatus AS read_status, apmh.ModificationAction AS mod_action FROM ".OPAL_APPOINTMENT_MH_TABLE." apmh, ".OPAL_ALIAS_EXPRESSION_TABLE." ae, ".OPAL_SOURCE_DATABASE_TABLE." sd WHERE apmh.AliasExpressionSerNum = ae.AliasExpressionSerNum AND apmh.SourceDatabaseSerNum = sd.SourceDatabaseSerNum AND apmh.CronLogSerNum IN (%%LIST_IDS%%);
");

define("OPAL_GET_ALIAS_MH","
    SELECT DISTINCT al.AliasType AS type, ae.ExpressionName AS expression_name, ae.Description AS expression_description, apmh.AppointmentRevSerNum AS revision, apmh.CronLogSerNum AS cron_serial, apmh.PatientSerNum AS patient_serial, sd.SourceDatabaseName AS source_db, apmh.AppointmentAriaSer AS source_uid, apmh.DateAdded AS date_added, apmh.ReadStatus AS read_status, apmh.ModificationAction AS mod_action FROM ".OPAL_APPOINTMENT_MH_TABLE." apmh, ".OPAL_ALIAS_EXPRESSION_TABLE." ae, ".OPAL_SOURCE_DATABASE_TABLE." sd, ".OPAL_ALIAS_TABLE." al WHERE apmh.AliasExpressionSerNum  = ae.AliasExpressionSerNum AND ae.AliasSerNum = al.AliasSerNum AND apmh.SourceDatabaseSerNum = sd.SourceDatabaseSerNum AND apmh.CronLogSerNum IN (%%LIST_IDS%%) UNION ALL SELECT DISTINCT al.AliasType AS type, ae.ExpressionName AS expression_name, ae.Description AS expression_description, docmh.DocumentRevSerNum AS revision, docmh.CronLogSerNum AS cron_serial, docmh.PatientSerNum AS patient_serial, sd.SourceDatabaseName AS source_db, docmh.DocumentId AS source_uid, docmh.DateAdded AS date_added, docmh.ReadStatus AS read_status, docmh.ModificationAction AS mod_action FROM ".OPAL_DOCUMENT_MH_TABLE." docmh, ".OPAL_ALIAS_EXPRESSION_TABLE." ae, ".OPAL_SOURCE_DATABASE_TABLE." sd, ".OPAL_ALIAS_TABLE." al WHERE docmh.AliasExpressionSerNum = ae.AliasExpressionSerNum AND ae.AliasSerNum = al.AliasSerNum AND docmh.SourceDatabaseSerNum = sd.SourceDatabaseSerNum AND docmh.CronLogSerNum IN (%%LIST_IDS%%) UNION ALL SELECT DISTINCT al.AliasType AS type, ae.ExpressionName AS expression_name, ae.Description AS expression_description, tmh.TaskRevSerNum AS revision, tmh.CronLogSerNum AS cron_serial, tmh.PatientSerNum AS patient_serial, sd.SourceDatabaseName AS source_db, tmh.TaskAriaSer AS source_uid, tmh.DateAdded AS date_added, 'N/A' AS read_status, tmh.ModificationAction AS mod_action FROM ".OPAL_TASK_MH_TABLE." tmh, ".OPAL_ALIAS_EXPRESSION_TABLE." ae, ".OPAL_SOURCE_DATABASE_TABLE." sd, ".OPAL_ALIAS_TABLE." al WHERE tmh.AliasExpressionSerNum = ae.AliasExpressionSerNum AND ae.AliasSerNum = al.AliasSerNum AND tmh.SourceDatabaseSerNum = sd.SourceDatabaseSerNum AND tmh.CronLogSerNum IN (%%LIST_IDS%%);
");

define("OPAL_GET_EMAILS_MH","
    SELECT DISTINCT emmh.EmailControlSerNum AS control_serial, emmh.EmailRevSerNum AS revision, emmh.CronLogSerNum AS cron_serial, emmh.PatientSerNum AS patient_serial, emt.EmailTypeName AS type, emmh.DateAdded AS date_added, emmh.ModificationAction AS mod_action FROM ".OPAL_EMAIL_LOG_MH_TABLE." emmh, ".OPAL_EMAIL_CONTROL." emc, ".OPAL_EMAIL_TYPE." emt WHERE emmh.EmailControlSerNum = emc.EmailControlSerNum AND emc.EmailTypeSerNum = emt.EmailTypeSerNum AND emmh.CronLogSerNum IN (%%LIST_IDS%%);
");

define("OPAL_GET_NOTIFICATIONS_MH","
    SELECT DISTINCT ntmh.NotificationControlSerNum AS control_serial, ntmh.NotificationRevSerNum AS revision, ntmh.CronLogSerNum AS cron_serial, ntmh.PatientSerNum AS patient_serial, ntt.NotificationTypeName AS type, ntmh.RefTableRowSerNum AS ref_table_serial, ntmh.ReadStatus AS read_status, ntmh.DateAdded AS date_added, ntmh.ModificationAction AS mod_action FROM ".OPAL_NOTIFICATION_MH_TABLE." ntmh, ".OPAL_NOTIFICATION_CONTROL_TABLE." ntc, ".OPAL_NOTIFICATION_TYPES_TABLE." ntt WHERE ntmh.NotificationControlSerNum = ntc.NotificationControlSerNum AND ntc.NotificationTypeSerNum = ntt.NotificationTypeSerNum  AND ntmh.CronLogSerNum IN (%%LIST_IDS%%);
");

define("OPAL_GET_TEST_RESULTS_MH", "
    SELECT DISTINCT tre.ExpressionName AS expression_name, trmh.TestResultRevSerNum AS revision, trmh.CronLogSerNum AS cron_serial, trmh.PatientSerNum AS patient_serial, sd.SourceDatabaseName AS source_db, trmh.TestResultAriaSer AS source_uid, trmh.AbnormalFlag AS abnormal_flag, trmh.TestDate AS test_date, trmh.MaxNorm AS max_norm, trmh.MinNorm AS min_norm, trmh.TestValue AS test_value, trmh.UnitDescription AS unit, trmh.ValidEntry AS valid, trmh.DateAdded AS date_added, trmh.ReadStatus AS read_status, trmh.ModificationAction AS mod_action FROM ".OPAL_TEST_RESULT_MH_TABLE." trmh, ".OPAL_TEST_RESULT_EXPRESSION_TABLE." tre, ".OPAL_SOURCE_DATABASE_TABLE." sd WHERE trmh.TestResultExpressionSerNum = tre.TestResultExpressionSerNum AND trmh.SourceDatabaseSerNum = sd.SourceDatabaseSerNum AND trmh.CronLogSerNum IN (%%LIST_IDS%%);
");

define("OPAL_GET_HOSPITAL_MAP_DETAILS","
    SELECT DISTINCT HospitalMapSerNum AS serial, MapURL_EN AS url_EN, MapURL_FR AS url_FR, QRMapAlias AS qrid, MapName_EN AS name_EN, MapDescription_EN AS description_EN, MapName_FR AS name_FR, MapDescription_FR AS description_FR FROM ".OPAL_HOSPITAL_MAP_TABLE." WHERE HospitalMapSerNum = :HospitalMapSerNum;
");

define("OPAL_GET_CRON_LOG_APPOINTMENTS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(apmh.CronLogSerNum) AS y, apmh.CronLogSerNum AS cron_serial FROM ".OPAL_APPOINTMENT_MH_TABLE." apmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = apmh.CronLogSerNum AND apmh.CronLogSerNum IS NOT NULL GROUP BY apmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_CRON_LOG_DOCUMENTS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(docmh.CronLogSerNum) AS y, docmh.CronLogSerNum AS cron_serial FROM ".OPAL_DOCUMENT_MH_TABLE." docmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = docmh.CronLogSerNum AND docmh.CronLogSerNum IS NOT NULL GROUP BY docmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_CRON_LOG_TASKS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(tmh.CronLogSerNum) AS y, tmh.CronLogSerNum AS cron_serial FROM ".OPAL_TASK_MH_TABLE." tmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = tmh.CronLogSerNum AND tmh.CronLogSerNum IS NOT NULL GROUP BY tmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_CRON_LOG_ANNOUNCEMENTS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(anmh.CronLogSerNum) AS y, anmh.CronLogSerNum AS cron_serial FROM ".OPAL_ANNOUNCEMENT_MH_TABLE." anmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = anmh.CronLogSerNum AND anmh.CronLogSerNum IS NOT NULL GROUP BY anmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_CRON_LOG_TTMS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(ttmmh.CronLogSerNum) AS y, ttmmh.CronLogSerNum AS cron_serial FROM ".OPAL_TXT_TEAM_MSG_MH_TABLE." ttmmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = ttmmh.CronLogSerNum AND ttmmh.CronLogSerNum IS NOT NULL GROUP BY ttmmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_CRON_LOG_PFP","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(pfpmh.CronLogSerNum) AS y, pfpmh.CronLogSerNum AS cron_serial FROM ".OPAL_PATIENTS_FOR_PATIENTS_MH_TABLE." pfpmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = pfpmh.CronLogSerNum AND pfpmh.CronLogSerNum IS NOT NULL GROUP BY pfpmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_CRON_LOG_EDU_MATERIALS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(emmh.CronLogSerNum) AS y, emmh.CronLogSerNum AS cron_serial FROM ".OPAL_EDUCATION_MATERIAL_MH_TABLE." emmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = emmh.CronLogSerNum AND emmh.CronLogSerNum IS NOT NULL GROUP BY emmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_CRON_LOG_NOTIFICATIONS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(ntmh.CronLogSerNum) AS y, ntmh.CronLogSerNum AS cron_serial FROM ".OPAL_NOTIFICATION_MH_TABLE." ntmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = ntmh.CronLogSerNum AND ntmh.CronLogSerNum IS NOT NULL GROUP BY ntmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_CRON_LOG_TEST_RESULTS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(trmh.CronLogSerNum) AS y, trmh.CronLogSerNum AS cron_serial FROM ".OPAL_TEST_RESULT_MH_TABLE." trmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = trmh.CronLogSerNum AND trmh.CronLogSerNum IS NOT NULL GROUP BY trmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC
");

define("OPAL_GET_CRON_LOG_EMAILS","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(emmh.CronLogSerNum) AS y, emmh.CronLogSerNum AS cron_serial FROM ".OPAL_EMAIL_LOG_MH_TABLE." emmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = emmh.CronLogSerNum AND emmh.CronLogSerNum IS NOT NULL GROUP BY  emmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC 
");

define("OPAL_GET_CRON_LOG_QUESTIONNAIRES","
    SELECT DISTINCT cl.CronDateTime AS x, COUNT(lqmh.CronLogSerNum) AS y, lqmh.CronLogSerNum AS cron_serial FROM ".OPAL_QUESTIONNAIRE_MH_TABLE." lqmh, ".OPAL_CRON_LOG_TABLE." cl WHERE cl.CronStatus = 'Started' AND cl.CronLogSerNum = lqmh.CronLogSerNum AND lqmh.CronLogSerNum IS NOT NULL GROUP BY lqmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC
");

define("OPAL_GET_HOSPITAL_MAPS","
    SELECT DISTINCT HospitalMapSerNum AS serial, MapURL_EN AS url_EN, MapURL_FR AS url_FR, QRMapAlias AS qrid,
    MapName_EN AS name_EN, MapDescription_EN AS description_EN, MapName_FR AS name_FR,
    MapDescription_FR AS description_FR FROM ".OPAL_HOSPITAL_MAP_TABLE."
");

define("OPAL_GET_CATEGORY_MENU","
SELECT ID, name_EN, name_FR FROM ".OPAL_CATEGORY_MODULE_TABLE." ORDER BY `order`
");

define("OPAL_GET_NAV_MENU","
    SELECT ID, operation, name_EN, name_FR, iconClass, url, subModule, subModuleMenu FROM ".OPAL_MODULE_TABLE."
    WHERE active = ".ACTIVE_RECORD." AND categoryModuleId = :categoryModuleId ORDER BY `order`
");

define("OPAL_GET_ALERTS_LIST","
    SELECT ID, subject, active, creationDate, lastUpdated FROM ".OPAL_ALERT_TABLE." WHERE deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_UPDATE_ALERT_ACTIVATION_FLAG",
    "UPDATE ".OPAL_ALERT_TABLE." SET active = :active, updatedBy = :updatedBy WHERE ID = :ID AND (active != :active);"
);

define("OPAL_GET_ALERT_DETAILS",
    "SELECT `ID`, `contact`, `subject`, `body`, `trigger` FROM ".OPAL_ALERT_TABLE." WHERE ID = :ID;"
);

define("OPAL_UPDATE_ALERT", "
    UPDATE ".OPAL_ALERT_TABLE." SET `contact` = :contact, `subject` = :subject, `body` = :body, `trigger` = :trigger,
    updatedBy = :updatedBy WHERE `ID` = :ID AND deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_MARK_ALERT_AS_DELETED", "
    UPDATE ".OPAL_ALERT_TABLE." SET deleted = ".DELETED_RECORD.", active = ".INACTIVE_RECORD.", updatedBy = :updatedBy,
    deletedBy = :updatedBy WHERE ID = :ID;
");

define("OPAL_GET_AUDITS","
    SELECT `ID`, `module`, `method`, `access`, `ipAddress`, `creationDate`, `createdBy` FROM ".OPAL_AUDIT_TABLE."
    ORDER BY creationDate DESC, createdBy LIMIT 10000;
");

define("OPAL_GET_AUDIT_DETAILS",
    "SELECT * FROM ".OPAL_AUDIT_TABLE." WHERE ID = :ID;"
);

define("OPAL_GET_DIAG_TRANS_DETAILS","
    SELECT DISTINCT DiagnosisTranslationSerNum AS serial, Name_EN AS name_EN, Name_FR AS name_FR, Description_EN AS description_EN,
    Description_FR AS description_FR, EducationalMaterialControlSerNum AS eduMatSer, NULL AS eduMat FROM ".OPAL_DIAGNOSIS_TRANSLATION_TABLE."
    WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum
");

define("OPAL_GET_DIAGNOSIS_CODES","
    SELECT DISTINCT SourceUID AS sourceuid, DiagnosisCode AS code, Description AS description,
    CONCAT(DiagnosisCode, ' (', Description, ')') AS name, 1 AS added FROM ".OPAL_DIAGNOSIS_CODE_TABLE."
    WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum;
");

define("OPAL_GET_ACTIVATE_SOURCE_DB","
    SELECT SourceDatabaseSerNum FROM ".OPAL_SOURCE_DATABASE_TABLE." WHERE Enabled = ".ACTIVE_RECORD."
");

define("OPAL_GET_ASSIGNED_DIAGNOSES","
    SELECT dxc.SourceUID AS sourceuid, dxt.Name_EN AS name_EN, dxt.Name_FR AS name_FR FROM ".OPAL_DIAGNOSIS_CODE_TABLE." dxc
    LEFT JOIN ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." dxt ON dxt.DiagnosisTranslationSerNum = dxc.DiagnosisTranslationSerNum;
");

define("OPAL_GET_DIAGNOSES","
    SELECT externalId AS sourceuid, code, description, CONCAT(code, ' (', description, ')') AS name
    FROM ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." WHERE deleted = ".NON_DELETED_RECORD." AND source IN(%%SOURCE_DB_IDS%%) ORDER BY code
");

define("OPAL_GET_DIAGNOSIS_TRANSLATIONS","
    SELECT dt.DiagnosisTranslationSerNum AS serial, dt.Name_EN AS name_EN, dt.Name_FR AS name_FR,
    (SELECT COUNT(*) FROM DiagnosisCode dc WHERE dc.DiagnosisTranslationSerNum = dt.DiagnosisTranslationSerNum) AS `count`
    FROM ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." dt;
");

define("OPAL_VALIDATE_EDU_MATERIAL_ID","
    SELECT COUNT(*) AS total FROM ".OPAL_EDUCATION_MATERIAL_TABLE."
    WHERE EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum;
");

define("OPAL_UPDATE_DIAGNOSIS_TRANSLATION","
    UPDATE ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." SET Name_EN = :Name_EN, Name_FR = :Name_FR, Description_EN = :Description_EN,
    Description_FR = :Description_FR, EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum,
    LastUpdatedBy = :LastUpdatedBy, SessionId = :SessionId WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum
    AND (Name_EN != :Name_EN || Name_FR != :Name_FR || Description_EN != :Description_EN || Description_FR != :Description_FR
    || EducationalMaterialControlSerNum != :EducationalMaterialControlSerNum)
");

define("OPAL_DELETE_DIAGNOSIS_CODES","
    DELETE FROM ".OPAL_DIAGNOSIS_CODE_TABLE." WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum AND
    SourceUID NOT IN (%%LIST_SOURCES_UIDS%%);
");

define("OPAL_DELETE_ALL_DIAGNOSIS_CODES","
    DELETE FROM ".OPAL_DIAGNOSIS_CODE_TABLE." WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum;
");

define("OPAL_DELETE_DIAGNOSIS_TRANSLATION","
    DELETE FROM ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum;
");

define("OPAL_GET_TRIGGERS_LIST","
    SELECT sourceContentId, sourceModuleId, onCondition, eventType, targetContentId, targetModuleId FROM ".OPAL_TRIGGER_TABLE." WHERE active = ".ACTIVE_RECORD." AND sourceContentId = :contentId AND sourceModuleId = :moduleId;
");