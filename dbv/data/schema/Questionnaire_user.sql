CREATE TABLE `Questionnaire_user` (
  `questionnaire_serNum` int(11) unsigned NOT NULL,
  `user_serNum` int(11) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_updated_by` int(11) unsigned NOT NULL,
  `created_by` int(11) unsigned NOT NULL,
  PRIMARY KEY (`questionnaire_serNum`,`user_serNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1