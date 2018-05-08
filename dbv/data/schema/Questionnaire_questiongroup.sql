CREATE TABLE `Questionnaire_questiongroup` (
  `questionnaire_serNum` int(11) unsigned NOT NULL,
  `questiongroup_serNum` bigint(20) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `section_serNum` int(11) unsigned NOT NULL DEFAULT '1',
  `optional` tinyint(1) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`questionnaire_serNum`,`questiongroup_serNum`),
  KEY `questiongroup_serNum` (`questiongroup_serNum`),
  KEY `section_serNum` (`section_serNum`),
  CONSTRAINT `questionnaire_questiongroup_ibfk_1` FOREIGN KEY (`questionnaire_serNum`) REFERENCES `QuestionnaireControlNew` (`serNum`) ON UPDATE CASCADE,
  CONSTRAINT `questionnaire_questiongroup_ibfk_2` FOREIGN KEY (`questiongroup_serNum`) REFERENCES `Questiongroup` (`serNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1