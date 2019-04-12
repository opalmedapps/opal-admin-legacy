<?php
/**
 * User: Dominic Bourdua
 * Date: 4/11/2019
 * Time: 8:54 AM
 */

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
define("DELETED_RECORD", 1);

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

/*
 * Listing of all SQL queries for the questionnaire database
 * */
define("SQL_QUESTIONNAIRE_FETCH_ALL_QUESTIONS",
    "SELECT
    q.ID AS ID,
    (SELECT d.content FROM dictionary d WHERE d.contentId = q.question AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = q.question AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR,
    q.private,
    q.typeId AS answertype_Id,
    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS answertype_name_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS answertype_name_FR,
    q.final
    FROM ".QUESTION_TABLE." q
    LEFT JOIN ".TYPE_TABLE." t ON t.ID = q.typeId
    WHERE deleted = 0 AND (OAUserId = :userId OR private = 0);"
);

define("SQL_QUESTIONNAIRE_FETCH_LIBRARIES_QUESTION",
    "SELECT
    (SELECT d.content FROM dictionary d WHERE d.contentId = l.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = l.name AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR
    FROM ".LIBRARY_TABLE." l
    RIGHT JOIN ".LIBRARY_QUESTION_TABLE." lq ON lq.libraryId = l.ID
    WHERE lq.questionId = :questionId AND (OAUserId = :userId OR private = 0)"
);

define("SQL_QUESTIONNAIRE_FETCH_QUESTIONNAIRES_ID_QUESTION",
    "SELECT DISTINCT qst.ID AS ID
    FROM ".QUESTIONNAIRE_TABLE." qst
    RIGHT JOIN section s ON s.questionnaireId = qst.ID
    RIGHT JOIN ".QUESTION_SECTION_TABLE." qs ON qs.sectionId = s.ID
    WHERE qs.questionId = :questionId AND (OAUserId = :userId OR private = 0)"
);

define("SQL_QUESTIONNAIRE_GET_QUESTION_TYPES",
    "SELECT
    tt.ID AS serNum, t.ID as typeSerNum,
    (SELECT d.content FROM dictionary d WHERE d.contentId = tt.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = tt.name AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR,
    tt.private,
    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS category_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS category_FR,
    tts.minValue,
    tts.maxValue,
    tts.increment,
    (SELECT d.content FROM dictionary d WHERE d.contentId = tts.minCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS minCaption_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = tts.minCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS minCaption_FR,
    (SELECT d.content FROM dictionary d WHERE d.contentId = tts.maxCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS maxCaption_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = tts.maxCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS maxCaption_FR,
    dt1.name AS tableName,
    dt2.name AS subTableName,
    tt.OAUserId AS created_by
    FROM ".TYPE_TEMPLATE_TABLE." tt
    LEFT JOIN type t ON t.ID = tt.typeId
    LEFT JOIN ".DEFINITION_TABLE." dt1 ON dt1.ID = t.templateTableId
    LEFT JOIN ".DEFINITION_TABLE." dt2 ON dt2.ID = t.templateSubTableId
    LEFT JOIN ".TYPE_TEMPLATE_SLIDER_TABLE." tts ON tts.typeTemplateId = tt.ID
    WHERE tt.private = 0 OR tt.OAUserId = :userId;"
);

define("SQL_QUESTIONNAIRE_GET_QUESTION_TYPE_OPTIONS",
    "SELECT st.*,
    (SELECT d.content FROM dictionary d WHERE d.contentId = st.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS text_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = st.description AND d.languageId = ".FRENCH_LANGUAGE.") AS text_FR
    FROM %%SUBTABLENAME%% st WHERE parentTableId = :subTableId ORDER BY st.order;"
);

define("SQL_QUESTIONNAIRE_GET_QUESTION_TYPES_CATEGORIES",
    "SELECT
    ID,
    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS category_EN,
    (SELECT d.content FROM dictionary d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS category_FR
    FROM ".TYPE_TABLE." t;"
);

define("SQL_QUESTIONNAIRE_GET_LAST_TIME_TABLE_UPDATED",
    "SELECT lastUpdated, updatedBy FROM %%TABLENAME%% WHERE ID = :ID;"
);

define("SQL_QUESTIONNAIRE_CAN_RECORD_BE_UPDATED",
    "SELECT COUNT(*) AS total
    FROM %%TABLENAME%%
    WHERE ID = :tableId AND lastUpdated = :lastUpdated AND updatedBy = :updatedBy;"
);

define("SQL_QUESTIONNAIRE_MARK_RECORD_AS_DELETED",
    "UPDATE %%TABLENAME%% SET deleted = ".DELETED_RECORD.", deletedBy = :username, updatedBy = :username
    WHERE ID = :recordId AND (OAUserId = :userId OR private = 0);"
);

define("SQL_QUESTIONNAIRE_GET_DICTIONARY_NEXT_CONTENT_ID",
    "SELECT COALESCE(MAX(contentId) + 1, 1) AS nextContentId FROM ".DICTIONARY_TABLE.";"
);

define("SQL_QUESTIONNAIRE_GET_ALL_LANGUAGE",
    "SELECT * FROM ".LANGUAGE_TABLE. ";"
);

define("SQL_QUESTIONNAIRE_GET_DEFINITION_TABLE_ID",
    "SELECT ID FROM ".DEFINITION_TABLE." WHERE name = :tableName"
);