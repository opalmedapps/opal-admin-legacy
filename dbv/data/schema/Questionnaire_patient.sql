CREATE TABLE `Questionnaire_patient` (
  `serNum` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `questionnaire_serNum` int(11) unsigned NOT NULL,
  `patient_serNum` int(11) unsigned NOT NULL,
  `user_serNum` int(11) unsigned NOT NULL DEFAULT '0',
  `submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`serNum`),
  KEY `questionnaire_serNum` (`questionnaire_serNum`),
  CONSTRAINT `questionnaire_patient_ibfk_1` FOREIGN KEY (`questionnaire_serNum`) REFERENCES `QuestionnaireControlNew` (`serNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1