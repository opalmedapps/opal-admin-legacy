CREATE TABLE `QuestionnaireAnswerType` (
  `serNum` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name_EN` varchar(128) NOT NULL,
  `name_FR` varchar(128) NOT NULL,
  `category_EN` varchar(128) DEFAULT NULL,
  `category_FR` varchar(128) DEFAULT NULL,
  `private` tinyint(1) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`serNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1