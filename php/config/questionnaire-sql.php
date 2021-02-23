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

//Definition of all questionnaires table from the questionnaire DB
define("ANSWER_CHECKBOX_TABLE","answerCheckbox");
define("ANSWER_QUESTIONNAIRE_TABLE","answerQuestionnaire");
define("ANSWER_RADIO_BUTTON_TABLE","answerRadioButton");
define("ANSWER_SECTION_TABLE","answerSection");
define("ANSWER_SLIDER_TABLE","answerSlider");
define("ANSWER_TABLE","answer");
define("ANSWER_TEXT_BOX_TABLE","answerTextBox");
define("CHECKBOX_OPTION_TABLE","checkboxOption");
define("CHECKBOX_TABLE","checkbox");
define("DATE_TABLE","date");
define("DEFINITION_TABLE","definitionTable");
define("DICTIONARY_TABLE","dictionary");
define("LABEL_TABLE","label");
define("LABEL_OPTION_TABLE","labelOption");
define("PURPOSE_TABLE","purpose");
define("RESPONDENT_TABLE","respondent");
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
define("TAG_QUESTION_TABLE","tagQuestion");
define("TEMPLATE_QUESTION_TABLE","templateQuestion");
define("TEMPLATE_QUESTION_CHECKBOX_TABLE","templateQuestionCheckbox");
define("TEMPLATE_QUESTION_CHECKBOX_OPTION_TABLE","templateQuestionCheckboxOption");
define("TEMPLATE_QUESTION_DATE","templateQuestionDate");
define("TEMPLATE_QUESTION_LABEL_TABLE","templateQuestionLabel");
define("TEMPLATE_QUESTION_LABEL_OPTION_TABLE","templateQuestionLabelOption");
define("TEMPLATE_QUESTION_RADIO_BUTTON_TABLE","templateQuestionRadioButton");
define("TEMPLATE_QUESTION_RADIO_BUTTON_OPTION_TABLE","templateQuestionRadioButtonOption");
define("TEMPLATE_QUESTION_DATE_TABLE","templateQuestionDate");
define("TEMPLATE_QUESTION_SLIDER_TABLE","templateQuestionSlider");
define("TEMPLATE_QUESTION_TEXTBOX_TABLE","templateQuestionTextBox");
define("TEMPLATE_QUESTION_TEXT_BOX_TRIGGER","templateQuestionTextBoxTrigger");
define("TEMPLATE_QUESTION_TIME_TABLE","templateQuestionTime");
define("TEXT_BOX_TABLE","textBox");
define("TEXT_BOX_TRIGGER_TABLE","textBoxTrigger");
define("TIME_TABLE","time");
define("TYPE_TABLE","type");

/*
 * Listing of all SQL queries for the questionnaire database
 * */
define("SQL_QUESTIONNAIRE_FETCH_ALL_QUESTIONS",
    "SELECT
    q.ID AS ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.question AND d.languageId = ".ENGLISH_LANGUAGE.") AS question_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.question AND d.languageId = ".FRENCH_LANGUAGE.") AS question_FR,
    q.private,
    q.typeId AS typeId,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS questionType_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS questionType_FR,
    q.final
    FROM ".QUESTION_TABLE." q
    LEFT JOIN ".TYPE_TABLE." t ON t.ID = q.typeId
    WHERE q.deleted = ".NON_DELETED_RECORD." AND (OAUserId = :OAUserId OR private = 0);"
);

define("SQL_QUESTIONNAIRE_FETCH_QUESTIONS_BY_ID",
    "SELECT
    q.ID,
    q.private
    FROM ".QUESTION_TABLE." q
    WHERE q.ID IN (%%LISTIDS%%) AND q.deleted = ".NON_DELETED_RECORD." AND (OAUserId = :OAUserId OR private = ".PUBLIC_RECORD.") AND q.final = ".FINAL_RECORD.";"
);

define("SQL_QUESTIONNAIRE_COUNT_PRIVATE_QUESTIONS",
    "SELECT COUNT(*) AS total
    FROM ".QUESTION_TABLE." q
    WHERE q.ID IN (%%LISTIDS%%) AND q.deleted = ".NON_DELETED_RECORD." AND private = ".PRIVATE_RECORD.";"
);

