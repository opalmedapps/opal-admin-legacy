<?php

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
define("OPAL_OAUSER_ACTIVITY_LOG_TABLE","OAActivityLog");
define("OPAL_ALIAS_EXPRESSION_MH_TABLE","AliasExpressionMH");
define("OPAL_DIAGNOSIS_TRANSLATION_MH_TABLE","DiagnosisTranslationMH");
define("OPAL_DIAGNOSIS_CODE_MH_TABLE","DiagnosisCodeMH");
define("OPAL_QUESTIONNAIRE_CONTROL_TABLE","QuestionnaireControl");
define("OPAL_QUESTIONNAIRE_TABLE","Questionnaire");
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
define("OPAL_AUDIT_SYSTEM_TABLE","auditSystem");
define("OPAL_CATEGORY_MODULE_TABLE","categoryModule");
define("OPAL_MODULE_PUBLICATION_SETTING_TABLE","modulePublicationSetting");
define("OPAL_PUBLICATION_SETTING_TABLE","publicationSetting");
define("OPAL_POST_TABLE","PostControl");
define("OPAL_TX_TEAM_MESSAGE_TABLE","TxTeamMessage");
define("OPAL_ANNOUNCEMENT_TABLE","Announcement");
define("OPAL_PATIENTS_FOR_PATIENTS_TABLE","PatientsForPatients");
define("OPAL_EDUCATION_MATERIAL_TABLE","EducationalMaterial");
define("OPAL_EDUCATION_MATERIAL_CONTROL_TABLE","EducationalMaterialControl");
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
define("OPAL_MASTER_SOURCE_TEST_RESULT_TABLE","v_masterSourceTestResult");
define("OPAL_ALIAS_EXPRESSION_TABLE","AliasExpression");
define("OPAL_DOCTOR_TABLE","Doctor");
define("OPAL_STATUS_ALIAS_TABLE","StatusAlias");
define("OPAL_ALIAS_TABLE","Alias");
define("OPAL_DIAGNOSIS_TRANSLATION_TABLE","DiagnosisTranslation");
define("OPAL_PATIENT_TABLE","Patient");
define("OPAL_PATIENT_STUDY_TABLE","patientStudy");
define("OPAL_QUESTIONNAIRE_STUDY_TABLE","questionnaireStudy");
define("OPAL_TEST_RESULT_EXPRESSION_TABLE","TestResultExpression");
//define("OPAL_TEST_RESULT_ADD_LINKS_TABLE","TestResultAdditionalLinks");
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
define("OPAL_PATIENT_HOSPITAL_IDENTIFIER_TABLE","Patient_Hospital_Identifier");
define("OPAL_DIAGNOSIS_TABLE","Diagnosis");
define("OPAL_APPOINTMENTS_TABLE", "Appointment");
define("OPAL_APPOINTMENTS_PENDING_TABLE", "AppointmentPending");
define("OPAL_APPOINTMENTS_PENDING_MH_TABLE", "AppointmentPendingMH");
define("OPAL_APPOINTMENT_CHECKIN_TABLE","AppointmentCheckin");
define("OPAL_RESOURCE_TABLE", "Resource");
define("OPAL_RESOURCE_APPOINTMENT_TABLE", "ResourceAppointment");
define("OPAL_PATIENT_CONTROL_TABLE", "PatientControl");
define("OPAL_EDUCATIONAL_MATERIAL_TABLE", "EducationalMaterial");
define("OPAL_LEGACY_TEST_RESULT_TABLE", "TestResult");
define("OPAL_PATIENT_TEST_RESULT_TABLE", "PatientTestResult");
define("OPAL_TEST_EXPRESSION_TABLE", "TestExpression");
define("OPAL_TEST_GROUP_EXPRESSION_TABLE", "TestGroupExpression");
define("OPAL_TEST_CONTROL_TABLE", "TestControl");
define("OPAL_NOTIFICATION_TABLE", "Notification");
define("OPAL_TASK_TABLE", "Task");
define("OPAL_PRIORITY_TABLE", "Priority");
define("OPAL_DOCUMENT_TABLE", "Document");
define("OPAL_USERS_TABLE", "Users");
define("OPAL_TEST_RESULT_CONTROL_TABLE","TestResultControl");
define("OPAL_PATIENT_ACTIVITY_LOG_TABLE","PatientActivityLog");
define("OPAL_PATIENT_DEVICE_IDENTIFIER_TABLE", "PatientDeviceIdentifier");
define("OPAL_APPOINTMENT_CHECK_IN_TABLE", "AppointmentCheckin");
define("OPAL_PUSH_NOTIFICATION_TABLE", "PushNotification");
define("OPAL_STAFF_TABLE", "Staff");
define("OPAL_SECURITY_ANSWER_TABLE", "SecurityAnswer");
define("OPAL_SECURITY_QUESTION_TABLE", "SecurityQuestion");
define("OPAL_ACCESS_LEVEL_TABLE", "accesslevel");

//Definition of the primary keys of the opalDB database
define("OPAL_POST_PK","PostControlSerNum");
define("OPAL_RESOURCE_PENDING_TABLE", "resourcePending");
define("OPAL_RESOURCE_PENDING_ERROR_TABLE", "resourcePendingError");
define("OPAL_RESOURCE_PENDING_MH_TABLE", "resourcePendingMH");
define("OPAL_PATIENT_DOCTOR_TABLE", "PatientDoctor");

// //Define Cronjob patient control table names
// define("OPAL_CRON_CONTROL_PATIENT_DOCUMENT","cronControlPatient_Document");
// define("OPAL_CRON_CONTROL_PATIENT_EDUCATION_MATERIAL", "cronControlPatient_EducationalMaterial");
// define("OPAL_CRON_CONTROL_PATIENT_ANNOUNCEMENT", "cronControlPatient_Announcement");
// define("OPAL_CRON_CONTROL_PATIENT_LEGACY_QUESTIONNAIRE", "cronControlPatient_LegacyQuestionnaire");
// define("OPAL_CRON_CONTROL_PATIENT_PATIENTS_FOR_PATIENTS","cronControlPatient_PatientsForPatients");
// define("OPAL_CRON_CONTROL_PATIENT_TREATMENT_TEAM_MESSAGE", "cronControlPatient_TreatmentTeamMessage");