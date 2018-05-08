CREATE TABLE `Questiongroup_library` (
  `questiongroup_serNum` bigint(20) unsigned NOT NULL,
  `library_serNum` int(11) unsigned NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`questiongroup_serNum`,`library_serNum`),
  KEY `library_serNum` (`library_serNum`),
  CONSTRAINT `Questiongroup_library_ibfk_1` FOREIGN KEY (`questiongroup_serNum`) REFERENCES `Questiongroup` (`serNum`) ON UPDATE CASCADE,
  CONSTRAINT `Questiongroup_library_ibfk_2` FOREIGN KEY (`library_serNum`) REFERENCES `QuestionnaireLibrary` (`serNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1