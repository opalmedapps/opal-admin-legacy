CREATE TABLE `StatusAlias` (
  `StatusAliasSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `Name` varchar(30) NOT NULL,
  `Expression` varchar(45) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`StatusAliasSerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  CONSTRAINT `StatusAlias_ibfk_1` FOREIGN KEY (`SourceDatabaseSerNum`) REFERENCES `SourceDatabase` (`SourceDatabaseSerNum`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1