CREATE TABLE `Priority` (
  `PrioritySerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `PriorityAriaSer` int(11) NOT NULL,
  `PriorityDateTime` datetime NOT NULL,
  `PriorityCode` varchar(25) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PrioritySerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `PriorityAriaSer` (`PriorityAriaSer`),
  CONSTRAINT `Priority_ibfk_1` FOREIGN KEY (`SourceDatabaseSerNum`) REFERENCES `SourceDatabase` (`SourceDatabaseSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1