CREATE TABLE IF NOT EXISTS `SecurityQuestion` (
  `SecurityQuestionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `QuestionText_EN` varchar(2056) NOT NULL,
  `QuestionText_FR` varchar(2056) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Active` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 = Disable / 1 = Enable',
  PRIMARY KEY (`SecurityQuestionSerNum`),
  KEY `Index 2` (`Active`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 