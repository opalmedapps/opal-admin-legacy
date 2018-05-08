CREATE TABLE `QuestionnaireAnswerText` (
  `serNum` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `answer_serNum` bigint(20) unsigned NOT NULL,
  `answer_text` text NOT NULL,
  `answered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`serNum`),
  KEY `answer_serNum` (`answer_serNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1