define("SQL_QUESTIONNAIRE_FETCH_LIBRARIES_QUESTION",
    "SELECT l.ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = l.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = l.name AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR
    FROM ".LIBRARY_TABLE." l
    RIGHT JOIN ".LIBRARY_QUESTION_TABLE." lq ON lq.libraryId = l.ID
    WHERE lq.questionId = :questionId AND (OAUserId = :OAUserId OR private = 0) AND l.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_FETCH_QUESTIONNAIRES_ID_QUESTION",
    "SELECT DISTINCT qst.ID AS ID
    FROM ".QUESTIONNAIRE_TABLE." qst
    RIGHT JOIN section s ON s.questionnaireId = qst.ID
    RIGHT JOIN ".QUESTION_SECTION_TABLE." qs ON qs.sectionId = s.ID
    WHERE qs.questionId = :questionId AND (OAUserId = :OAUserId OR private = 0) AND qst.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTIONS",
    "SELECT
    tt.ID, t.ID as typeId,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = tt.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = tt.name AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR,
    tt.private,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS category_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS category_FR,
    tts.minValue,
    tts.maxValue,
    tts.increment,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = tts.minCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS minCaption_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = tts.minCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS minCaption_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = tts.maxCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS maxCaption_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = tts.maxCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS maxCaption_FR,
    dt1.name AS tableName,
    dt2.name AS subTableName,
    tt.OAUserId
    FROM ".TEMPLATE_QUESTION_TABLE." tt
    LEFT JOIN type t ON t.ID = tt.typeId
    LEFT JOIN ".DEFINITION_TABLE." dt1 ON dt1.ID = t.templateTableId
    LEFT JOIN ".DEFINITION_TABLE." dt2 ON dt2.ID = t.templateSubTableId
    LEFT JOIN ".TEMPLATE_QUESTION_SLIDER_TABLE." tts ON tts.templateQuestionId = tt.ID
    WHERE tt.typeId IN (1, 2, 3, 4) AND (tt.private = 0 OR tt.OAUserId = :OAUserId) AND tt.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_DETAILS",
    "SELECT
    tt.ID,
    t.ID AS typeId,
    tt.name,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = tt.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = tt.name AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR,
    tt.private,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS category_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS category_FR,
    dt1.name AS tableName,
    dt2.name AS subTableName,
    tt.OAUserId
    FROM ".TEMPLATE_QUESTION_TABLE." tt
    LEFT JOIN type t ON t.ID = tt.typeId
    LEFT JOIN ".DEFINITION_TABLE." dt1 ON dt1.ID = t.templateTableId
    LEFT JOIN ".DEFINITION_TABLE." dt2 ON dt2.ID = t.templateSubTableId
    WHERE tt.ID = :ID AND (tt.private = 0 OR tt.OAUserId = :OAUserId) AND tt.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_OPTIONS",
    "SELECT st.*,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = st.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS description_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = st.description AND d.languageId = ".FRENCH_LANGUAGE.") AS description_FR
    FROM %%SUBTABLENAME%% st WHERE parentTableId = :subTableId ORDER BY st.order;"
);

