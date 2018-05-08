CREATE TABLE `QuestionnaireAnswer` (
  `serNum` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_serNum` bigint(20) unsigned NOT NULL,
  `answeroption_serNum` bigint(20) unsigned NOT NULL,
  `questionnaire_patient_serNum` bigint(20) unsigned NOT NULL,
  `answered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`serNum`),
  KEY `question_serNum` (`question_serNum`),
  KEY `answeroption_serNum` (`answeroption_serNum`),
  KEY `questionnaire_patient_serNum` (`questionnaire_patient_serNum`),
  CONSTRAINT `QuestionnaireAnswer_ibfk_1` FOREIGN KEY (`question_serNum`) REFERENCES `QuestionnaireQuestion` (`serNum`) ON UPDATE CASCADE,
  CONSTRAINT `QuestionnaireAnswer_ibfk_2` FOREIGN KEY (`answeroption_serNum`) REFERENCES `QuestionnaireAnswerOption` (`serNum`) ON UPDATE CASCADE,
  CONSTRAINT `QuestionnaireAnswer_ibfk_3` FOREIGN KEY (`questionnaire_patient_serNum`) REFERENCES `Questionnaire_patient` (`serNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1