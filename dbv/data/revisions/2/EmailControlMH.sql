CREATE TABLE `EmailControlMH` (
  `EmailControlSerNum` int(11) NOT NULL,
  `RevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Subject_EN` varchar(100) NOT NULL,
  `Subject_FR` varchar(100) NOT NULL,
  `Body_EN` text NOT NULL,
  `Body_FR` text NOT NULL,
  `EmailTypeSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`EmailControlSerNum`,`RevSerNum`),
  KEY `EmailTypeSerNum` (`EmailTypeSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1