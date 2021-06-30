<?php

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

define("QUESTIONNAIRE_STATUS_NEW", 0);
define("QUESTIONNAIRE_STATUS_IN_PROGRESS", 1);
define("QUESTIONNAIRE_STATUS_COMPLETED", 2);

//Definition of all questionnaires table from the questionnaire DB
define("ANSWER_CHECKBOX_TABLE","answerCheckbox");
define("ANSWER_QUESTIONNAIRE_TABLE","answerQuestionnaire");
define("ANSWER_RADIO_BUTTON_TABLE","answerRadioButton");
define("ANSWER_SECTION_TABLE","answerSection");
define("ANSWER_SLIDER_TABLE","answerSlider");
define("ANSWER_DATE_TABLE","answerDate");
define("ANSWER_TIME_TABLE","answerTime");
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