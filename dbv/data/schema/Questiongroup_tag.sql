CREATE TABLE `Questiongroup_tag` (
  `questiongroup_serNum` bigint(20) unsigned NOT NULL,
  `tag_serNum` int(11) unsigned NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`questiongroup_serNum`,`tag_serNum`),
  KEY `tag_serNum` (`tag_serNum`),
  CONSTRAINT `Questiongroup_tag_ibfk_1` FOREIGN KEY (`questiongroup_serNum`) REFERENCES `Questiongroup` (`serNum`) ON UPDATE CASCADE,
  CONSTRAINT `Questiongroup_tag_ibfk_2` FOREIGN KEY (`tag_serNum`) REFERENCES `QuestionnaireTag` (`serNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1