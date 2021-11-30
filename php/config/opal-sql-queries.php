<?php
/*
 * Listing of all SQL queries for the Opal database
 * */
define("SQL_OPAL_SELECT_USER_INFO",
    "SELECT OAUserSerNum AS OAUserId,
    Username AS username,
    Language as language,
    oaRoleId as userRole,
    type
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
    SELECT DISTINCT PatientSerNum AS id, 'Patient' AS type, 0 AS added, CONCAT(CONCAT(UCASE(SUBSTRING(LastName, 1, 1)), LOWER(SUBSTRING(LastName, 2))), ', ', CONCAT(UCASE(SUBSTRING(FirstName, 1, 1)), LOWER(SUBSTRING(FirstName, 2)))) AS name
    FROM ".OPAL_PATIENT_TABLE." ORDER BY LastName;
");

define("OPAL_GET_MRN_PATIENT_SERNUM","
    SELECT MRN, Hospital_Identifier_Type_Code AS hospital FROM ".OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE." WHERE PatientSerNum = :PatientSerNum;
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
    SELECT DISTINCT ResourceAriaSer AS id, ResourceName AS name, 'Machine' AS 'type', 0 AS 'added' FROM ".OPAL_RESOURCE_TABLE."
    WHERE ResourceName LIKE 'STX%' OR  ResourceName LIKE 'TB%' ORDER BY ResourceName;
");

define("OPAL_GET_STUDIES_TRIGGERS","
    SELECT DISTINCT ID AS id, CONCAT (code, ' ', title_EN) AS name, 'Study' AS 'type', 0 AS 'added' FROM ".OPAL_STUDY_TABLE." WHERE deleted = ".NON_DELETED_RECORD." ORDER BY code, title_EN;
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

define("SQL_OPAL_VALIDATE_OAUSER_ACCESS","
    SELECT * FROM ".OPAL_OAUSER_TABLE." WHERE Username = :Username AND deleted = ".NON_DELETED_RECORD." AND type = " . HUMAN_USER . ";
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
    SELECT COUNT(*) AS total FROM ".OPAL_OAUSER_TABLE." WHERE Username = :Username AND deleted = ".NON_DELETED_RECORD."
");

define("OPAL_IS_USER_EXISTS","
    SELECT * FROM ".OPAL_OAUSER_TABLE." WHERE Username = :Username;
");

define("OPAL_UNDELETE_USER","
    UPDATE ".OPAL_OAUSER_TABLE." SET deleted = ".NON_DELETED_RECORD." WHERE Username = :Username;
");

define("OPAL_UPDATE_USER","
    UPDATE ".OPAL_OAUSER_TABLE." SET oaRoleId = :oaRoleId, type = :type, Language = :Language, Password = :Password,
    deleted = ".NON_DELETED_RECORD."
    WHERE Username = :Username;
");

define("OPAL_MARK_USER_AS_DELETED",
    "UPDATE ".OPAL_OAUSER_TABLE." SET deleted = ".DELETED_RECORD." WHERE OAUserSerNum = :recordId
    AND OAUserSerNum != :OAUserId AND deleted = ".NON_DELETED_RECORD.";
");

const OPAL_GET_OAUSERID = "SELECT OAUserSerNum AS ID FROM " . OPAL_OAUSER_TABLE . " WHERE Username = :Username;";

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
    SELECT ID, code, title_EN, title_FR, investigator, email, phone, phoneExt, startDate, endDate, creationDate FROM ".OPAL_STUDY_TABLE."
    WHERE deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_STUDY_DETAILS","
    SELECT ID, consentQuestionnaireId, code, title_EN, title_FR, description_EN, description_FR, investigator, email, phone, phoneExt, startDate, endDate FROM ".OPAL_STUDY_TABLE." WHERE ID = :ID AND deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_UPDATE_STUDY","
    UPDATE ".OPAL_STUDY_TABLE." SET code = :code, title_EN = :title_EN, title_FR = :title_FR, description_EN = :description_EN, description_FR = :description_FR, investigator = :investigator, phone = :phone, email = :email, phoneExt = :phoneExt, startDate = :startDate,
    endDate = :endDate, consentQuestionnaireId = :consentQuestionnaireId, updatedBy = :updatedBy WHERE ID = :ID AND deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_UPDATE_STUDY_CONSENT", "
    UPDATE ".OPAL_PATIENT_STUDY_TABLE." SET consentStatus = :patientConsent, patientId = :patientId WHERE studyId = :studyId AND patientId = :patientId;
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
    SELECT DISTINCT em.EducationalMaterialControlSerNum AS serial, em.EducationalMaterialType_EN AS type_EN, em.EducationalMaterialType_FR AS type_FR, em.Name_EN AS name_EN, em.Name_FR AS name_FR, em.URL_EN AS url_EN, em.URL_FR AS url_FR, phase.PhaseInTreatmentSerNum AS phase_serial, phase.Name_EN AS phase_EN, phase.Name_FR AS phase_FR, em.PublishFlag AS publish, em.ParentFlag AS parentFlag, em.ShareURL_EN AS share_url_EN, em.ShareURL_FR AS share_url_FR, em.LastUpdated AS lastupdated, (SELECT COUNT(*) AS locked FROM ".OPAL_FILTERS_TABLE." f WHERE f.ControlTableSerNum = em.EducationalMaterialControlSerNum and ControlTable = '".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE."') AS locked, (case WHEN em.ParentFlag = 1 then (SELECT COALESCE(round(AVG(emr.RatingValue)), 0) FROM EducationalMaterialRating emr WHERE emr.EducationalMaterialControlSerNum = em.EducationalMaterialControlSerNum) ELSE 0 END) AS rating FROM ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." em, ".OPAL_PHASE_IN_TREATMENT_TABLE." phase WHERE phase.PhaseInTreatmentSerNum = em.PhaseInTreatmentSerNum AND em.deleted = 0;
");

define("OPAL_GET_PUBLISHED_EDUCATIONAL_MATERIAL","
    SELECT DISTINCT em.EducationalMaterialControlSerNum AS serial, em.EducationalMaterialType_EN AS type_EN, em.EducationalMaterialType_FR AS type_FR, em.Name_EN AS name_EN, em.Name_FR AS name_FR, em.URL_EN AS url_EN, em.URL_FR AS url_FR, phase.PhaseInTreatmentSerNum AS phase_serial, phase.Name_EN AS phase_EN, phase.Name_FR AS phase_FR, em.PublishFlag AS publish, em.ParentFlag AS parentFlag, em.ShareURL_EN AS share_url_EN, em.ShareURL_FR AS share_url_FR, em.LastUpdated AS lastupdated, (SELECT COUNT(*) AS locked FROM ".OPAL_FILTERS_TABLE." f WHERE f.ControlTableSerNum = em.EducationalMaterialControlSerNum and ControlTable = '".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE."') AS locked, (case WHEN em.ParentFlag = 1 then (SELECT COALESCE(round(AVG(emr.RatingValue)), 0) FROM EducationalMaterialRating emr WHERE emr.EducationalMaterialControlSerNum = em.EducationalMaterialControlSerNum) ELSE 0 END) AS rating FROM ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." em, ".OPAL_PHASE_IN_TREATMENT_TABLE." phase WHERE phase.PhaseInTreatmentSerNum = em.PhaseInTreatmentSerNum AND em.deleted = 0;
");

define("OPAL_GET_TOCS_EDU_MATERIAL","
    SELECT DISTINCT em.EducationalMaterialControlSerNum AS serial, em.Name_EN AS name_EN, em.Name_FR AS name_FR, toc.OrderNum AS `order`, em.EducationalMaterialType_EN AS type_EN, em.EducationalMaterialType_FR AS type_FR, em.URL_EN AS url_EN, em.URL_FR AS url_FR FROM ".OPAL_EDUCATION_MATERIAL_TOC_TABLE." toc, ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." em WHERE toc.EducationalMaterialControlSerNum= em.EducationalMaterialControlSerNum AND toc.ParentSerNum = :ParentSerNum ORDER BY toc.OrderNum
");

define("OPAL_GET_EDU_MATERIAL_DETAILS","
    SELECT DISTINCT em.EducationalMaterialType_EN AS type_EN, em.EducationalMaterialType_FR AS type_FR, em.Name_EN AS name_EN, em.Name_FR AS name_FR, em.EducationalMaterialControlSerNum AS serial, em.PublishFlag AS publish, em.URL_EN AS url_EN, em.URL_FR AS url_FR, phase.PhaseInTreatmentSerNum AS phase_serial, phase.Name_EN AS phase_EN, phase.Name_FR AS phase_FR, em.ShareURL_EN AS share_url_EN, em.ShareURL_FR AS share_url_FR FROM ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." em, ".OPAL_PHASE_IN_TREATMENT_TABLE." phase WHERE em.EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum AND phase.PhaseInTreatmentSerNum = em.PhaseInTreatmentSerNum;
");

define("OPAL_GET_EDU_MATERIAL_MH","
    SELECT DISTINCT emc.Name_EN AS material_name, emmh.EducationalMaterialRevSerNum AS revision, emmh.CronLogSerNum AS cron_serial, emmh.PatientSerNum AS patient_serial, emmh.DateAdded AS date_added, emmh.ReadStatus AS read_status, emmh.ModificationAction AS mod_action FROM ".OPAL_EDUCATION_MATERIAL_MH_TABLE." emmh, ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." emc WHERE emc.EducationalMaterialControlSerNum = emmh.EducationalMaterialControlSerNum AND emmh.CronLogSerNum IN (%%LIST_IDS%%);
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
    SELECT ID, operation, name_EN, name_FR, description_EN, description_FR, iconClass, url, subModule, subModuleMenu FROM ".OPAL_MODULE_TABLE."
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
    SELECT DISTINCT d.SourceUID AS sourceuid, d.DiagnosisCode AS code, d.Description AS description,
    CONCAT(d.DiagnosisCode, ' (', d.Description, ')') AS name, 1 AS added FROM ".OPAL_DIAGNOSIS_CODE_TABLE." d
    LEFT JOIN ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." m ON m.code = d.DiagnosisCode AND m.description = d.Description
    WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum AND m.deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_ACTIVATE_SOURCE_DB","
    SELECT SourceDatabaseSerNum FROM ".OPAL_SOURCE_DATABASE_TABLE." WHERE Enabled = ".ACTIVE_RECORD."
");

define("OPAL_GET_ASSIGNED_DIAGNOSES","
    SELECT SourceUID, dxc.DiagnosisCode AS code, dxc.Description AS description, Source AS source, dxt.Name_EN AS name_EN, dxt.Name_FR AS name_FR FROM ".OPAL_DIAGNOSIS_CODE_TABLE." dxc
    LEFT JOIN ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." dxt ON dxt.DiagnosisTranslationSerNum = dxc.DiagnosisTranslationSerNum;
");

define("OPAL_GET_DIAGNOSES","
    SELECT ID, code, description, CONCAT(code, ' (', description, ')') AS name
    FROM ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." WHERE deleted = ".NON_DELETED_RECORD." AND source IN(%%SOURCE_DB_IDS%%) ORDER BY code
");

define("OPAL_GET_DIAGNOSIS_TRANSLATIONS","
    SELECT dt.DiagnosisTranslationSerNum AS serial, dt.Name_EN AS name_EN, dt.Name_FR AS name_FR,
    (SELECT COUNT(*) FROM DiagnosisCode dc WHERE dc.DiagnosisTranslationSerNum = dt.DiagnosisTranslationSerNum) AS `count`
    FROM ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." dt;
");

define("OPAL_VALIDATE_EDU_MATERIAL_ID","
    SELECT COUNT(*) AS total FROM ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE."
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
    DELETE dc FROM ".OPAL_DIAGNOSIS_CODE_TABLE." dc LEFT JOIN ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." msd ON msd.ID = dc.SourceUID
    WHERE dc.DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum AND msd.deleted = ".NON_DELETED_RECORD." AND
    dc.SourceUID NOT IN (%%LIST_SOURCES_UIDS%%);
");

define("OPAL_DELETE_ALL_DIAGNOSIS_CODES","
    DELETE FROM ".OPAL_DIAGNOSIS_CODE_TABLE." WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum;
");

define("OPAL_DELETE_DIAGNOSIS_TRANSLATION","
    DELETE FROM ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum;
");

define("OPAL_GET_TRIGGERS_LIST","
    SELECT sourceContentId, sourceModuleId, onCondition, eventType, targetContentId, targetModuleId FROM ".OPAL_TRIGGER_TABLE." WHERE active = ".ACTIVE_RECORD." AND sourceContentId = :sourceContentId AND sourceModuleId = :sourceModuleId;
");

define("OPAL_GET_PATIENT_SERNUM","
    SELECT getPatientSerNum(:patientId);
");

define("OPAL_GET_PATIENT_DIAGNOSIS","
    SELECT d.DiagnosisCode, d.Description_EN AS CodeDescription_EN, getDiagnosisDescription(d.DiagnosisCode,'EN') AS Description_EN,
    d.Description_FR AS CodeDescription_FR, getDiagnosisDescription(d.DiagnosisCode,'FR') AS Description_FR, d.CreationDate, d.LastUpdated
    FROM ".OPAL_DIAGNOSIS_TABLE." d RIGHT JOIN ".OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE." p ON p.PatientSerNum = d.PatientSerNum
    RIGHT JOIN ".OPAL_SOURCE_DATABASE_TABLE." s ON s.SourceDatabaseSerNum = d.SourceDatabaseSerNum WHERE p.MRN = :MRN
    AND p.Hospital_Identifier_Type_Code = :site AND p.Is_Active = ".ACTIVE_RECORD."
    %%SOURCE%%
    AND DATE(d.CreationDate) >= :startDate AND DATE(d.CreationDate) <= :endDate ORDER BY d.CreationDate DESC
");

define("OPAL_SOURCE_DATABASE","AND s.SourceDatabaseName %%OPERATOR%% :SourceDatabaseName");

define("OPAL_GET_PATIENT_SITE","
    SELECT * FROM ".OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE." WHERE Hospital_Identifier_Type_Code = :Hospital_Identifier_Type_Code
    AND MRN = :MRN AND Is_Active = ".ACTIVE_RECORD.";
");

define("OPAL_GET_SOURCE_DB_DETAILS","
    SELECT * FROM ".OPAL_SOURCE_DATABASE_TABLE." WHERE SourceDatabaseName = :SourceDatabaseName AND Enabled = ".ACTIVE_RECORD."
");

define("OPAL_GET_DIAGNOSIS_CODE_DETAILS","
    SELECT * FROM ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." msd
    LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." sd ON sd.SourceDatabaseSerNum = msd.source
    WHERE msd.code = :code AND sd.SourceDatabaseName = :SourceDatabaseName AND msd.externalId = :externalId
    AND msd.deleted = ".NON_DELETED_RECORD."
");

define("OPAL_GET_PATIENT_DIAGNOSIS_ID","
    SELECT DiagnosisSerNum FROM ".OPAL_DIAGNOSIS_TABLE." WHERE PatientSerNum = :PatientSerNum
    AND SourceDatabaseSerNum = :SourceDatabaseSerNum AND DiagnosisAriaSer = :DiagnosisAriaSer;
");

define("OPAL_DELETE_PATIENT_DIAGNOSIS","
    DELETE FROM ".OPAL_DIAGNOSIS_TABLE." WHERE DiagnosisSerNum = :DiagnosisSerNum;
");

define("OPAL_GET_PATIENT_NAME", "
    SELECT PatientSerNum AS psnum, CONCAT(UCASE(SUBSTRING(FirstName, 1, 1)), LOWER(SUBSTRING(FirstName, 2))) AS pname,
    CONCAT(UCASE(SUBSTRING(LastName, 1, 1)), LOWER(SUBSTRING(LastName, 2))) AS plname,
    SSN AS pramq, Sex AS psex, Email AS pemail, Language AS plang FROM ".OPAL_PATIENT_TABLE." WHERE LastName LIKE :name;

");

define("OPAL_GET_PATIENT_MRN", "
    SELECT p.PatientSerNum AS psnum, CONCAT(UCASE(SUBSTRING(p.FirstName, 1, 1)), LOWER(SUBSTRING(p.FirstName, 2))) AS pname,
    CONCAT(UCASE(SUBSTRING(p.LastName, 1, 1)), LOWER(SUBSTRING(p.LastName, 2))) AS plname,
    p.SSN AS pramq, p.Sex AS psex, p.Email AS pemail, p.Language AS plang FROM ".OPAL_PATIENT_TABLE." p
    WHERE (SELECT COUNT(*) FROM ".OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE." phi WHERE phi.MRN LIKE :MRN
    AND phi.PatientSerNum = p.PatientSerNum) > 0;
");

define("OPAL_GET_PATIENT_RAMQ", "
    SELECT PatientSerNum AS psnum, CONCAT(UCASE(SUBSTRING(FirstName, 1, 1)), LOWER(SUBSTRING(FirstName, 2))) AS pname,
    CONCAT(UCASE(SUBSTRING(LastName, 1, 1)), LOWER(SUBSTRING(LastName, 2))) AS plname,
    SSN AS pramq, Sex AS psex, Email AS pemail, Language AS plang FROM ".OPAL_PATIENT_TABLE." WHERE SSN LIKE :SSN;

");

define("OPAL_GET_DIAGNOSIS_REPORT", "
    SELECT DiagnosisSerNum AS sernum, CreationDate AS creationdate, Description_EN AS description
    FROM ".OPAL_DIAGNOSIS_TABLE." WHERE PatientSerNum = :pnum;
");

define ("OPAL_UPDATE_APPOINTMENT_STATUS","
UPDATE ".OPAL_APPOINTMENTS_TABLE." 
SET Status=:Status , State=:State 
WHERE SourceDatabaseSerNum=:SourceDatabaseSerNum 
AND AppointmentSerNum=:AppointmentSerNum 
");

define("OPAL_GET_APPOINTMENT_ID", "
SELECT *
FROM ".OPAL_APPOINTMENTS_TABLE."
WHERE SourceDatabaseSerNum=:SourceSystem
AND AppointmentAriaSer=:SourceId
");

define("OPAL_GET_APPOINTMENT_PENDING_ID", "
SELECT ID
FROM ".OPAL_APPOINTMENTS_PENDING_TABLE."
WHERE sourceName=:SourceSystem
AND AppointmentAriaSer=:SourceId
");


define("OPAL_GET_APPOINTMENT_PENDING", "
SELECT ID, PatientSerNum, sourceName, 
appointmentTypeCode, appointmentTypeDescription, 
AppointmentAriaSer, PrioritySerNum, DiagnosisSerNum, 
Status, State, ScheduledStartTime, ScheduledEndTime, 
ActualStartDate, ActualEndDate, Location, 
RoomLocation_EN, RoomLocation_FR, Checkin, 
ChangeRequest, DateAdded, DateModified, ReadStatus, 
Level, SessionId, updatedBy, LastUpdated
FROM ".OPAL_APPOINTMENTS_PENDING_TABLE."
WHERE sourceName=:SourceSystem
AND AppointmentAriaSer=:SourceId
");

define("OPAL_GET_APPOINTMENT_PENDING_MH_ID", "
SELECT AppointmentPendingId
FROM ".OPAL_APPOINTMENTS_PENDING_MH_TABLE."
WHERE sourceName=:SourceSystem
AND AppointmentAriaSer=:SourceId
");

define("OPAL_GET_APPOINTMENT_PENDING_MH", "
SELECT AppointmentPendingId, revisionId, ACTION, PatientSerNum, 
sourceName, AppointmentAriaSer, PrioritySerNum, 
DiagnosisSerNum, Status, State, ScheduledStartTime, 
ScheduledEndTime, ActualStartDate, ActualEndDate, 
Location, RoomLocation_EN, RoomLocation_FR, Checkin, Level, 
ChangeRequest, PendingDate, ProcessedDate, ReadStatus, 
SessionId, LastUpdated
FROM ".OPAL_APPOINTMENTS_PENDING_MH_TABLE."
WHERE sourceName=:SourceSystem
AND AppointmentAriaSer=:SourceId
");

define("OPAL_DELETE_APPOINTMENT_PENDING","
    DELETE FROM ".OPAL_APPOINTMENTS_PENDING_TABLE." WHERE ID = :AppointmentSerNum;
");

define("OPAL_GET_APPOINTMENT", "
    SELECT DISTINCT phi.PatientSerNum,
    hm.MapUrl,hm.MapURL_EN,hm.MapURL_FR,hm.MapName_EN,hm.MapName_FR,hm.MapDescription_EN,hm.MapDescription_FR,
    a.ScheduledStartTime AS starttime, a.ScheduledEndTime AS endtime,
    a.checkin,a.SourceDatabaseSerNum,a.AppointmentAriaSer,em.ReadStatus,
    r.ResourceName,r.ResourceType,a.Status ,
    a.RoomLocation_EN,a.RoomLocation_FR,
    ac.CheckinPossible,ac.CheckinInstruction_EN,ac.CheckinInstruction_FR,
    hm.HospitalMapSerNum,
    a.ScheduledStartTime AS starttime, a.Status AS status, a.DateAdded AS dateadded,
    als.AliasName_EN AS aliasname, als.AliasType AS aliastype, r.ResourceName AS resourcename
    FROM ".OPAL_APPOINTMENTS_TABLE." a,
     ".OPAL_HOSPITAL_MAP_TABLE." hm,
    ".OPAL_ALIAS_TABLE." als,
    ".OPAL_APPOINTMENT_CHECK_IN_TABLE." ac,
     ".OPAL_ALIAS_EXPRESSION_TABLE." ae,
    ".OPAL_RESOURCE_TABLE." r, ".OPAL_RESOURCE_APPOINTMENT_TABLE." ra,
    ".OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE ." phi,
    ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." emc ,
    ".OPAL_EDUCATION_MATERIAL_TABLE." em
    WHERE phi.Hospital_Identifier_Type_Code = :site
    AND phi.mrn = :mrn
    AND phi.PatientSerNum = a.PatientSerNum
    AND em.PatientSerNum = a.PatientSerNum
    AND em.EducationalMaterialControlSerNum=emc.EducationalMaterialControlSerNum
    AND a.AliasExpressionSerNum = ae.AliasExpressionSerNum
    AND ae.AliasSerNum = als.AliasSerNum
    AND als.AliasSerNum = ac.AliasSerNum
    AND als.HospitalMapSerNum = hm.HospitalMapSerNum
    AND r.ResourceSerNum = ra.ResourceSerNum
    AND ra.AppointmentSerNum = a.AppointmentSerNum
    AND (:startDate IS NULL OR ScheduledStartTime >=  CAST(:startDate AS DATETIME))
    AND (:endDate IS NULL OR ScheduledStartTime <= CAST(:endDate AS DATETIME));
");

define("OPAL_GET_APPOINTMENT_REPORT", "
    SELECT a.ScheduledStartTime AS starttime, a.Status AS status, a.DateAdded AS dateadded,
    als.AliasName_EN AS aliasname, als.AliasType AS aliastype, r.ResourceName AS resourcename
    FROM ".OPAL_APPOINTMENTS_TABLE." a, ".OPAL_ALIAS_EXPRESSION_TABLE." ae, ".OPAL_ALIAS_TABLE." als,
    ".OPAL_RESOURCE_TABLE." r, ".OPAL_RESOURCE_APPOINTMENT_TABLE." ra
    WHERE PatientSerNum = :pnum AND a.AliasExpressionSerNum = ae.AliasExpressionSerNum
    AND ae.AliasSerNum = als.AliasSerNum AND r.ResourceSerNum = ra.ResourceSerNum
    AND ra.AppointmentSerNum = a.AppointmentSerNum;
");

define("OPAL_GET_QUESTIONNAIRE_REPORT", "
    SELECT q.DateAdded AS dateadded, q.CompletionDate AS datecompleted, qc.QuestionnaireName_EN AS name
    FROM ".OPAL_QUESTIONNAIRE_TABLE." q, ".OPAL_QUESTIONNAIRE_CONTROL_TABLE." qc
    WHERE q.QuestionnaireControlSerNum = qc.QuestionnaireControlSerNum AND PatientSerNum = :pnum;
");

define("OPAL_GET_EDUCATIONAL_MATERIAL_REPORT", "
    SELECT em.DateAdded AS dateadded, em.ReadStatus AS readstatus, emc.EducationalMaterialType_EN AS materialtype,
    emc.Name_EN AS name
    FROM ".OPAL_EDUCATIONAL_MATERIAL_TABLE." AS em, ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." AS emc
    WHERE em.EducationalMaterialControlSerNum = emc.EducationalMaterialControlSerNum
    AND PatientSerNum = :pnum;
");

define("OPAL_GET_LEGACY_TEST_REPORT", "
    SELECT DateAdded AS dateadded, TestDate AS testdate, ComponentName AS componentname,
    AbnormalFlag AS abnormalflag, TestValue AS testvalue, MinNorm AS minnorm, MaxNorm AS maxnorm,
    UnitDescription AS unitdescription, ReadStatus AS readstatus
    FROM ".OPAL_LEGACY_TEST_RESULT_TABLE."
    WHERE PatientSerNum = :pnum;
");

define("OPAL_GET_TEST_REPORT", "
    SELECT IfNull((Select tge.ExpressionName from ".OPAL_TEST_GROUP_EXPRESSION_TABLE." tge where ptr.TestGroupExpressionSerNum = tge.TestGroupExpressionSerNum), '') as groupname,
	ptr.ReadStatus as readstatus,
	IfNull((select tc.Name_EN from ".OPAL_TEST_CONTROL_TABLE." tc where te.TestControlSerNum = tc.TestControlSerNum), te.ExpressionName) as testname,
	ptr.AbnormalFlag as abnormalflag,
	ptr.NormalRange as normalrange,
	case
		when ptr.TestValue = 'Non détecté' then '0'
		when ptr.TestValue = 'Détecté' then '1'
	else ptr.TestValue
	end as testvalue,
	ptr.UnitDescription as description,
	ptr.DateAdded as dateadded,
	ptr.CollectedDateTime as datecollected,
	ptr.ResultDateTime as resultdate
FROM ".OPAL_PATIENT_TEST_RESULT_TABLE." ptr, ".OPAL_TEST_EXPRESSION_TABLE." te
WHERE
	ptr.PatientSerNum = :pnum
	AND ptr.TestExpressionSerNum = te.TestExpressionSerNum
	AND ptr.TestValueNumeric is not null
ORDER BY groupName, sequenceNum;");

define("OPAL_GET_NOTIFICATIONS_REPORT", "
    SELECT n.DateAdded AS dateadded, n.LastUpdated lastupdated, n.ReadStatus AS readstatus,
    nc.Name_EN AS name, n.RefTableRowTitle_EN AS tablerowtitle
    FROM ".OPAL_PATIENT_TABLE." p, ".OPAL_NOTIFICATION_TABLE." n, ".OPAL_NOTIFICATION_CONTROL_TABLE." nc
    WHERE p.PatientSerNum = n.PatientSerNum
    AND n.NotificationControlSerNum = nc.NotificationControlSerNum
    AND p.PatientSerNum = :pnum;
");

define("OPAL_GET_TREATMENT_PLAN_REPORT", "
    SELECT d.Description_EN AS diagnosisdescription, a.AliasType AS aliastype, pr.PriorityCode AS prioritycode,
    ae.Description AS aliasexpressiondescription, a.AliasName_EN AS aliasname, a.AliasDescription_EN AS aliasdescription,
    t.Status AS taskstatus, t.State AS taskstate, t.DueDateTime AS taskdue, t.CompletionDate AS taskcompletiondate
    FROM ".OPAL_TASK_TABLE." t, ".OPAL_PATIENT_TABLE." p, ".OPAL_ALIAS_EXPRESSION_TABLE." ae, ".OPAL_ALIAS_TABLE." a,
    ".OPAL_DIAGNOSIS_TABLE." d, ".OPAL_PRIORITY_TABLE." AS pr
    WHERE t.PatientSerNum = p.PatientSerNum AND p.PatientSerNum = pr.PatientSerNum
    AND ae.AliasExpressionSerNum = t.AliasExpressionSerNum AND ae.AliasSerNum = a.AliasSerNum
    AND t.DiagnosisSerNum = d.DiagnosisSerNum AND t.PrioritySerNum = pr.PrioritySerNum
    AND p.PatientSerNum = :pnum;
");

define("OPAL_GET_CLINICAL_NOTES_REPORT", "
    SELECT d.OriginalFileName AS originalname, d.FinalFileName AS finalname, d.CreatedTimeStamp AS created,
    d.ApprovedTimeStamp AS approved, ae.ExpressionName AS aliasexpressionname
    FROM ".OPAL_DOCUMENT_TABLE." d, ".OPAL_PATIENT_TABLE." p, ".OPAL_ALIAS_EXPRESSION_TABLE." AS ae
    WHERE d.PatientSerNum = p.PatientSerNum AND d.AliasExpressionSerNum = ae.AliasExpressionSerNum
    AND p.PatientSerNum = :pnum;
");

define("OPAL_GET_TREATING_TEAM_REPORT", "
    SELECT tx.DateAdded AS dateadded, pc.PostName_EN AS name, tx.ReadStatus AS readstatus, pc.Body_EN AS body
    FROM ".OPAL_TX_TEAM_MESSAGE_TABLE." tx, ".OPAL_POST_TABLE." pc, ".OPAL_PATIENT_TABLE." p
    WHERE tx.PatientSerNum = p.PatientSerNum AND tx.PostControlSerNum = pc.PostControlSerNum
    AND p.PatientSerNum = :pnum;
");

define("OPAL_GET_GENERAL_REPORT", "
    SELECT a.DateAdded AS dateadded, a.ReadStatus AS readstatus, pc.PostName_EN AS name, pc.Body_EN AS body
    FROM ".OPAL_PATIENT_TABLE." p, ".OPAL_ANNOUNCEMENT_TABLE." a, ".OPAL_POST_TABLE." pc
    WHERE p.PatientSerNum = a.PatientSerNum AND a.PostControlSerNum = pc.PostControlSerNum
    AND p.PatientSerNum = :pnum;
");

define("OPAL_GET_EDUCATIONAL_MATERIAL_OPTIONS", "
    SELECT Name_EN AS name, PublishFlag AS pflag
    FROM ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE."
    WHERE EducationalMaterialType_EN = :matType;
");

define("OPAL_GET_EDUCATIONAL_MATERIAL_GROUP", "
    SELECT p.FirstName AS pname, p.LastName AS plname, p.PatientSerNum AS pser, p.Sex AS psex,
    p.Age AS page, p.DateOfBirth AS pdob, em.DateAdded AS edate, em.ReadStatus AS eread, em.LastUpdated AS eupdate
    FROM ".OPAL_PATIENT_TABLE." p, ".OPAL_EDUCATIONAL_MATERIAL_TABLE." em, ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." emc
    WHERE em.PatientSerNum = p.PatientSerNum AND em.EducationalMaterialControlSerNum = emc.EducationalMaterialControlSerNum
    AND emc.EducationalMaterialType_EN = :matType
    AND emc.Name_EN = :matName
");

define("OPAL_GET_QUESTIONNAIRE_OPTIONS", "
    SELECT QuestionnaireName_EN AS name FROM ".OPAL_QUESTIONNAIRE_CONTROL_TABLE.";");

define("OPAL_GET_QUESTIONNAIRE_REPORT_GROUP", "
    SELECT p.FirstName AS pname, p.LastName AS plname, p.PatientSerNum AS pser, p.Sex AS psex,
    p.DateOfBirth AS pdob, q.DateAdded AS qdate, q.CompletionDate AS qcomplete
    FROM ".OPAL_PATIENT_TABLE." p, ".OPAL_QUESTIONNAIRE_TABLE." q, ".OPAL_QUESTIONNAIRE_CONTROL_TABLE." qc
    WHERE p.PatientSerNum = q.PatientSerNum AND q.QuestionnaireControlSerNum = qc.QuestionnaireControlSerNum
    AND qc.QuestionnaireName_EN = :qName
");

define("OPAL_GET_DEMOGRAPHICS_REPORT_GROUP", "
    SELECT p.PatientSerNum AS pser, p.FirstName AS pname, p.LastName AS plname, p.Sex AS psex,
    p.DateOfBirth AS pdob, p.Age AS page, p.Email AS pemail, p.Language AS plang, p.RegistrationDate AS preg,
    p.ConsentFormExpirationDate AS pcons, ifnull((select d1.Description_EN from ".OPAL_DIAGNOSIS_TABLE." d1 where p.PatientSerNum = d1.PatientSerNum order by CreationDate desc limit 1), 'NA') as diagdesc,
    ifnull((select d2.CreationDate from ".OPAL_DIAGNOSIS_TABLE." d2 where p.PatientSerNum = d2.PatientSerNum order by CreationDate desc limit 1), now()) as diagdate
    FROM ".OPAL_PATIENT_TABLE." p ORDER BY p.RegistrationDate
");

define("OPAL_GET_TEST_RESULTS","
    SELECT DISTINCT TestControlSerNum AS serial, Name_EN AS name_EN, Name_FR AS name_FR, PublishFlag AS publish,
    Group_EN AS group_EN, Group_FR AS group_FR, 0 AS changed FROM ".OPAL_TEST_CONTROL_TABLE.";
");

define("OPAL_GET_ASSIGNED_TESTS","
    SELECT tre.ExpressionName AS id, trc.Name_EN AS name_EN FROM ".OPAL_TEST_RESULT_CONTROL_TABLE." trc
    LEFT JOIN ".OPAL_TEST_RESULT_EXPRESSION_TABLE." tre ON trc.TestResultControlSerNum = tre.TestResultControlSerNum;
");

define("OPAL_UPDATE_TEST_RESULTS_PUBLISH_FLAG","
    UPDATE ".OPAL_TEST_CONTROL_TABLE." SET PublishFlag = :PublishFlag, LastUpdatedBy = :LastUpdatedBy,
    SessionId = :SessionId WHERE TestControlSerNum = :TestControlSerNum AND PublishFlag != :PublishFlag;
");

define("OPAL_GET_TEST_RESULT_DETAILS","
    SELECT DISTINCT Name_EN AS name_EN, Name_FR AS name_FR, Description_EN AS description_EN, Description_FR AS description_FR,
    Group_EN AS group_EN, Group_FR AS group_FR, EducationalMaterialControlSerNum AS eduMatSer FROM ".OPAL_TEST_CONTROL_TABLE."
    WHERE TestControlSerNum = :TestControlSerNum;
");

define("OPAL_GET_TEST_EXPRESSION_NAMES","
    SELECT DISTINCT ExpressionName AS name, TestExpressionSerNum AS id, 1 AS added FROM ".OPAL_TEST_EXPRESSION_TABLE."
    WHERE TestControlSerNum = :TestControlSerNum;
");

/*define("OPAL_GET_TEST_RESULT_ADD_LINK","
    SELECT DISTINCT TestResultAdditionalLinksSerNum AS serial, Name_EN AS name_EN, Name_FR AS name_FR, URL_EN AS url_EN,
    URL_FR AS url_FR FROM ".OPAL_TEST_RESULT_ADD_LINKS_TABLE." WHERE TestResultControlSerNum = :TestResultControlSerNum;
");*/

define("OPAL_GET_TEST_RESULT_GROUPS","
    SELECT DISTINCT Group_EN AS EN, Group_FR AS FR FROM ".OPAL_TEST_CONTROL_TABLE.";
");

define("OPAL_DOES_EDU_MATERIAL_EXISTS","
    SELECT EducationalMaterialControlSerNum FROM ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE."
    WHERE EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum;
");

define("OPAL_SANITIZE_EMPTY_TEST_RESULTS","
    UPDATE ".OPAL_TEST_CONTROL_TABLE." tc LEFT JOIN ".OPAL_TEST_EXPRESSION_TABLE." tre ON
    tc.TestControlSerNum = tre.TestControlSerNum SET tc.PublishFlag = ".INACTIVE_RECORD.", tc.LastUpdatedBy = :LastUpdatedBy,
    tc.SessionId = :SessionId WHERE tre.TestExpressionSerNum IS NULL;
");

define("OPAL_UPDATE_TEST_CONTROL", "
    UPDATE ".OPAL_TEST_CONTROL_TABLE." SET name_EN = :name_EN, name_FR = :name_FR, description_EN = :description_EN,
    description_FR = :description_FR, group_EN = :group_EN, group_FR = :group_FR,
    EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum, LastUpdatedBy = :LastUpdatedBy,
    SessionId = :SessionId WHERE TestControlSerNum = :TestControlSerNum;"
);

define("OPAL_REMOVE_UNUSED_TEST_EXPRESSIONS","
    UPDATE ".OPAL_TEST_EXPRESSION_TABLE." SET TestControlSerNum = NULL WHERE TestControlSerNum = :TestControlSerNum
    AND TestExpressionSerNum NOT IN (%%LISTIDS%%);
");

/*define("OPAL_COUNT_TR_ADDITIONAL_LINKS", "
    SELECT COUNT(*) AS total FROM ".OPAL_TEST_RESULT_ADD_LINKS_TABLE." WHERE TestResultAdditionalLinksSerNum IN (%%LISTIDS%%);
");

define("OPAL_DELETE_UNUSED_ADD_LINKS","
    DELETE FROM ".OPAL_TEST_RESULT_ADD_LINKS_TABLE." WHERE TestResultControlSerNum = :TestResultControlSerNum
    AND TestResultAdditionalLinksSerNum NOT IN (%%LISTIDS%%);
");

define("OPAL_UPDATE_ADDITIONAL_LINKS","
    UPDATE ".OPAL_TEST_RESULT_ADD_LINKS_TABLE." SET Name_EN = :Name_EN, Name_FR = :Name_FR, URL_EN = :URL_EN, URL_FR = :URL_FR
    WHERE TestResultAdditionalLinksSerNum = :TestResultAdditionalLinksSerNum AND (Name_EN != :Name_EN OR Name_FR != :Name_FR
    OR URL_EN != :URL_EN OR URL_FR != :URL_FR);
");*/

define("OPAL_GET_TEST_RESULT_CHART_LOG","
    SELECT DISTINCT trmh.CronLogSerNum AS cron_serial, COUNT(trmh.CronLogSerNum) AS y, cl.CronDateTime AS x,
    trc.Name_EN AS name FROM ".OPAL_TEST_RESULT_MH_TABLE." trmh, ".OPAL_TEST_RESULT_EXPRESSION_TABLE." tre,
    ".OPAL_CRON_LOG_TABLE." cl, ".OPAL_TEST_RESULT_CONTROL_TABLE." trc WHERE cl.CronStatus = 'Started'
    AND cl.CronLogSerNum = trmh.CronLogSerNum AND trmh.CronLogSerNum IS NOT NULL
    AND trmh.TestResultExpressionSerNum = tre.TestResultExpressionSerNum
    AND tre.TestResultControlSerNum = trc.TestResultControlSerNum GROUP BY trmh.CronLogSerNum, cl.CronDateTime
    ORDER BY cl.CronDateTime ASC;
");

define("OPAL_GET_TEST_RESULT_CHART_LOG_BY_ID","
    SELECT DISTINCT trmh.CronLogSerNum AS cron_serial, COUNT(trmh.CronLogSerNum) AS y, cl.CronDateTime AS x,
    trc.Name_EN AS name FROM ".OPAL_TEST_RESULT_MH_TABLE." trmh, ".OPAL_TEST_RESULT_EXPRESSION_TABLE." tre,
    ".OPAL_CRON_LOG_TABLE." cl, ".OPAL_TEST_RESULT_CONTROL_TABLE." trc WHERE cl.CronStatus = 'Started'
    AND cl.CronLogSerNum = trmh.CronLogSerNum AND trmh.CronLogSerNum IS NOT NULL
    AND trmh.TestResultExpressionSerNum = tre.TestResultExpressionSerNum AND tre.TestResultControlSerNum = :TestResultControlSerNum
    AND tre.TestResultControlSerNum = trc.TestResultControlSerNum GROUP BY trmh.CronLogSerNum, cl.CronDateTime
    ORDER BY cl.CronDateTime ASC;
");

define("OPAL_UNSET_TEST_EXPRESSIONS","
    UPDATE ".OPAL_TEST_EXPRESSION_TABLE." SET TestControlSerNum = NULL WHERE TestControlSerNum = :TestControlSerNum;
");

/*define("OPAL_DELETE_TEST_RESULT_ADDITIONAL_LINKS","
    DELETE FROM ".OPAL_TEST_RESULT_ADD_LINKS_TABLE." WHERE TestResultControlSerNum = :TestResultControlSerNum;
");*/

define("OPAL_DELETE_TEST_RESULT","
    DELETE FROM ".OPAL_TEST_CONTROL_TABLE." WHERE TestControlSerNum = :TestControlSerNum;
");

define("OPAL_UPDATE_TEST_RESULT_MH_DELETION","
    UPDATE ".OPAL_TEST_RESULT_CONTROL_MH_TABLE." SET LastUpdatedBy = :LastUpdatedBy, SessionId = :SessionId
    WHERE TestResultControlSerNum = :TestResultControlSerNum ORDER BY RevSerNum DESC LIMIT 1
");

define("OPAL_GET_TEST_NAMES","
    SELECT te.TestExpressionSerNum AS id, te.TestControlSerNum, tc.Name_EN AS name_EN, te.ExpressionName AS name
    FROM ".OPAL_TEST_EXPRESSION_TABLE." te LEFT JOIN ".OPAL_TEST_CONTROL_TABLE." tc
    ON te.TestControlSerNum = tc.TestControlSerNum ORDER BY te.ExpressionName;
");

define("OPAL_COUNT_TEST_IDS","
    SELECT COUNT(*) AS total FROM ".OPAL_TEST_EXPRESSION_TABLE." WHERE TestExpressionSerNum IN (%%LISTIDS%%);
");

define("OPAL_UPDATE_TEST_EXPRESSION","
    UPDATE ".OPAL_TEST_EXPRESSION_TABLE." SET TestControlSerNum = :TestControlSerNum, SessionId = :SessionId,
    LastUpdatedBy = :LastUpdatedBy WHERE TestExpressionSerNum = :TestExpressionSerNum;
");

define("OPAL_GET_PATIENTS_LIST","
    SELECT DISTINCT PatientSerNum AS id, 0 AS added, CONCAT(CONCAT(UCASE(SUBSTRING(LastName, 1, 1)), LOWER(SUBSTRING(LastName, 2))), ', ', CONCAT(UCASE(SUBSTRING(FirstName, 1, 1)), LOWER(SUBSTRING(FirstName, 2)))) AS name
    FROM ".OPAL_PATIENT_TABLE." ORDER BY PatientSerNum;
");

define("OPAL_GET_PATIENTS_LIST_BY_ID","
    SELECT * FROM ".OPAL_PATIENT_TABLE." WHERE PatientSerNum IN (%%LISTIDS%%);
");

define("OPAL_GET_PATIENTS_STUDY","
    SELECT patientId FROM ".OPAL_PATIENT_STUDY_TABLE." WHERE studyId = :studyId ORDER BY patientId;
");

define("OPAL_GET_PATIENTS_STUDY_CONSENTS","
    SELECT ps.patientId AS id, ps.consentStatus AS consent, CONCAT(CONCAT(UCASE(SUBSTRING(p.LastName, 1, 1)), LOWER(SUBSTRING(p.LastName, 2))), ', ', CONCAT(UCASE(SUBSTRING(p.FirstName, 1, 1)), LOWER(SUBSTRING(p.FirstName, 2)))) AS name
    FROM ".OPAL_PATIENT_STUDY_TABLE." ps, ".OPAL_PATIENT_TABLE." p
    WHERE p.PatientSerNum = ps.patientId AND ps.studyId = :studyId;
");

define("OPAL_CHECK_CONSENT_FORM_PUBLISHED","
    SELECT QuestionnaireControlSerNum, QuestionnaireName_EN, QuestionnaireName_FR, PublishFlag, DateAdded
    FROM ".OPAL_QUESTIONNAIRE_CONTROL_TABLE."
    WHERE QuestionnaireDBSerNum = :consentId AND PublishFlag = 1;
");

define("OPAL_GET_CONSENT_BY_STUDY_ID","
    SELECT consentQuestionnaireId FROM ".OPAL_STUDY_TABLE." WHERE ID = :studyId;
");

define("OPAL_GET_QUESTIONNAIRES_STUDY","
    SELECT questionnaireId FROM ".OPAL_QUESTIONNAIRE_STUDY_TABLE." WHERE studyId = :studyId ORDER BY questionnaireId;
");

define("OPAL_DELETE_PATIENTS_STUDY", "
    DELETE FROM ".OPAL_PATIENT_STUDY_TABLE." WHERE studyId = :studyId AND patientId NOT IN (%%LISTIDS%%);
");

define("OPAL_DELETE_QUESTIONNAIRES_STUDY", "
    DELETE FROM ".OPAL_QUESTIONNAIRE_STUDY_TABLE." WHERE studyId = :studyId AND questionnaireId NOT IN (%%LISTIDS%%);
");

define("OPAL_DELETE_QUESTIONNAIRE_FROM_STUDIES", "
    DELETE FROM ".OPAL_QUESTIONNAIRE_STUDY_TABLE." WHERE questionnaireId = :questionnaireId;
");

define("OPAL_UPDATE_PATIENT_PUBLISH_FLAG","
    UPDATE ".OPAL_PATIENT_CONTROL_TABLE." SET PatientUpdate = :PatientUpdate WHERE PatientSerNum = :PatientSerNum
");

define("OPAL_UPDATE_PATIENT","
    UPDATE ".OPAL_PATIENT_TABLE." SET PatientAriaSer = :PatientAriaSer, PatientId = :PatientId,	PatientId2 = :PatientId2,
	FirstName = :FirstName, LastName = :LastName, Alias = :Alias, ProfileImage = :ProfileImage, Sex = :Sex,
	DateOfBirth = :DateOfBirth, Age = :Age, TelNum = :TelNum, EnableSMS = :EnableSMS, Email = :Email,
	Language = :Language, SSN = :SSN, AccessLevel = :AccessLevel, RegistrationDate = :RegistrationDate,
	ConsentFormExpirationDate = :ConsentFormExpirationDate, BlockedStatus = :BlockedStatus, StatusReasonTxt = :StatusReasonTxt,
	DeathDate = :DeathDate, SessionId = :SessionId, TestUser = :TestUser, TermsAndAgreementSign = :TermsAndAgreementSign,
	TermsAndAgreementSignDateTime = :TermsAndAgreementSignDateTime WHERE PatientSerNum = :PatientSerNum
");

define("OPAL_UPDATE_PATIENT_HOSPITAL_IDENTIFIER","
    UPDATE " . OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE . " SET
    PatientSerNum = :PatientSerNum,
    Hospital_Identifier_Type_Code = :Hospital_Identifier_Type_Code,
    MRN = :MRN,
    Is_Active = :Is_Active
    WHERE Patient_Hospital_Identifier_Id = :Patient_Hospital_Identifier_Id
");

define("OPAL_GET_PATIENTS","
    SELECT DISTINCT pc.PatientSerNum AS serial, pt.SSN AS RAMQ, pc.PatientUpdate AS transfer, CONCAT(UCASE(LEFT(pt.FirstName, 1)), LCASE(SUBSTRING(pt.FirstName, 2)),

    ' ', UCASE(LEFT(pt.LastName, 1)), LCASE(SUBSTRING(pt.LastName, 2))) AS name, pc.LastTransferred AS lasttransferred, pt.email AS email FROM ".OPAL_PATIENT_TABLE." pt RIGHT JOIN
    ".OPAL_PATIENT_CONTROL_TABLE." pc ON pt.PatientSerNum = pc.PatientSerNum LEFT JOIN ".OPAL_USERS_TABLE." usr ON
    pt.PatientSerNum = usr.UserTypeSerNum WHERE usr.UserType = 'Patient';
");

define("OPAL_GET_PATIENT_ACTIVITY","
SELECT DISTINCT
p.PatientSerNum AS serial,
p.SSN AS RAMQ,
CONCAT(UCASE(LEFT(p.FirstName, 1)), LCASE(SUBSTRING(p.FirstName, 2)),
' ', UCASE(LEFT(p.LastName, 1)), LCASE(SUBSTRING(p.LastName, 2))) AS name,
pdi.DeviceId AS deviceId,
CASE WHEN pdi.DeviceType = 0 THEN 'iOS'
     WHEN pdi.DeviceType = 1 THEN 'Android'
     ELSE 'Browser' END AS deviceType,
pdi.LastUpdated AS login,
pdi.appVersion AS appVersion
FROM ".OPAL_PATIENT_TABLE." p, ".OPAL_PATIENT_DEVICE_IDENTIFIER_TABLE." pdi, ".OPAL_USERS_TABLE." u
WHERE pdi.PatientSerNum = p.PatientSerNum
AND p.PatientSerNum = u.UserTypeSerNum
AND u.UserType = 'Patient'
ORDER BY pdi.LastUpdated DESC LIMIT 20000;
");

define("OPAL_GET_SOURCE_TEST_RESULTS","
    SELECT mstr.ID, mstr.externalId, mstr.code, mstr.description, mstr.source, mstr.creationDate, mstr.createdBy, mstr.lastUpdated,
    mstr.updatedBy, tc.TestControlSerNum, tc.Name_EN, tc.name_FR FROM ".OPAL_MASTER_SOURCE_TEST_RESULT_TABLE." mstr
    LEFT JOIN ".OPAL_TEST_EXPRESSION_TABLE." te ON te.TestCode = mstr.code AND te.SourceDatabaseSerNum = mstr.source
    LEFT JOIN ".OPAL_TEST_CONTROL_TABLE." tc ON tc.TestControlSerNum = te.TestControlSerNum
    WHERE mstr.deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_SOURCE_TEST_RESULT_DETAILS","
    SELECT mstr.ID, mstr.externalId, mstr.code, mstr.description, mstr.source, mstr.creationDate, mstr.createdBy, mstr.lastUpdated,
    mstr.updatedBy, tc.TestControlSerNum, tc.Name_EN, tc.name_FR, sc.SourceDatabaseName FROM ".OPAL_MASTER_SOURCE_TEST_RESULT_TABLE." mstr
    LEFT JOIN ".OPAL_TEST_EXPRESSION_TABLE." te ON te.TestCode = mstr.code AND te.SourceDatabaseSerNum = mstr.source
    LEFT JOIN ".OPAL_TEST_CONTROL_TABLE." tc ON tc.TestControlSerNum = te.TestControlSerNum
    LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." sc ON sc.SourceDatabaseSerNum = mstr.source
    WHERE mstr.deleted = ".NON_DELETED_RECORD." AND mstr.code = :code AND mstr.source = :source;
");

define("OPAL_GET_SOURCE_ID","
    SELECT SourceDatabaseSerNum AS ID FROM SourceDatabase WHERE SourceDatabaseName = :SourceDatabaseName AND
    Enabled = ".ACTIVE_RECORD.";
");

define("OPAL_REPLACE_TEST_RESULT", "
    UPDATE ".OPAL_MASTER_SOURCE_TEST_RESULT_TABLE." SET code = :code, description = :description, deleted = ".NON_DELETED_RECORD.",
    deletedBy = '', creationDate = :creationDate, createdBy = :createdBy, updatedBy = :updatedBy WHERE ID = :ID;
");

define("OPAL_UPDATE_TEST_RESULT", "
    UPDATE ".OPAL_MASTER_SOURCE_TEST_RESULT_TABLE." SET externalId = :externalId, description = :description, updatedBy = :updatedBy WHERE code = :code
    AND source = :source;
");

define("OPAL_SOURCE_TEST_RESULTS_EXISTS","
    SELECT ID, code, description, deleted FROM ".OPAL_MASTER_SOURCE_TEST_RESULT_TABLE." WHERE code = :code AND source = :source;
");

define("OPAL_MARK_AS_DELETED_SOURCE_TEST_RESULT","
    UPDATE ".OPAL_MASTER_SOURCE_TEST_RESULT_TABLE." SET deleted = ".DELETED_RECORD.", updatedBy = :updatedBy, deletedBy = :deletedBy WHERE code = :code
    AND source = :source;
");

define("OPAL_GET_EXTERNAL_SOURCE_DB","
    SELECT SourceDatabaseSerNum AS ID, SourceDatabaseName AS name FROM ".OPAL_SOURCE_DATABASE_TABLE." WHERE Enabled = ".ACTIVE_RECORD.";
");

define("OPAL_GET_SOURCE_DIAGNOSES","
    SELECT msd.ID, msd.externalId, msd.code, msd.description, msd.source, msd.creationDate, msd.createdBy, msd.lastUpdated,
    msd.updatedBy, dt.DiagnosisTranslationSerNum, dt.Name_EN, dt.name_FR FROM ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." msd
    LEFT JOIN ".OPAL_DIAGNOSIS_CODE_TABLE." dc ON dc.SourceUID = msd.externalId AND dc.Source = msd.source
    LEFT JOIN ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." dt ON dt.DiagnosisTranslationSerNum = dc.DiagnosisTranslationSerNum
    WHERE msd.deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_SOURCE_DIAGNOSIS_DETAILS","
    SELECT msd.ID, msd.externalId, msd.code, msd.description, msd.source, msd.creationDate, msd.createdBy, msd.lastUpdated,
    msd.updatedBy, dt.DiagnosisTranslationSerNum, dt.Name_EN, dt.name_FR, sc.SourceDatabaseName FROM ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." msd
    LEFT JOIN ".OPAL_DIAGNOSIS_CODE_TABLE." dc ON dc.SourceUID = msd.externalId AND dc.Source = msd.source
    LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." sc ON sc.SourceDatabaseSerNum = msd.source
    LEFT JOIN ".OPAL_DIAGNOSIS_TRANSLATION_TABLE." dt ON dt.DiagnosisTranslationSerNum = dc.DiagnosisTranslationSerNum
    WHERE msd.deleted = ".NON_DELETED_RECORD." AND msd.externalId = :externalId AND msd.source = :source AND msd.code = :code;
");

define("OPAL_REPLACE_SOURCE_DIAGNOSIS", "
    UPDATE ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." SET description = :description, deleted = ".NON_DELETED_RECORD.", deletedBy = '', creationDate = :creationDate, createdBy = :createdBy, updatedBy = :updatedBy WHERE externalId = :externalId
    AND source = :source AND code = :code;
");

define("OPAL_UPDATE_SOURCE_DIAGNOSIS", "
    UPDATE ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." SET description = :description, updatedBy = :updatedBy WHERE externalId = :externalId
    AND source = :source AND code = :code;
");

define("OPAL_IS_SOURCE_DIAGNOSIS_EXISTS","
    SELECT code, description, deleted FROM ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." WHERE externalId = :externalId
    AND source = :source AND code = :code;
");

define("OPAL_MARKED_AS_DELETED_SOURCE_DIAGNOSIS", "
    UPDATE ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." SET deleted = ".DELETED_RECORD.", updatedBy = :updatedBy, deletedBy = :deletedBy WHERE externalId = :externalId
    AND source = :source AND code = :code;
");

define("OPAL_GET_SOURCE_ALIAS_DETAILS","
    SELECT msa.ID, msa.externalId, msa.code, msa.description, msa.type, msa.source, msa.creationDate, msa.createdBy, msa.lastUpdated,
    msa.updatedBy, a.AliasSerNum, a.AliasName_EN AS name_EN, a.AliasName_FR AS name_FR, sc.SourceDatabaseName FROM ".OPAL_MASTER_SOURCE_ALIAS_TABLE." msa
    LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON ae.ExpressionName = msa.code AND ae.Description = msa.description
    LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." sc ON sc.SourceDatabaseSerNum = msa.source
    LEFT JOIN ".OPAL_ALIAS_TABLE." a ON a.AliasSerNum = ae.AliasSerNum
    WHERE msa.deleted = ".NON_DELETED_RECORD." AND msa.externalId = :externalId
    AND msa.source = :source AND msa.code = :code AND msa.type = :type;
");

define("OPAL_REPLACE_SOURCE_ALIAS", "
    UPDATE ".OPAL_MASTER_SOURCE_ALIAS_TABLE." SET description = :description,
    deleted = ".NON_DELETED_RECORD.", deletedBy = '', createdBy = :createdBy,
    updatedBy = :updatedBy WHERE externalId = :externalId AND source = :source AND type = :type AND code = :code;
");

define("OPAL_UPDATE_SOURCE_ALIAS", "
    UPDATE ".OPAL_MASTER_SOURCE_ALIAS_TABLE." SET description = :description, updatedBy = :updatedBy WHERE externalId = :externalId
    AND source = :source AND type = :type AND code = :code;
");

define("OPAL_IS_SOURCE_ALIAS_EXISTS","
    SELECT code, description, deleted FROM ".OPAL_MASTER_SOURCE_ALIAS_TABLE." WHERE externalId = :externalId
    AND source = :source AND code = :code AND type = :type;
");

define("OPAL_MARKED_AS_DELETED_SOURCE_ALIAS", "
    UPDATE ".OPAL_MASTER_SOURCE_ALIAS_TABLE." SET deleted = ".DELETED_RECORD.", updatedBy = :updatedBy, deletedBy = :deletedBy WHERE externalId = :externalId
    AND source = :source AND type = :type AND code = :code;
");

define("OPAL_GET_ALIASES","
    SELECT a.AliasSerNum AS serial, a.AliasType AS type, a.AliasName_FR AS name_FR, a.AliasName_EN AS name_EN,
    a.AliasUpdate AS `update`, a.SourceDatabaseSerNum AS sd_serial, sd.SourceDatabaseName AS sd_name, a.ColorTag AS color,
    a.LastUpdated AS lastupdated, (SELECT COUNT(*) FROM ".OPAL_ALIAS_EXPRESSION_TABLE." ae WHERE ae.AliasSerNum = a.AliasSerNum)
    AS count FROM ".OPAL_ALIAS_TABLE." a LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." sd ON
    sd.SourceDatabaseSerNum = a.SourceDatabaseSerNum WHERE a.SourceDatabaseSerNum = sd.SourceDatabaseSerNum AND
    sd.Enabled = ".ACTIVE_RECORD.";
");

define("OPAL_GET_ALIASES_UNPUBLISHED_EXPRESSION","
    SELECT ae.ExpressionName AS id, ae.Description AS description, m.externalId, 1 AS added FROM ".OPAL_ALIAS_EXPRESSION_TABLE." ae
    LEFT JOIN ".OPAL_MASTER_SOURCE_ALIAS_TABLE." m ON m.ID = ae.masterSourceAliasId
    RIGHT JOIN ".OPAL_ALIAS_TABLE." al ON al.AliasSerNum = ae.AliasSerNum
    WHERE ae.AliasSerNum = :AliasSerNum
    AND CASE
        WHEN al.AliasType='Task' THEN (SELECT COUNT(*) FROM ".OPAL_TASK_TABLE." t WHERE t.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        WHEN al.AliasType='Appointment' THEN (SELECT COUNT(*) FROM ".OPAL_APPOINTMENTS_TABLE." a WHERE a.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        ELSE (SELECT COUNT(*) FROM ".OPAL_DOCUMENT_TABLE." d WHERE d.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
    END
    AND m.deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_ALIASES_PUBLISHED_EXPRESSION","
    SELECT ae.ExpressionName AS id, ae.Description AS description, m.externalId FROM ".OPAL_ALIAS_EXPRESSION_TABLE." ae
    LEFT JOIN ".OPAL_MASTER_SOURCE_ALIAS_TABLE." m ON m.ID = ae.masterSourceAliasId
    RIGHT JOIN ".OPAL_ALIAS_TABLE." al ON al.AliasSerNum = ae.AliasSerNum
    WHERE ae.AliasSerNum = :AliasSerNum
    AND CASE
        WHEN al.AliasType='Task' THEN (SELECT COUNT(*) FROM ".OPAL_TASK_TABLE." t WHERE t.AliasExpressionSerNum = ae.AliasExpressionSerNum) > 0
        WHEN al.AliasType='Appointment' THEN (SELECT COUNT(*) FROM ".OPAL_APPOINTMENTS_TABLE." a WHERE a.AliasExpressionSerNum = ae.AliasExpressionSerNum) > 0
        ELSE (SELECT COUNT(*) FROM ".OPAL_DOCUMENT_TABLE." d WHERE d.AliasExpressionSerNum = ae.AliasExpressionSerNum) > 0
    END
    AND m.deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_DELETED_ALIASES_EXPRESSION","
    SELECT ae.ExpressionName AS id, ae.Description AS description, msa.externalId FROM ".OPAL_ALIAS_EXPRESSION_TABLE." ae
    LEFT JOIN ".OPAL_ALIAS_TABLE." al ON al.AliasSerNum = ae.AliasSerNum
    LEFT JOIN ".OPAL_MASTER_SOURCE_ALIAS_TABLE." msa ON msa.ID = ae.masterSourceAliasId
    WHERE msa.deleted = ".DELETED_RECORD." AND ae.AliasSerNum = :AliasSerNum;
");

define("OPAL_GET_ALIAS_DETAILS","
    SELECT a.AliasSerNum AS serial, a.AliasType AS type, a.AliasName_FR AS name_FR, a.AliasName_EN AS name_EN,
    a.AliasDescription_FR AS description_FR, a.AliasDescription_EN AS description_EN, a.AliasUpdate AS `update`,
    a.EducationalMaterialControlSerNum AS eduMatSer, a.SourceDatabaseSerNum, s.SourceDatabaseName, a.ColorTag AS color,
    a.HospitalMapSerNum AS hospitalMapSer, ac.CheckinPossible AS checkin_possible, ac.CheckinInstruction_EN AS instruction_EN,
    ac.CheckinInstruction_FR AS instruction_FR FROM ".OPAL_ALIAS_TABLE." a LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." s
    ON s.SourceDatabaseSerNum = a.SourceDatabaseSerNum LEFT JOIN ".OPAL_APPOINTMENT_CHECKIN_TABLE." ac ON
    ac.AliasSerNum = a.AliasSerNum WHERE a.AliasSerNum = :AliasSerNum;
");

define("OPAL_SANITIZE_EMPTY_ALIASES","
    UPDATE ".OPAL_ALIAS_TABLE." a LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON a.AliasSerNum = ae.AliasSerNum SET
    a.AliasUpdate = ".NON_DELETED_RECORD.", a.SessionId = :SessionId, a.LastUpdatedBy = :LastUpdatedBy WHERE
    ae.AliasSerNum IS NULL AND a.AliasUpdate != 0;
");

define("OPAL_UPDATE_ALIAS_PUBLISH_FLAG","
    UPDATE ".OPAL_ALIAS_TABLE." SET AliasUpdate = :AliasUpdate, LastUpdatedBy = :LastUpdatedBy, SessionId = :SessionId
    WHERE AliasSerNum = :AliasSerNum;
");

define("OPAL_GET_SOURCE_DATABASES","
    SELECT SourceDatabaseSerNum AS serial, SourceDatabaseName AS name FROM ".OPAL_SOURCE_DATABASE_TABLE."
    WHERE Enabled = ".ACTIVE_RECORD." ORDER BY SourceDatabaseSerNum
");

define("OPAL_GET_ARIA_SOURCE_ALIASES","
    SELECT m.ID AS masterSourceAliasId, m.description AS name, m.code AS id, m.description, m.externalId, a.AliasName_EN AS assigned
    FROM ".OPAL_MASTER_SOURCE_ALIAS_TABLE." m
    LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON ae.masterSourceAliasId = m.ID
    LEFT JOIN ".OPAL_ALIAS_TABLE." a ON a.AliasSerNum = ae.AliasSerNum
    WHERE m.type = :type AND m.source = :source
    AND CASE
        WHEN a.AliasType='Task' THEN (SELECT COUNT(*) FROM ".OPAL_TASK_TABLE." t WHERE t.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        WHEN a.AliasType='Appointment' THEN (SELECT COUNT(*) FROM ".OPAL_APPOINTMENTS_TABLE." app WHERE app.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        ELSE (SELECT COUNT(*) FROM ".OPAL_DOCUMENT_TABLE." d WHERE d.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
    END
    AND m.deleted = ".NON_DELETED_RECORD." ORDER BY m.code");

define("OPAL_GET_SOURCE_ALIASES","
    SELECT m.ID AS masterSourceAliasId, CONCAT(m.code, ' (', m.description, ')') AS name, m.code AS id, m.description, m.externalId, 
    a.AliasName_EN AS assigned FROM ".OPAL_MASTER_SOURCE_ALIAS_TABLE." m
    LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON ae.masterSourceAliasId = m.ID
    LEFT JOIN ".OPAL_ALIAS_TABLE." a ON a.AliasSerNum = ae.AliasSerNum
    WHERE m.type = :type AND m.source = :source
    AND CASE
        WHEN a.AliasType='Task' THEN (SELECT COUNT(*) FROM ".OPAL_TASK_TABLE." t WHERE t.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        WHEN a.AliasType='Appointment' THEN (SELECT COUNT(*) FROM ".OPAL_APPOINTMENTS_TABLE." app WHERE app.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        ELSE (SELECT COUNT(*) FROM ".OPAL_DOCUMENT_TABLE." d WHERE d.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
    END
    AND m.deleted = ".NON_DELETED_RECORD." ORDER BY m.code");

define("OPAL_GET_DEACTIVATED_DIAGNOSIS_CODES","
    SELECT DISTINCT d.SourceUID AS sourceuid, d.DiagnosisCode AS code, d.Description AS description,
    CONCAT(d.DiagnosisCode, ' (', d.Description, ')') AS name FROM ".OPAL_DIAGNOSIS_CODE_TABLE." d
    LEFT JOIN ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." m ON m.code = d.DiagnosisCode AND m.description = d.Description
    WHERE DiagnosisTranslationSerNum = :DiagnosisTranslationSerNum AND m.deleted = ".DELETED_RECORD.";
");

define("OPAL_GET_LIST_DIAGNOSIS_CODES","
    SELECT ID, code, description, source FROM ".OPAL_MASTER_SOURCE_DIAGNOSIS_TABLE." WHERE ID IN (%%LISTIDS%%) AND deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_GET_ALIAS_LOGS","
    SELECT DISTINCT al.AliasName_EN AS name, apmh.CronLogSerNum AS cron_serial, COUNT(apmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_ALIAS_TABLE." al LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON ae.AliasSerNum = al.AliasSerNum
    LEFT JOIN ".OPAL_APPOINTMENT_MH_TABLE." apmh ON apmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
    LEFT JOIN ".OPAL_CRON_LOG_TABLE." cl ON cl.CronLogSerNum = apmh.CronLogSerNum
    WHERE cl.CronStatus = 'Started' AND cl.CronDateTime > curdate() - interval 1 year
    GROUP BY al.AliasName_EN, apmh.CronLogSerNum, cl.CronDateTime
    UNION ALL
    SELECT DISTINCT al.AliasName_EN AS name, docmh.CronLogSerNum AS cron_serial, COUNT(docmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_ALIAS_TABLE." al LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON ae.AliasSerNum = al.AliasSerNum
    LEFT JOIN ".OPAL_DOCUMENT_MH_TABLE." docmh ON docmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
    LEFT JOIN ".OPAL_CRON_LOG_TABLE." cl ON cl.CronLogSerNum = docmh.CronLogSerNum
    WHERE cl.CronStatus = 'Started' AND cl.CronDateTime > curdate() - interval 1 year
    GROUP BY al.AliasName_EN, docmh.CronLogSerNum, cl.CronDateTime
    UNION ALL
    SELECT DISTINCT al.AliasName_EN AS name, tmh.CronLogSerNum AS cron_serial, COUNT(tmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_ALIAS_TABLE." al LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON ae.AliasSerNum = al.AliasSerNum
    LEFT JOIN ".OPAL_TASK_MH_TABLE." tmh ON tmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
    LEFT JOIN ".OPAL_CRON_LOG_TABLE." cl ON cl.CronLogSerNum = tmh.CronLogSerNum
    WHERE cl.CronStatus = 'Started' AND cl.CronDateTime > curdate() - interval 1 year
    GROUP BY al.AliasName_EN, tmh.CronLogSerNum, cl.CronDateTime
    ORDER BY X ASC
");

define("OPAL_GET_APPOINTMENT_LOGS","
    SELECT DISTINCT apmh.CronLogSerNum AS cron_serial, COUNT(apmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_APPOINTMENT_MH_TABLE." apmh
    LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON apmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
    LEFT JOIN ".OPAL_CRON_LOG_TABLE." cl ON cl.CronLogSerNum = apmh.CronLogSerNum
    WHERE cl.CronStatus = 'Started' AND ae.AliasSerNum = :AliasSerNum
    GROUP BY apmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC
");

define("OPAL_GET_DOCUMENT_LOGS","
    SELECT DISTINCT docmh.CronLogSerNum AS cron_serial, COUNT(docmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_DOCUMENT_MH_TABLE." docmh
    LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON docmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
    LEFT JOIN ".OPAL_CRON_LOG_TABLE." cl ON cl.CronLogSerNum = docmh.CronLogSerNum
    WHERE cl.CronStatus = 'Started' AND ae.AliasSerNum = :AliasSerNum
    GROUP BY docmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC
");

define("OPAL_TASK_LOGS","
    SELECT DISTINCT taskmh.CronLogSerNum AS cron_serial, COUNT(taskmh.CronLogSerNum) AS y, cl.CronDateTime AS x
    FROM ".OPAL_TASK_MH_TABLE." taskmh
    LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON taskmh.AliasExpressionSerNum = ae.AliasExpressionSerNum
    LEFT JOIN ".OPAL_CRON_LOG_TABLE." cl ON cl.CronLogSerNum = taskmh.CronLogSerNum
    WHERE cl.CronStatus = 'Started' AND ae.AliasSerNum = :AliasSerNum
    GROUP BY taskmh.CronLogSerNum, cl.CronDateTime ORDER BY cl.CronDateTime ASC
");

define("OPAL_COUNT_EDU_MATERIAL","
    SELECT COUNT(*) AS total FROM ".OPAL_EDUCATION_MATERIAL_CONTROL_TABLE." WHERE EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum;
");

define("OPAL_COUNT_HOSPITAL_MAP","
    SELECT COUNT(*) AS total FROM ".OPAL_HOSPITAL_MAP_TABLE." WHERE HospitalMapSerNum = :HospitalMapSerNum;
");

define("OPAL_SELECT_ALIAS_EXPRESSIONS_TO_INSERT","
    SELECT msa.*, ae.AliasExpressionSerNum FROM ".OPAL_MASTER_SOURCE_ALIAS_TABLE." msa
    LEFT JOIN ".OPAL_ALIAS_EXPRESSION_TABLE." ae ON ae.masterSourceAliasId = msa.ID
    LEFT JOIN ".OPAL_ALIAS_TABLE." al ON al.AliasSerNum = ae.AliasSerNum
    WHERE ID IN (%%LISTIDS%%)
    AND CASE
        WHEN al.AliasType='Task' THEN (SELECT COUNT(*) FROM ".OPAL_TASK_TABLE." t WHERE t.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        WHEN al.AliasType='Appointment' THEN (SELECT COUNT(*) FROM ".OPAL_APPOINTMENTS_TABLE." a WHERE a.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        ELSE (SELECT COUNT(*) FROM ".OPAL_DOCUMENT_TABLE." d WHERE d.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
    END
    AND deleted = ".NON_DELETED_RECORD.";
");

define("OPAL_COUNT_SOURCE_DB","
    SELECT COUNT(*) AS total FROM ".OPAL_SOURCE_DATABASE_TABLE." WHERE SourceDatabaseSerNum = :SourceDatabaseSerNum
    AND Enabled = ".ACTIVE_RECORD.";
");

define("OPAL_UPDATE_ALIAS","
    UPDATE ".OPAL_ALIAS_TABLE." SET AliasName_FR = :AliasName_FR, AliasName_EN = :AliasName_EN, AliasDescription_FR = :AliasDescription_FR,
    AliasDescription_EN = :AliasDescription_EN, %%EDU_MATERIAL%%,
    %%HOSP_MAP%%, ColorTag = :ColorTag, LastUpdatedBy = :LastUpdatedBy, SessionId = :SessionId
    WHERE AliasSerNum = :AliasSerNum AND (AliasName_FR != :AliasName_FR OR AliasName_EN != :AliasName_EN OR
    AliasDescription_FR != :AliasDescription_FR OR AliasDescription_EN != :AliasDescription_EN OR
    %%EDU_MATERIAL_COND%% OR %%HOSP_MAP_COND%% OR
    ColorTag != :ColorTag)
");

define("OPAL_EDU_MATERIAL_SERNUM", "EducationalMaterialControlSerNum = :EducationalMaterialControlSerNum");
define("OPAL_EDU_MATERIAL_COND", "ifnull(EducationalMaterialControlSerNum, -1) != :EducationalMaterialControlSerNum");

define("OPAL_HOSP_MAP_SERNUM", "HospitalMapSerNum = :HospitalMapSerNum");
define("OPAL_HOSP_MAP_COND", "ifnull(HospitalMapSerNum, -1) != :HospitalMapSerNum");

define("OPAL_GET_ALIAS_EXPRESSION","
    SELECT A.AliasSerNum, A.AliasUpdate, A.SourceDatabaseSerNum, AE.AliasExpressionSerNum, AE.ExpressionName, AE.Description
    FROM ".OPAL_ALIAS_TABLE." A, ".OPAL_ALIAS_EXPRESSION_TABLE." AE
    WHERE A.AliasSerNum = AE.AliasSerNum
    AND A.AliasType = :AliasType
    AND AE.ExpressionName = :ExpressionName
    AND AE.Description = :Description;
");

define("OPAL_DELETE_ALIAS_EXPRESSIONS","
    DELETE ae FROM ".OPAL_ALIAS_EXPRESSION_TABLE." ae LEFT JOIN ".OPAL_MASTER_SOURCE_ALIAS_TABLE." msa ON
    msa.ID = ae.masterSourceAliasId
    RIGHT JOIN ".OPAL_ALIAS_TABLE." al ON al.AliasSerNum = ae.AliasSerNum
    WHERE ae.AliasSerNum = :AliasSerNum AND msa.deleted = ".NON_DELETED_RECORD."
    AND CASE
        WHEN al.AliasType='Task' THEN (SELECT COUNT(*) FROM ".OPAL_TASK_TABLE." t WHERE t.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        WHEN al.AliasType='Appointment' THEN (SELECT COUNT(*) FROM ".OPAL_APPOINTMENTS_TABLE." a WHERE a.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
        ELSE (SELECT COUNT(*) FROM ".OPAL_DOCUMENT_TABLE." d WHERE d.AliasExpressionSerNum = ae.AliasExpressionSerNum) <= 0
    END
    AND ae.masterSourceAliasId NOT IN (%%LIST_SOURCES_UIDS%%);
");

define("OPAL_UPDATE_ALIAS_EXPRESSION", "
    UPDATE ".OPAL_ALIAS_EXPRESSION_TABLE." SET
    AliasSerNum = :AliasSerNum,
    masterSourceAliasId = :masterSourceAliasId,
    ExpressionName = :ExpressionName,
    Description = :Description,
    LastUpdatedBy = :LastUpdatedBy,
    SessionId = :SessionId
    WHERE AliasExpressionSerNum = :AliasExpressionSerNum AND
    (AliasSerNum != :AliasSerNum OR
    masterSourceAliasId != :masterSourceAliasId OR
    ExpressionName != :ExpressionName OR
    Description != :Description OR
    LastUpdatedBy != :LastUpdatedBy OR
    SessionId != :SessionId)
");

define("OPAL_UPDATE_ALIAS_EXPRESSION_WITH_LAST_TRANSFERRED", "
    UPDATE ".OPAL_ALIAS_EXPRESSION_TABLE." SET
    AliasSerNum = :AliasSerNum,
    masterSourceAliasId = :masterSourceAliasId,
    ExpressionName = :ExpressionName,
    Description = :Description,
    LastUpdatedBy = :LastUpdatedBy,
    LastTransferred = :LastTransferred,
    SessionId = :SessionId
    WHERE AliasExpressionSerNum = :AliasExpressionSerNum AND
    (AliasSerNum != :AliasSerNum OR
    masterSourceAliasId != :masterSourceAliasId OR
    ExpressionName != :ExpressionName OR
    Description != :Description OR
    LastUpdatedBy != :LastUpdatedBy OR
    SessionId != :SessionId OR
    LastTransferred != :LastTransferred)
");

define("OPAL_GET_COUNT_ALIASES", "
    SELECT COUNT(*) AS total from ".OPAL_ALIAS_TABLE." WHERE AliasSerNum IN (%%LISTIDS%%);
");

const OPAL_GET_LAST_COMPLETED_QUESTIONNAIRE = "
    SELECT QuestionnaireControlSerNum AS questionnaireControlId, CompletionDate AS completionDate, LastUpdated AS lastUpdated
    FROM ".OPAL_QUESTIONNAIRE_TABLE." WHERE CompletedFlag = " . OPAL_QUESTIONNAIRE_COMPLETED_FLAG . "
    AND PatientSerNum = :PatientSerNum ORDER BY LastUpdated DESC;
";

const OPAL_GET_PATIENTS_COMPLETED_QUESTIONNAIRES = "
    SELECT DISTINCT PHI.MRN AS mrn, PHI.Hospital_Identifier_Type_Code AS site, MAX(Q.CompletionDate) AS completionDate,
    QC.QuestionnaireName_EN AS name_EN, QC.QuestionnaireName_FR AS name_FR, QC.QuestionnaireControlSerNum AS questionnaireControlId,
    Q.LastUpdated AS lastUpdated FROM ".OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE." PHI INNER JOIN ".OPAL_QUESTIONNAIRE_TABLE." Q
    ON Q.PatientSerNum = PHI.PatientSerNum AND Q.CompletedFlag = 1 INNER JOIN ".OPAL_QUESTIONNAIRE_CONTROL_TABLE." QC ON
    QC.QuestionnaireControlSerNum = Q.QuestionnaireControlSerNum %%CONDTION_OPTINAL%% GROUP BY PHI.MRN ORDER BY
    PHI.Hospital_Identifier_Type_Code ASC, PHI.MRN ASC, Q.CompletionDate DESC
";

const OPAL_CONDITION_QUESTIONNAIRES_OPTIONAL = " WHERE QC.QuestionnaireControlSerNum IN (%%QUESTIONNAIRES_LIST%%) ";

const OPAL_GET_STUDIES_QUESTIONNAIRE = "
    SELECT studyId FROM " . OPAL_QUESTIONNAIRE_STUDY_TABLE . " WHERE questionnaireId = :questionnaireId;
";

const OPAL_GET_STUDIES_PATIENT_CONSENTED = "
SELECT s.ID as studyId, s.code, s.title_EN, s.title_FR FROM ".OPAL_STUDY_TABLE." s LEFT JOIN ".OPAL_PATIENT_STUDY_TABLE." ps
ON ps.studyId = s.ID LEFT JOIN ".OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE." phi ON phi.PatientSerNum = ps.patientId
WHERE ps.consentStatus IN (".CONSENT_STATUS_OPAL_CONSENTED.", ".CONSENT_STATUS_OTHER_CONSENTED.")
AND phi.MRN = :MRN AND phi.Hospital_Identifier_Type_Code = :Hospital_Identifier_Type_Code ORDER BY s.ID;
";

const OPAL_GET_APPOINTMENT_FOR_RESOURCE = "
    SELECT * FROM " . OPAL_APPOINTMENTS_TABLE . " WHERE AppointmentAriaSer = :AppointmentAriaSer AND SourceDatabaseSerNum = :SourceDatabaseSerNum;
";

const OPAL_GET_RESOURCE_PENDING = "
    SELECT * FROM ".OPAL_RESOURCE_PENDING_TABLE." WHERE sourceName = :sourceName AND appointmentId = :appointmentId;
";

const OPAL_UPDATE_RESOURCE_PENDING = "
    UPDATE ".OPAL_RESOURCE_PENDING_TABLE." SET resources = :resources, updatedBy = :updatedBy WHERE
    sourceName = :sourceName AND appointmentId = :appointmentId AND `level` = ".RESOURCE_LEVEL_READY.";
";

const OPAL_UPDATE_RESOURCE = "
    UPDATE ".OPAL_RESOURCE_TABLE." SET ResourceName = :ResourceName, ResourceType = :ResourceType WHERE
    SourceDatabaseSerNum = :SourceDatabaseSerNum AND ResourceCode = :ResourceCode;
";

const OPAL_GET_RESOURCES_FOR_RESOURCE_APPOINTMENT = "
    SELECT :AppointmentSerNum AS AppointmentSerNum, NOW() AS DateAdded, '1' AS ExclusiveFlag, '0' AS PrimaryFlag,
    ResourceSerNum FROM ".OPAL_RESOURCE_TABLE." WHERE %%SOURCE_CODE_LIST%%;
";

const DELETE_FROM_RESOURCE_APPOINTMENT = "
    DELETE FROM ".OPAL_RESOURCE_APPOINTMENT_TABLE." WHERE AppointmentSerNum = :AppointmentSerNum AND ResourceSerNum
    NOT IN (%%RESOURCE_ID_LIST%%);
";

const UPDATE_RESOURCE_PENDING_LEVEL_IN_PROCESS = "
    UPDATE ".OPAL_RESOURCE_PENDING_TABLE." rp SET rp.`level` = ".RESOURCE_LEVEL_IN_PROCESS.", updatedBy = :updatedBy
    WHERE ( SELECT COUNT(*) AS total FROM ".OPAL_APPOINTMENTS_TABLE." a LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." s
    ON s.SourceDatabaseSerNum = a.SourceDatabaseSerNum WHERE s.SourceDatabaseName = rp.sourceName AND
    s.Enabled = ".ACTIVE_RECORD." AND a.AppointmentAriaSer = rp.appointmentId AND rp.`level` = ".RESOURCE_LEVEL_READY.") = 1
";

const GET_OLDEST_RESOURCE_PENDING_IN_PROCESS = "
    SELECT rp.*, (SELECT a.AppointmentSerNum FROM ".OPAL_APPOINTMENTS_TABLE." a LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." s
    ON s.SourceDatabaseSerNum = a.SourceDatabaseSerNum WHERE s.SourceDatabaseName = rp.sourceName AND s.Enabled = 1 AND
    a.AppointmentAriaSer = rp.appointmentId) AS AppointmentSerNum, (SELECT s1.SourceDatabaseSerNum FROM
    ".OPAL_SOURCE_DATABASE_TABLE." s1 WHERE s1.SourceDatabaseName = rp.sourceName AND s1.Enabled = 1) AS SourceDatabaseSerNum
    FROM ".OPAL_RESOURCE_PENDING_TABLE." rp WHERE rp.`level` = 2 ORDER BY creationDate ASC LIMIT 1;
";

const OPAL_DELETE_RESOURCE_PENDING = "DELETE FROM " . OPAL_RESOURCE_PENDING_TABLE . " WHERE ID = :ID;";

const UPDATE_APPOINTMENT_CHECKIN = "
    UPDATE ".OPAL_APPOINTMENTS_TABLE." SET Checkin = ".CHECKED_IN." WHERE SourceDatabaseSerNum = :SourceDatabaseSerNum
    AND AppointmentAriaSer = :AppointmentAriaSer AND Checkin = ".NOT_CHECKED_IN.";
";

const OPAL_GET_FIRST_MRN_SITE_BY_SOURCE_APPOINTMENT = "
    SELECT phi.Hospital_Identifier_Type_Code AS site, phi.MRN AS mrn FROM ".OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE." phi
    LEFT JOIN ".OPAL_APPOINTMENTS_TABLE." a ON a.PatientSerNum = phi.PatientSerNum
    LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." s ON s.SourceDatabaseSerNum = a.SourceDatabaseSerNum
    WHERE s.SourceDatabaseName = :SourceDatabaseName AND a.AppointmentAriaSer = :AppointmentAriaSer AND
    Is_Active = ".ACTIVE_RECORD." LIMIT 1;
";

const OPAL_GET_PUBLICATION_SETTINGS = "
    SELECT ID, internalName, opalDB, opalPK FROM ".OPAL_PUBLICATION_SETTING_TABLE." WHERE isUnique = 0;
";

const OPAL_GET_PUBLICATION_SETTINGS_TO_IGNORE = "
    SELECT internalName FROM ".OPAL_PUBLICATION_SETTING_TABLE." WHERE isUnique = 1 UNION ALL SELECT 'CheckedInFlag'
    AS internalName;
";

const OPAL_DELETE_QUESTIONNAIRE_FREQUENCY_EVENTS = "
    DELETE FROM FrequencyEvents WHERE ControlTableSerNum = :ControlTableSerNum AND ControlTable = 'LegacyQuestionnaireControl';
";

const UPDATE_APPOINTMENT_PENDING_LEVEL_IN_PROCESS = "
    UPDATE ".OPAL_APPOINTMENTS_PENDING_TABLE." ap SET ap.`level` = ".APPOINTMENT_LEVEL_IN_PROCESS.", updatedBy = :updatedBy
    WHERE ( SELECT COUNT(*) AS total FROM ".OPAL_APPOINTMENTS_TABLE." a LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." s
    ON s.SourceDatabaseSerNum = a.SourceDatabaseSerNum WHERE s.SourceDatabaseName = ap.sourceName AND
    s.Enabled = ".ACTIVE_RECORD." AND a.AppointmentAriaSer = ap.AppointmentAriaSer AND ap.`level` = ".APPOINTMENT_LEVEL_READY.") = 1
";

const GET_OLDEST_APPOINTMENT_PENDING_IN_PROCESS = "
    SELECT ap.*, (SELECT a.AppointmentSerNum FROM ".OPAL_APPOINTMENTS_TABLE." a LEFT JOIN ".OPAL_SOURCE_DATABASE_TABLE." s
    ON s.SourceDatabaseSerNum = a.SourceDatabaseSerNum WHERE s.SourceDatabaseName = ap.sourceName AND s.Enabled = 1 AND
    a.AppointmentAriaSer = ap.AppointmentAriaSer) AS AppointmentSerNum, (SELECT s1.SourceDatabaseSerNum FROM
    ".OPAL_SOURCE_DATABASE_TABLE." s1 WHERE s1.SourceDatabaseName = ap.sourceName AND s1.Enabled = 1) AS SourceDatabaseSerNum
    FROM ".OPAL_APPOINTMENTS_PENDING_TABLE." ap WHERE ap.`level` = 1 ORDER BY DateAdded ASC;
";

const OPAL_GET_AUDIT_SYSTEM_LAST_DATES = "
    SELECT DISTINCT DATE(creationDate) AS `date` FROM ".OPAL_AUDIT_SYSTEM_TABLE." WHERE creationDate != ''
    AND DATE(creationDate) != CURDATE() ORDER BY `date` DESC LIMIT ".LIMIT_DAYS_AUDIT_SYSTEM_BACKUP.";
";

const OPAL_GET_AUDIT_SYSTEM_ENTRIES_BY_DATE = "
    SELECT * FROM ".OPAL_AUDIT_SYSTEM_TABLE." WHERE DATE(creationDate) = :creationDate;
";

const OPAL_TEMPLATE_AUDIT_SYSTEM = "DROP TABLE IF EXISTS `auditSystem%%DATE_TO_INSERT%%`;
CREATE TABLE `auditSystem%%DATE_TO_INSERT%%` (
	`ID` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'Primary key. Auto-increment.',
	`module` VARCHAR(128) NOT NULL COMMENT 'Name of the module the user accessed' COLLATE 'latin1_swedish_ci',
	`method` VARCHAR(128) NOT NULL COMMENT 'Name of the method in the module the user activated' COLLATE 'latin1_swedish_ci',
	`argument` LONGTEXT NOT NULL COMMENT 'Arguments (if any) passed to the method called.' COLLATE 'latin1_swedish_ci',
	`access` VARCHAR(16) NOT NULL COMMENT 'If the access to the user was GRANTED or DENIED' COLLATE 'latin1_swedish_ci',
	`ipAddress` VARCHAR(64) NOT NULL COMMENT 'IP address of the user' COLLATE 'latin1_swedish_ci',
	`creationDate` DATETIME NOT NULL COMMENT 'Date of the user request',
	`createdBy` VARCHAR(128) NOT NULL COMMENT 'Username of the user who made the request' COLLATE 'latin1_swedish_ci',
	PRIMARY KEY (`ID`) USING BTREE
)
COLLATE='latin1_swedish_ci' ENGINE=InnoDB;
INSERT INTO `auditSystem%%DATE_TO_INSERT%%` (`module`, `method`, `argument`, `access`, `ipAddress`, `creationDate`, `createdBy`) VALUES
%%INSERT_DATA_HERE%%
;";

const OPAL_DELETE_AUDIT_SYSTEM_BY_DATE = "
    DELETE FROM ".OPAL_AUDIT_SYSTEM_TABLE." WHERE DATE(creationDate) = :creationDate;
";

const OPAL_COUNT_AUDIT_SYSTEM_REMAINING_DATES = "
    SELECT COUNT(DISTINCT DATE(creationDate)) AS remaining FROM ".OPAL_AUDIT_SYSTEM_TABLE." WHERE creationDate != ''
    AND DATE(creationDate) != CURDATE();
";

const OPAL_GET_DOCUMENT = "
SELECT DocumentSerNum,PatientSerNum,SourceDatabaseSerNum,
DocumentId, AliasExpressionSerNum, DateAdded 
FROM ".OPAL_DOCUMENT_TABLE." 
WHERE DocumentId = :DocumentId
AND SourceDatabaseSerNum = :SourceDatabaseSerNum;
";

const OPAL_GET_NOTIFICATION_CONTROL_DETAILS = "
SELECT DISTINCT nc.NotificationControlSerNum,
    CASE
        WHEN p.Language = 'EN' THEN nc.Description_EN
        WHEN p.Language = 'FR' THEN nc.Description_FR
    END AS Message,
    CASE 
        WHEN p.Language = 'EN' THEN nc.Name_EN
        WHEN p.Language = 'FR' THEN nc.Name_FR
    END As Name
FROM   ".OPAL_PATIENT_TABLE." p, ".OPAL_NOTIFICATION_CONTROL_TABLE." nc,
        ".OPAL_NOTIFICATION_TYPES_TABLE." nt
WHERE p.PatientSerNum              = :Patientser
    AND nc.NotificationTypeSerNum  = nt.NotificationTypeSerNum
    AND nt.NotificationTypeId      = :Notificationtype
";

const OPAL_GET_PATIENT_DEVICE_IDENTIFIERS = "
SELECT DISTINCT ptdid.PatientDeviceIdentifierSerNum,
    ptdid.RegistrationId, ptdid.DeviceType
FROM ".OPAL_PATIENT_DEVICE_IDENTIFIER_TABLE." ptdid
WHERE
    ptdid.PatientSerNum = :Patientser
    AND ptdid.DeviceType in ('0', '1')
    AND IfNull(RegistrationId, '') <> ''
";

const OPAL_GET_PATIENT_ACCESS_LEVEL = "
SELECT pt.Accesslevel
FROM " . OPAL_PATIENT_TABLE . " pt WHERE pt.PatientSerNum = :PatientSer;";

const OPAL_GET_ALIAS_EXPRESSION_DETAIL = "
SELECT ExpressionName, Description,
    AliasType, AliasName_FR, AliasName_EN,
    AliasDescription_FR, AliasDescription_EN
FROM ".OPAL_ALIAS_EXPRESSION_TABLE." AE, ". OPAL_ALIAS_TABLE." A
WHERE AE.AliasExpressionSerNum = :AliasExpressionSerNum
AND AE.AliasSerNum = A.AliasSerNum;";


const OPAL_GET_STAFF_DETAIL = "
    SELECT StaffSerNum, FirstName, LastName, LastUpdated
     FROM Staff  WHERE SourceDatabaseSerNum = :SourceDatabaseSerNum AND StaffId =:StaffId;
";