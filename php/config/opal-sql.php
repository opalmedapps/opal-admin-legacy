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

//Definition of all questionnaires table from the questionnaire DB
define("OPAL_OAUSER_TABLE","oauser");
define("OPAL_OAUSER_ROLE_TABLE","oauserrole");
define("OPAL_QUESTIONNAIRE_CONTROL_TABLE","QuestionnaireControl");
define("OPAL_FILTERS_TABLE","Filters");
define("OPAL_FILTERS_MODIFICATION_HISTORY_TABLE","FiltersMH");
define("OPAL_FREQUENCY_EVENTS_TABLE","FrequencyEvents");

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
    Filters.FilterType AS type,
    Filters.FilterId AS id,
    1 AS added
	FROM 
	".OPAL_QUESTIONNAIRE_CONTROL_TABLE.", 
	".OPAL_FILTERS_TABLE." 
	WHERE 
    QuestionnaireControl.QuestionnaireControlSerNum = :QuestionnaireControlSerNum
    AND Filters.ControlTable = 'LegacyQuestionnaireControl'
    AND Filters.ControlTableSerNum = QuestionnaireControl.QuestionnaireControlSerNum
    AND Filters.FilterType != ''
    AND Filters.FilterId != '';"
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
    QuestionnaireControl.QuestionnaireName_EN = :QuestionnaireName_EN, 
    QuestionnaireControl.QuestionnaireName_FR = :QuestionnaireName_FR,
    QuestionnaireControl.LastUpdatedBy = :LastUpdatedBy,
    QuestionnaireControl.SessionId = :SessionId
    WHERE 
    QuestionnaireControl.QuestionnaireControlSerNum = :QuestionnaireControlSerNum;"
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