define("SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTIONS_CATEGORIES",
    "SELECT
    ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS category_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS category_FR
    FROM ".TYPE_TABLE." t WHERE t.ID IN (1, 2, 3, 4);"
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
    WHERE ID = :recordId AND (OAUserId = :OAUserId OR private = 0) AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_MARK_RECORD_AS_DELETED_NO_USER",
    "UPDATE %%TABLENAME%% SET deleted = ".DELETED_RECORD.", deletedBy = :username, updatedBy = :username
    WHERE ID = :recordId AND deleted = ".NON_DELETED_RECORD.";"
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

define("SQL_QUESTIONNAIRE_GET_ID_FROM_TEMPLATE_TYPES_OPTION",
    "SELECT DISTINCT ID FROM %%TABLENAME%% WHERE templateQuestionId = :ID;"
);

define("SQL_QUESTIONNAIRE_GET_ALL_LIBRARIES",
    "SELECT 
    l.ID AS serNum,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = l.name AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = l.name AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR
    FROM ".LIBRARY_TABLE." l
    WHERE l.deleted = ".NON_DELETED_RECORD." AND (l.OAUserId = :OAUserId OR l.private = 0);"
);

define("SQL_QUESTIONNAIRE_GET_USER_LIBRARIES",
    "SELECT 
    l.ID
    FROM ".LIBRARY_TABLE." l
    WHERE l.deleted = ".NON_DELETED_RECORD." AND (l.OAUserId = :OAUserId OR l.private = 0) AND l.ID IN (%%LISTOFIDS%%);"
);

define("SQL_QUESTIONNAIRE_DELETE_LIBRARY_QUESTION",
    "DELETE lq FROM ".LIBRARY_QUESTION_TABLE." lq
    LEFT JOIN ".LIBRARY_TABLE." l ON l.id = lq.libraryId
    WHERE lq.questionId = :questionId
    AND ((l.OAUserId = :OAUserId AND l.private = 1) OR l.private = 0)
    AND lq.libraryId NOT IN (%%LIBRARYIDS%%)"
);

define("SQL_QUESTIONNAIRE_DELETE_ALL_LIBRARIES_QUESTION",
    "DELETE lq FROM ".LIBRARY_QUESTION_TABLE." lq
    LEFT JOIN ".QUESTION_TABLE." q ON q.id = lq.questionId
    WHERE lq.questionId = :questionId
    AND (q.OAUserId = :OAUserId OR q.private = 0)"
);

define("SQL_QUESTIONNAIRE_DELETE_ALL_SECTIONS_QUESTION",
    "DELETE qs FROM ".QUESTION_SECTION_TABLE." qs
    LEFT JOIN ".QUESTION_TABLE." q ON q.id = qs.questionId
    WHERE qs.questionId = :questionId
    AND (q.OAUserId = :OAUserId OR q.private = 0)"
);

define("SQL_QUESTIONNAIRE_DELETE_ALL_TAGS_QUESTION",
    "DELETE tq FROM ".TAG_QUESTION_TABLE." tq
    LEFT JOIN ".QUESTION_TABLE." q ON q.id = tq.questionId
    WHERE tq.questionId = :questionId
    AND (q.OAUserId = :OAUserId OR q.private = 0)"
);

define("SQL_QUESTIONNAIRE_DELETE_QUESTION_OPTIONS",
    "DELETE top FROM %%TABLENAME%% top
    LEFT JOIN %%PARENTTABLE%% pt ON pt.id = top.parentTableId
    LEFT JOIN %%GRANDPARENTTABLE%% gpt ON gpt.id = pt.%%GRANDPARENTFIELDNAME%%
    WHERE top.parentTableId = :parentTableId
    AND (gpt.OAUserId = :OAUserId OR gpt.private = 0)
    AND top.ID NOT IN (%%OPTIONIDS%%);"
);

define("SQL_QUESTIONNAIRE_DELETE_QUESTION_SECTION",
    "DELETE qs FROM ".QUESTION_SECTION_TABLE." qs
    LEFT JOIN ".SECTION_TABLE." s ON s.ID = qs.sectionId
    LEFT JOIN ".QUESTIONNAIRE_TABLE." q ON q.id = s.questionnaireId
    WHERE qs.sectionId = :sectionId
    AND (q.OAUserId = :OAUserId OR q.private = 0)
    AND qs.questionId NOT IN (%%OPTIONIDS%%);"
);

define("SQL_QUESTIONNAIRE_SELECT_OPTIONS_TO_BE_DELETED",
    "SELECT top.description FROM %%TABLENAME%% top
    LEFT JOIN %%PARENTTABLE%% pt ON pt.id = top.parentTableId
    LEFT JOIN %%GRANDPARENTTABLE%% gpt ON gpt.id = pt.%%GRANDPARENTFIELDNAME%%
    WHERE top.parentTableId = :parentTableId
    AND (gpt.OAUserId = :OAUserId OR gpt.private = 0)
    AND top.ID NOT IN (%%OPTIONIDS%%);"
);

define("SQL_QUESTIONNAIRE_MARK_DICTIONARY_RECORD_AS_DELETED",
    "UPDATE ".DICTIONARY_TABLE." SET deleted = ".DELETED_RECORD.", deletedBy = :username, updatedBy = :username
    WHERE contentId = :contentId AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_TYPE_TEMPLATE",
    "SELECT
    tt.*,
    tts.minValue,
    tts.maxValue,
    tts.minCaption,
    tts.maxCaption,
    ttc.ID AS ttcID,
    ttc.minAnswer,
    ttc.maxAnswer,
    ttr.ID AS ttrID,
    tts.increment,
    dt1.name AS tableName,
    dt2.name AS subTableName
    FROM ".TEMPLATE_QUESTION_TABLE." tt
    LEFT JOIN ".TYPE_TABLE." t ON t.ID = tt.typeId
    LEFT JOIN ".DEFINITION_TABLE." dt1 ON dt1.ID = t.tableId
    LEFT JOIN ".DEFINITION_TABLE." dt2 ON dt2.ID = t.subTableId
    LEFT JOIN ".TEMPLATE_QUESTION_SLIDER_TABLE." tts ON tts.templateQuestionId = tt.ID
    LEFT JOIN ".TEMPLATE_QUESTION_CHECKBOX_TABLE." ttc ON ttc.templateQuestionId = tt.ID
    LEFT JOIN ".TEMPLATE_QUESTION_RADIO_BUTTON_TABLE." ttr ON ttr.templateQuestionId = tt.ID
    WHERE tt.ID = :ID AND (tt.private = 0 OR tt.OAUserId = :OAUserId) AND tt.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_LIBRARY",
    "SELECT * FROM ".LIBRARY_TABLE." l WHERE ID = :ID AND (private = 0 OR OAUserId = :OAUserId);"
);

define("SQL_QUESTIONNAIRE_GET_LIBRARIES",
    "SELECT * FROM ".LIBRARY_TABLE." l WHERE ID IN (%%LIBRARIES_ID%%);"
);

define("SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_CHECKBOX_OPTION",
    "SELECT * FROM " . TEMPLATE_QUESTION_CHECKBOX_OPTION_TABLE . " WHERE parentTableID = :parentTableID;"
);

define("SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_RADIO_BUTTON_OPTION",
    "SELECT * FROM " . TEMPLATE_QUESTION_RADIO_BUTTON_OPTION_TABLE . " WHERE parentTableID = :parentTableID;"
);

define("SQL_QUESTIONNAIRE_GET_DICTIONNARY_TEXT",
    "SELECT * FROM ".DICTIONARY_TABLE." WHERE contentId = :contentId;"
);

define("SQL_QUESTIONNAIRE_GET_LEGACY_TYPE",
    "SELECT * FROM ".LEGACY_TYPE_TABLE." WHERE typeId = :typeId; AND default = 1"
);

define("SQL_QUESTIONNAIRE_GET_QUESTION_DETAILS",
    "SELECT
    q.ID,
    q.display,
    q.definition,
    q.question,
    q.private,
    q.OAUserId AS OAUserId,
    q.question,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.question AND d.languageId = ".ENGLISH_LANGUAGE.") AS question_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.question AND d.languageId = ".FRENCH_LANGUAGE.") AS question_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.display AND d.languageId = ".ENGLISH_LANGUAGE.") AS display_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.display AND d.languageId = ".FRENCH_LANGUAGE.") AS display_FR,
    q.typeId,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS type_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS type_FR,
    q.final,
    q.createdBy,
    dt1.name AS tableName,
    dt2.name AS subTableName
    FROM ".QUESTION_TABLE." q
    LEFT JOIN ".TYPE_TABLE." t ON t.ID = q.typeId
    LEFT JOIN ".DEFINITION_TABLE." dt1 ON dt1.ID = t.tableId
    LEFT JOIN ".DEFINITION_TABLE." dt2 ON dt2.ID = t.subTableId
    WHERE q.ID = :ID AND (q.private = 0 OR q.OAUserId = :OAUserId) AND q.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_QUESTIONS_BY_SECTION_ID",
    "SELECT
    q.*,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.question AND d.languageId = ".ENGLISH_LANGUAGE.") AS question_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.question AND d.languageId = ".FRENCH_LANGUAGE.") AS question_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS type_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS type_FR,
    qs.order,
    qs.optional,
    dt1.name AS tableName,
    dt2.name AS subTableName
    FROM ".QUESTION_TABLE." q
    LEFT JOIN ".QUESTION_SECTION_TABLE." qs ON q.ID = qs.questionId
    LEFT JOIN ".TYPE_TABLE." t ON t.ID = q.typeId
    LEFT JOIN ".DEFINITION_TABLE." dt1 ON dt1.ID = t.tableId
    LEFT JOIN ".DEFINITION_TABLE." dt2 ON dt2.ID = t.subTableId
    WHERE qs.sectionID = :sectionId AND (q.private = ".PUBLIC_RECORD." OR q.OAUserId = :OAUserId) AND q.deleted = ".NON_DELETED_RECORD."
    ORDER BY qs.order;"
);

