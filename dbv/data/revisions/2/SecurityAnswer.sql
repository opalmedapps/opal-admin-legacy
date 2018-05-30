CREATE TABLE `SecurityAnswer` (
  `SecurityAnswerSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SecurityQuestionSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `AnswerText` varchar(2056) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`SecurityAnswerSerNum`),
  UNIQUE KEY `SecurityQuestionSerNum` (`SecurityQuestionSerNum`,`PatientSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  CONSTRAINT `SecurityAnswer_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `SecurityAnswer_ibfk_2` FOREIGN KEY (`SecurityQuestionSerNum`) REFERENCES `SecurityQuestion` (`SecurityQuestionSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1