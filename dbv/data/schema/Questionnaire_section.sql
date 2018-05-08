CREATE TABLE `Questionnaire_section` (
  `questionnaire_serNum` int(11) unsigned NOT NULL,
  `section_serNum` int(11) unsigned NOT NULL,
  `position` int(11) unsigned NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`questionnaire_serNum`,`section_serNum`),
  KEY `section_serNum` (`section_serNum`),
  CONSTRAINT `Questionnaire_section_ibfk_1` FOREIGN KEY (`questionnaire_serNum`) REFERENCES `QuestionnaireControlNew` (`serNum`) ON UPDATE CASCADE,
  CONSTRAINT `Questionnaire_section_ibfk_2` FOREIGN KEY (`section_serNum`) REFERENCES `QuestionnaireSection` (`serNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1