define("SQL_QUESTIONNAIRE_GET_FINALIZED_QUESTIONS",
    "SELECT
    q.ID,
    q.question,
    q.private,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.question AND d.languageId = ".ENGLISH_LANGUAGE.") AS question_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.question AND d.languageId = ".FRENCH_LANGUAGE.") AS question_FR,
    q.typeId,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS type_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS type_FR,
    q.final,
    dt1.name AS tableName,
    dt2.name AS subTableName
    FROM ".QUESTION_TABLE." q
    LEFT JOIN ".TYPE_TABLE." t ON t.ID = q.typeId
    LEFT JOIN ".DEFINITION_TABLE." dt1 ON dt1.ID = t.tableId
    LEFT JOIN ".DEFINITION_TABLE." dt2 ON dt2.ID = t.subTableId
    WHERE q.final = 1 AND (q.private = 0 OR q.OAUserId = :OAUserId) AND q.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_QUESTIONNAIRE_DETAILS",
    "SELECT q.*,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS title_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".FRENCH_LANGUAGE.") AS title_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS description_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.description AND d.languageId = ".FRENCH_LANGUAGE.") AS description_FR,
    q.purposeId AS purpose, q.respondentId AS respondent 
    FROM ".QUESTIONNAIRE_TABLE." q
    WHERE q.ID = :ID AND (q.private = 0 OR q.OAUserId = :OAUserId) AND q.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_SECTION_BY_QUESTIONNAIRE_ID",
    "SELECT s.*
    FROM ".SECTION_TABLE." s
    WHERE s.questionnaireId = :questionnaireId AND s.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_GET_QUESTION_OPTIONS",
    "SELECT *
    FROM %%TABLENAME%%
    WHERE %%FIELDNAME%% = :fieldId;"
);

