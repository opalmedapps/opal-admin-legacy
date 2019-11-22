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
define("OPAL_MODULE_TRIGGER_SETTING_TABLE","moduleTriggerSetting");
define("OPAL_POST_TABLE","PostControl");

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
    AND ControlTable = 'LegacyQuestionnaireControl'
    AND FilterType != ''
    AND FilterId != '';"
);

define("SQL_OPAL_DELETE_FILTERS",
    "DELETE FROM ".OPAL_FILTERS_TABLE."
    WHERE FilterId = :FilterId
    AND FilterType = :FilterType
    AND ControlTableSerNum = :ControlTableSerNum
    AND ControlTable = 'LegacyQuestionnaireControl';"
);

define("SQL_OPAL_UPDATE_FILTERSMH",
    "UPDATE ".OPAL_FILTERS_MODIFICATION_HISTORY_TABLE."
    SET 
    LastUpdatedBy = :LastUpdatedBy,
    SessionId = :SessionId
    WHERE FilterId = :FilterId
    AND FilterType = :FilterType
    AND ControlTableSerNum = :ControlTableSerNum
    AND ControlTable = 'LegacyQuestionnaireControl'
    ORDER BY DateAdded DESC 
    LIMIT 1;"
);

define("SQL_OPAL_DELETE_FREQUENCY_EVENTS_TABLE",
    "DELETE FROM ".OPAL_FREQUENCY_EVENTS_TABLE." 
    WHERE ControlTable = 'LegacyQuestionnaireControl'
    AND ControlTableSerNum = :ControlTableSerNum;"
);

define("SQL_OPAL_UPDATE_PUBLISHED_QUESTIONNAIRES_STATUS_LAST_PUBLISHED",
    "UPDATE ".OPAL_QUESTIONNAIRE_CONTROL_TABLE."
    SET PublishFlag = :PublishFlag, LastUpdatedBy = :LastUpdatedBy, LastPublished = :LastPublished
    WHERE QuestionnaireControlSerNum = :QuestionnaireControlSerNum
    AND (PublishFlag != :PublishFlag);"
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

define("SQL_OPAL_GET_MODULE_BY_ID", "
    SELECT * FROM ".OPAL_MODULE_TABLE." WHERE ID = :ID;
");

define("SQL_OPAL_GET_TRIGGERS_PER_MODULE", "
    SELECT triggerSettingId FROM ".OPAL_MODULE_TRIGGER_SETTING_TABLE." WHERE moduleId = :moduleId;
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

define("SQL_OPAL_GET_FREQUENCY_EVENTS_QUESTIONNAIRE_CONTROL",
    "SELECT DISTINCT CustomFlag, MetaKey, MetaValue 
    FROM ".OPAL_FREQUENCY_EVENTS_TABLE."
    WHERE ControlTable = 'LegacyQuestionnaireControl'
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

define("SQL_OPAL_DELETE_REPEAT_END_FROM_FREQUENCY_EVENTS",
    "DELETE FROM ".OPAL_FREQUENCY_EVENTS_TABLE."
    WHERE ControlTable = 'LegacyQuestionnaireControl'
    AND ControlTableSerNum = :ControlTableSerNum
    AND MetaKey = 'repeat_end';"
);

define("SQL_OPAL_DELETE_OTHER_METAS_FROM_FREQUENCY_EVENTS",
    "DELETE FROM ".OPAL_FREQUENCY_EVENTS_TABLE."
    WHERE ControlTable = 'LegacyQuestionnaireControl'
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

define("SQL_OPAL_MARK_RECORD_AS_DELETED", "
    UPDATE %%TABLENAME%% SET deleted = ".DELETED_RECORD.", LastUpdatedBy = :LastUpdatedBy, SessionId = :SessionId
    WHERE %%PRIMARY_KEY%% = :recordId AND deleted = ".NON_DELETED_RECORD.";
");