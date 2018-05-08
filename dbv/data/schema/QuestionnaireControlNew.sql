CREATE TABLE `QuestionnaireControlNew` (
  `serNum` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name_EN` varchar(128) NOT NULL,
  `name_FR` varchar(128) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `publish` tinyint(1) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) DEFAULT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`serNum`),
  KEY `last_updated_by` (`last_updated_by`),
  CONSTRAINT `QuestionnaireControlNew_ibfk_1` FOREIGN KEY (`last_updated_by`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1