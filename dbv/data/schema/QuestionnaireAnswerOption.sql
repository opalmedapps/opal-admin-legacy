CREATE TABLE `QuestionnaireAnswerOption` (
  `serNum` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text_EN` varchar(256) NOT NULL,
  `text_FR` varchar(256) NOT NULL,
  `answertype_serNum` int(11) unsigned NOT NULL,
  `position` int(4) unsigned NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`serNum`),
  KEY `answertype_serNum` (`answertype_serNum`),
  CONSTRAINT `QuestionnaireAnswerOption_ibfk_1` FOREIGN KEY (`answertype_serNum`) REFERENCES `QuestionnaireAnswerType` (`serNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1