define("SQL_QUESTIONNAIRE_GET_QUESTION_SLIDER_OPTIONS",
    "SELECT s.*,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = s.minCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS minCaption_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = s.minCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS minCaption_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = s.maxCaption AND d.languageId = ".ENGLISH_LANGUAGE.") AS maxCaption_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = s.maxCaption AND d.languageId = ".FRENCH_LANGUAGE.") AS maxCaption_FR
    FROM %%TABLENAME%% s
    WHERE %%FIELDNAME%% = :fieldId;"
);

define("SQL_QUESTIONNAIRE_GET_QUESTION_SUB_OPTIONS",
    "SELECT t.*,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS description_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = t.description AND d.languageId = ".FRENCH_LANGUAGE.") AS description_FR
    FROM %%TABLENAME%% t
    WHERE parentTableId = :parentTableId ORDER BY t.order;"
);

define("SQL_QUESTIONNAIRE_GET_QUESTION_TOTAL_SUB_OPTIONS",
    "SELECT COUNT(*) AS total FROM %%TABLENAME%% t
    WHERE parentTableId = :parentTableId;"
);

define("SQL_QUESTIONNAIRE_GET_QUESTIONNAIRE_NAMES",
    "SELECT
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS title_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".FRENCH_LANGUAGE.") AS title_FR
    FROM ".QUESTIONNAIRE_TABLE." q
    WHERE q.ID = :questionnaireId;"
);

define("SQL_QUESTIONNAIRE_GET_TEMPLATE_QUESTION_TOTAL_SUB_OPTIONS",
    "SELECT COUNT(*) AS total FROM %%TABLENAME%% t
    WHERE parentTableId = :parentTableId;"
);

