CREATE TABLE `QuestionnaireControlNewMH` (
  `serNum` int(11) unsigned NOT NULL,
  `rev_serNum` int(11) NOT NULL AUTO_INCREMENT,
  `name_EN` varchar(128) NOT NULL,
  `name_FR` varchar(128) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `publish` tinyint(1) NOT NULL,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) unsigned DEFAULT NULL,
  `created_by` int(11) unsigned NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `modification_action` varchar(25) NOT NULL,
  PRIMARY KEY (`serNum`,`rev_serNum`),
  KEY `last_updated_by` (`last_updated_by`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1