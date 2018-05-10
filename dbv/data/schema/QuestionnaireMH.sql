CREATE TABLE `QuestionnaireMH` (
  `QuestionnaireSerNum` int(11) NOT NULL,
  `QuestionnaireRevSerNum` int(11) NOT NULL,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `QuestionnaireControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `PatientQuestionnaireDBSerNum` int(11) DEFAULT NULL,
  `CompletedFlag` tinyint(4) NOT NULL,
  `CompletionDate` datetime DEFAULT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  PRIMARY KEY (`QuestionnaireSerNum`,`QuestionnaireRevSerNum`),
  KEY `QuestionnaireControlSerNum` (`QuestionnaireControlSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`),
  KEY `PatientQuestionnaireDBSerNum` (`PatientQuestionnaireDBSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1