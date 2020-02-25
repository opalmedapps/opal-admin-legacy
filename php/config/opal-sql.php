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

//Definition of all the tables from the opalDB database
define("OPAL_OAUSER_TABLE","OAUser");
define("OPAL_OAUSER_ROLE_TABLE","OAUserRole");
define("OPAL_QUESTIONNAIRE_CONTROL_TABLE","QuestionnaireControl");
define("OPAL_QUESTIONNAIRE_MH_TABLE","QuestionnaireMH");
define("OPAL_FILTERS_TABLE","Filters");
define("OPAL_FILTERS_MODIFICATION_HISTORY_TABLE","FiltersMH");
define("OPAL_FREQUENCY_EVENTS_TABLE","FrequencyEvents");
define("OPAL_MODULE_TABLE","module");
define("OPAL_MODULE_PUBLICATION_SETTING_TABLE","modulePublicationSetting");
define("OPAL_PUBLICATION_SETTING_TABLE","publicationSetting");
define("OPAL_POST_TABLE","PostControl");
define("OPAL_TX_TEAM_MESSAGE_TABLE","TxTeamMessage");
define("OPAL_ANNOUNCEMENT_TABLE","Announcement");
define("OPAL_PATIENTS_FOR_PATIENTS_TABLE","PatientsForPatients");
define("OPAL_EDUCATION_MATERIAL_TABLE","EducationalMaterialControl");
define("OPAL_PHASE_IN_TREATMENT_TABLE","PhaseInTreatment");
define("OPAL_ANNOUNCEMENT_MH_TABLE","AnnouncementMH");
define("OPAL_TXT_TEAM_MSG_MH_TABLE","TxTeamMessageMH");
define("OPAL_PATIENTS_FOR_PATIENTS_MH_TABLE","PatientsForPatientsMH");
define("OPAL_EDUCATION_MATERIAL_MH_TABLE","EducationalMaterialMH");
define("OPAL_CRON_LOG_TABLE","CronLog");

//Definition of the primary keys of the opalDB database
define("OPAL_POST_PK","PostControlSerNum");


/*
 * Listing of all SQL queries for the Opal database
 * */
define("SQL_OPAL_SELECT_USER_INFO",
    "SELECT OAUserSerNum AS OAUserId,
    Username AS username,
    Language as language
    FROM ".OPAL_OAUSER_TABLE."
    WHERE OAUserSerNum = :OAUserId"
);

