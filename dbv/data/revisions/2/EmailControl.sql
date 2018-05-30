CREATE TABLE `EmailControl` (
  `EmailControlSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Subject_EN` varchar(100) NOT NULL,
  `Subject_FR` varchar(100) NOT NULL,
  `Body_EN` text NOT NULL,
  `Body_FR` text NOT NULL,
  `EmailTypeSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`EmailControlSerNum`),
  KEY `EmailTypeSerNum` (`EmailTypeSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  CONSTRAINT `EmailControl_ibfk_2` FOREIGN KEY (`EmailTypeSerNum`) REFERENCES `EmailType` (`EmailTypeSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `EmailControl_ibfk_1` FOREIGN KEY (`LastUpdatedBy`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1