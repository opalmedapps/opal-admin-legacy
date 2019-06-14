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
define("OPAL_QUESTIONNAIRE_CONTROL_TABLE","questionnairecontrol");
define("OPAL_FILTERS_TABLE","Filters");

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
    qc.QuestionnaireName_EN AS name_FR,
    qc.QuestionnaireName_FR AS name_EN,
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
    qc.QuestionnaireControlSerNum = :questionnaireControlId
    AND f.ControlTable = 'LegacyQuestionnaireControl'
    AND f.ControlTableSerNum = qc.QuestionnaireControlSerNum
    AND f.FilterType != ''
    AND f.FilterId != '';"
);

define("SQL_OPAL_UPDATE_PUBLISHED_QUESTIONNAIRES_STATUS",
    "UPDATE ".QUESTIONNAIRE_TABLE."
    SET updatedBy = :updatedBy, lastUpdated = NOW()
    WHERE ID = :ID
    AND (private = 0 OR OAUserId = :OAUserId)
    AND deleted = ".NON_DELETED_RECORD.";"
);