define("SQL_OPAL_SELECT_USER_ROLE",
    "SELECT *
    FROM ".OPAL_OAUSER_ROLE_TABLE."
    WHERE OAUserSerNum = :OAUserId"
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

//TODO to remove - useless with new publication
define("SQL_OPAL_GET_FILTERS",
    "SELECT DISTINCT 
    f.FilterType AS type,
    f.FilterId AS id,
    1 AS added
    FROM 
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE." qc, 
    ".OPAL_FILTERS_TABLE." f
    WHERE
    qc.QuestionnaireControlSerNum = :QuestionnaireControlSerNum
    AND f.ControlTable = 'LegacyQuestionnaireControl'
    AND f.ControlTableSerNum = qc.QuestionnaireControlSerNum
    AND f.FilterType != ''
    AND f.FilterId != '';"
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
    "UPDATE ".OPAL_FILTERS_MODIFICATION_HISTORY_TABLE."
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
    "SELECT * FROM ".OPAL_MODULE_TABLE." m WHERE m.active = 1 AND m.publication = 1 ORDER BY m.order;"
);

define("SQL_OPAL_BUILD_PUBLICATION_VIEW",
    "SELECT m.sqlPublicationList, m.sqlPublicationChartLog FROM ".OPAL_MODULE_TABLE." m WHERE m.active = 1 AND m.publication = 1 ORDER BY m.order"
);

define("SQL_GET_QUERY_CHART_LOG",
    "SELECT sqlPublicationChartLog, sqlPublicationListLog FROM ".OPAL_MODULE_TABLE." m WHERE m.active = 1 AND m.publication = 1 AND ID = :ID"
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
    "SELECT m.ID, m.name_EN, m.name_FR, m.iconClass FROM ".OPAL_MODULE_TABLE." m WHERE m.active = 1 AND m.publication = 1 ORDER BY m.order;"
);

define("SQL_OPAL_GET_PUBLICATION_MODULES_USER_DETAILS",
    "SELECT m.ID, m.name_EN, m.name_FR, m.iconClass FROM ".OPAL_MODULE_TABLE." m WHERE m.active = 1 AND m.publication = 1 AND ID = :ID;"
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

//TODO to remove - useless with new publication
define("SQL_OPAL_GET_FILTERS_QUESTIONNAIRE_CONTROL",
    "SELECT DISTINCT 
    ".OPAL_FILTERS_TABLE.".FilterType AS type,
    ".OPAL_FILTERS_TABLE.".FilterId AS id,
    1 AS added
	FROM 
	".OPAL_QUESTIONNAIRE_CONTROL_TABLE.", 
	".OPAL_FILTERS_TABLE." 
	WHERE 
    ".OPAL_QUESTIONNAIRE_CONTROL_TABLE.".QuestionnaireControlSerNum = :QuestionnaireControlSerNum
    AND ".OPAL_FILTERS_TABLE.".ControlTable = 'LegacyQuestionnaireControl'
    AND ".OPAL_FILTERS_TABLE.".ControlTableSerNum = ".OPAL_QUESTIONNAIRE_CONTROL_TABLE.".QuestionnaireControlSerNum
    AND ".OPAL_FILTERS_TABLE.".FilterType != ''
    AND ".OPAL_FILTERS_TABLE.".FilterId != '';"
);

//TODO to remove - useless with new publication
define("SQL_OPAL_GET_FREQUENCY_EVENTS_QUESTIONNAIRE_CONTROL",
    "SELECT DISTINCT CustomFlag, MetaKey, MetaValue 
    FROM ".OPAL_FREQUENCY_EVENTS_TABLE."
    WHERE ControlTable = 'LegacyQuestionnaireControl'
    AND ControlTableSerNum = :ControlTableSerNum;"
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
                        SELECT DISTINCT
                            pc.PostName_EN AS post_control_name,
                            anmh.AnnouncementRevSerNum AS revision,
                            anmh.CronLogSerNum AS cron_serial,
                            anmh.PatientSerNum AS patient_serial,
                            anmh.DateAdded AS date_added,
                            anmh.ReadStatus AS read_status,
                            anmh.ModificationAction AS mod_action
                        FROM
                            AnnouncementMH anmh,
                            PostControl pc 
                        WHERE
                            pc.PostControlSerNum = anmh.PostControlSerNum
                        AND anmh.CronLogSerNum IN (%%CRON_LOG_IDS%%)
");
define("SQL_OPAL_GET_TTM_CHART_PER_IDS","
                        SELECT DISTINCT
                            pc.PostName_EN AS post_control_name,
                            ttmmh.TxTeamMessageRevSerNum AS revision,
                            ttmmh.CronLogSerNum AS cron_serial,
                            ttmmh.PatientSerNum AS patient_serial,
                            ttmmh.DateAdded AS date_added,
                            ttmmh.ReadStatus AS read_status,
                            ttmmh.ModificationAction AS mod_action
                        FROM
                            TxTeamMessageMH ttmmh,
                            PostControl pc 
                        WHERE
                            pc.PostControlSerNum = ttmmh.PostControlSerNum
                        AND ttmmh.CronLogSerNum IN (%%CRON_LOG_IDS%%)
");
define("SQL_OPAL_GET_PFP_CHART_PER_IDS","
                        SELECT DISTINCT
                            pc.PostName_EN AS post_control_name,
                            pfpmh.PatientsForPatientsRevSerNum AS revision,
                            pfpmh.CronLogSerNum AS cron_serial,
                            pfpmh.PatientSerNum AS patient_serial,
                            pfpmh.DateAdded AS date_added,
                            pfpmh.ReadStatus AS read_status,
                            pfpmh.ModificationAction AS mod_action
                        FROM
                            PatientsForPatientsMH pfpmh,
                            PostControl pc 
                        WHERE
                            pc.PostControlSerNum = pfpmh.PostControlSerNum
                        AND pfpmh.CronLogSerNum IN (%%CRON_LOG_IDS%%)
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