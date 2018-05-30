CREATE TABLE `Filters` (
  `FilterSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `ControlTable` varchar(100) NOT NULL,
  `ControlTableSerNum` int(11) NOT NULL,
  `FilterType` varchar(100) NOT NULL,
  `FilterId` varchar(150) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`FilterSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  KEY `ControlTableSerNum` (`ControlTableSerNum`),
  CONSTRAINT `Filters_ibfk_1` FOREIGN KEY (`LastUpdatedBy`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1