define("SQL_QUESTIONNAIRE_UPDATE_DICTIONARY",
    "UPDATE " . DICTIONARY_TABLE . "
    SET content = :content, updatedBy = :updatedBy
    WHERE contentId = :contentId
    AND languageId = :languageId
    AND content != :content
    AND tableId = :tableId
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_QUESTION",
    "UPDATE ".QUESTION_TABLE."
    SET updatedBy = :updatedBy, private = :private, final = :final
    WHERE ID = :ID
    AND (private = ".PUBLIC_RECORD." OR OAUserId = :OAUserId)
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_TYPE_TEMPLATE",
    "UPDATE ".TEMPLATE_QUESTION_TABLE."
    SET updatedBy = :updatedBy, private = :private
    WHERE ID = :ID
    AND (private = 0 OR OAUserId = :OAUserId)
    AND (private != :private) 
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_FORCE_UPDATE_UPDATEDBY",
    "UPDATE %%TABLENAME%%
    SET updatedBy = :updatedBy, lastUpdated = NOW()
    WHERE ID = :ID
    AND (private = 0 OR OAUserId = :OAUserId)
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_QUESTIONNAIRE",
    "UPDATE ".QUESTIONNAIRE_TABLE."
    SET updatedBy = :updatedBy, private = :private, final = :final, visualization = :visualization,
    purposeId = :purposeId, respondentId = :respondentId
    WHERE ID = :ID
    AND (private = ".PUBLIC_RECORD." OR OAUserId = :OAUserId)
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_UPDATEDBY_QUESTIONNAIRE",
    "UPDATE ".QUESTIONNAIRE_TABLE."
    SET updatedBy = :updatedBy, lastUpdated = NOW()
    WHERE ID = :ID
    AND (private = 0 OR OAUserId = :OAUserId)
    AND deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_QUESTION_OPTIONS",
    "UPDATE %%TABLENAME%% tb
    LEFT JOIN ".QUESTION_TABLE." q ON q.id = tb.questionId
    SET %%OPTIONSTOUPDATE%%
    WHERE tb.ID = :ID
    AND (%%OPTIONSWEREUPDATED%%)
    AND (q.OAUserId = :OAUserId OR q.private = 0)
    AND q.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_QUESTION_SUB_OPTIONS",
    "UPDATE %%TABLENAME%% tb
    LEFT JOIN %%PARENTTABLE%% pt ON pt.id = tb.parentTableId
    LEFT JOIN ".QUESTION_TABLE." q ON q.id = pt.questionId
    SET %%OPTIONSTOUPDATE%%
    WHERE tb.ID = :ID
    AND (%%OPTIONSWEREUPDATED%%)
    AND (q.OAUserId = :OAUserId OR q.private = 0)
    AND q.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_TEMPLATE_QUESTION_OPTIONS",
    "UPDATE %%TABLENAME%% tb
    LEFT JOIN ".TEMPLATE_QUESTION_TABLE." q ON q.id = tb.templateQuestionId
    SET %%OPTIONSTOUPDATE%%
    WHERE tb.ID = :ID
    AND (%%OPTIONSWEREUPDATED%%)
    AND (q.OAUserId = :OAUserId OR q.private = 0)
    AND q.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_TEMPLATE_QUESTION_SUB_OPTIONS",
    "UPDATE %%TABLENAME%% tb
    LEFT JOIN %%PARENTTABLE%% pt ON pt.id = tb.parentTableId
    LEFT JOIN ".TEMPLATE_QUESTION_TABLE." q ON q.id = pt.templateQuestionId
    SET %%OPTIONSTOUPDATE%%
    WHERE tb.ID = :ID
    AND (%%OPTIONSWEREUPDATED%%)
    AND (q.OAUserId = :OAUserId OR q.private = 0)
    AND q.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_UPDATE_QUESTION_SECTION",
    "UPDATE ".QUESTION_SECTION_TABLE." qst
    LEFT JOIN ".SECTION_TABLE." s ON s.id = qst.sectionId
    LEFT JOIN ".QUESTIONNAIRE_TABLE." q ON q.id = s.questionnaireId
    SET %%OPTIONSTOUPDATE%%
    WHERE qst.sectionId = :sectionId
    AND qst.questionId = :questionId
    AND (%%OPTIONSWEREUPDATED%%)
    AND (q.OAUserId = :OAUserId OR q.private = 0)
    AND q.deleted = ".NON_DELETED_RECORD.";"
);

