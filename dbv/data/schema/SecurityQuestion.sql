CREATE TABLE `SecurityQuestion` (
  `SecurityQuestionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `QuestionText_EN` varchar(2056) NOT NULL,
  `QuestionText_FR` varchar(2056) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`SecurityQuestionSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1