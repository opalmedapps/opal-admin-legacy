CREATE TABLE `QuestionnaireControlMH` (
  `QuestionnaireControlSerNum` int(11) NOT NULL,
  `RevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `QuestionnaireDBSerNum` int(11) NOT NULL,
  `QuestionnaireName_EN` varchar(2056) NOT NULL,
  `QuestionnaireName_FR` varchar(2056) NOT NULL,
  `Intro_EN` text NOT NULL,
  `Intro_FR` text NOT NULL,
  `PublishFlag` tinyint(4) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `LastPublished` datetime NOT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`QuestionnaireControlSerNum`,`RevSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1