define("SQL_QUESTIONNAIRE_FETCH_ALL_QUESTIONNAIRES",
    "SELECT
    q.ID AS ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR,
    q.private,
    q.final AS publish,
    q.createdBy AS created_by
    FROM ".QUESTIONNAIRE_TABLE." q
    WHERE q.deleted = ".NON_DELETED_RECORD." AND (OAUserId = :OAUserId OR private = 0);"
);
define("SQL_QUESTIONNAIRE_FETCH_ALL_QUESTIONNAIRES",
    "SELECT
    q.ID AS ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR,
    q.private,
    q.final AS publish,
    q.createdBy AS created_by
    FROM ".QUESTIONNAIRE_TABLE." q
    WHERE q.deleted = ".NON_DELETED_RECORD." AND (OAUserId = :OAUserId OR private = 0);"
);

define("SQL_QUESTIONNAIRE_FETCH_ALL_FINAL_QUESTIONNAIRES",
    "SELECT
    q.ID AS ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS name_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = q.title AND d.languageId = ".FRENCH_LANGUAGE.") AS name_FR,
    q.private,
    q.final,
    q.createdBy AS created_by
    FROM ".QUESTIONNAIRE_TABLE." q
    WHERE q.final = ".FINAL_RECORD." AND q.deleted = ".NON_DELETED_RECORD." AND (OAUserId = :OAUserId OR private = 0);"
);

define("SQL_QUESTIONNAIRE_UPDATE_LAST_CHECKBOX_OPTION",
    "UPDATE dictionary d RIGHT JOIN %%TABLENAME%% tn ON d.contentId = tn.description SET d.content = :content WHERE tn.parentTableId = :parentTableId AND d.languageID = :languageID AND tn.order = (SELECT MAX(tn.order) FROM %%TABLENAME%% tn WHERE tn.parentTableId = :parentTableId) and d.content != :content;"
);

define("SQL_QUESTIONNAIRE_CONDITIONAL_INSERT","
    SELECT %%VALUES%% FROM DUAL WHERE NOT EXISTS (SELECT * FROM ".DICTIONARY_TABLE." WHERE contentId = :controlContentId)
");

define("SQL_QUESTIONNAIRE_GET_PURPOSES",
    "SELECT
    p.ID AS ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = p.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS title_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = p.title AND d.languageId = ".FRENCH_LANGUAGE.") AS title_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = p.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS description_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = p.description AND d.languageId = ".FRENCH_LANGUAGE.") AS description_FR
    FROM ".PURPOSE_TABLE." p ORDER BY p.ID;"
);

define("SQL_QUESTIONNAIRE_GET_RESPONDENTS",
    "SELECT
    r.ID AS ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = r.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS title_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = r.title AND d.languageId = ".FRENCH_LANGUAGE.") AS title_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = r.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS description_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = r.description AND d.languageId = ".FRENCH_LANGUAGE.") AS description_FR
    FROM ".RESPONDENT_TABLE." r ORDER BY r.ID;"
);

define("SQL_QUESTIONNAIRE_GET_PURPOSE_DETAILS",
    "SELECT
    p.ID AS ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = p.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS title_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = p.title AND d.languageId = ".FRENCH_LANGUAGE.") AS title_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = p.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS description_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = p.description AND d.languageId = ".FRENCH_LANGUAGE.") AS description_FR
    FROM ".PURPOSE_TABLE." p WHERE p.ID = :ID;"
);

define("SQL_QUESTIONNAIRE_GET_RESPONDENT_DETAILS",
    "SELECT
    r.ID AS ID,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = r.title AND d.languageId = ".ENGLISH_LANGUAGE.") AS title_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = r.title AND d.languageId = ".FRENCH_LANGUAGE.") AS title_FR,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = r.description AND d.languageId = ".ENGLISH_LANGUAGE.") AS description_EN,
    (SELECT d.content FROM ".DICTIONARY_TABLE." d WHERE d.contentId = r.description AND d.languageId = ".FRENCH_LANGUAGE.") AS description_FR
    FROM ".RESPONDENT_TABLE." r WHERE r.ID = :ID;"
);