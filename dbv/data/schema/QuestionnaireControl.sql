CREATE TABLE `QuestionnaireControl` (
  `QuestionnaireControlSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `QuestionnaireDBSerNum` int(11) NOT NULL,
  `QuestionnaireName_EN` varchar(2056) NOT NULL,
  `QuestionnaireName_FR` varchar(2056) NOT NULL,
  `Intro_EN` text NOT NULL,
  `Intro_FR` text NOT NULL,
  `PublishFlag` tinyint(4) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `LastPublished` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`QuestionnaireControlSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  CONSTRAINT `QuestionnaireControl_ibfk_1` FOREIGN KEY (`LastUpdatedBy`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1