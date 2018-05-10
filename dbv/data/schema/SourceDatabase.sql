CREATE TABLE `SourceDatabase` (
  `SourceDatabaseSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceDatabaseName` varchar(255) NOT NULL,
  `Enabled` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`SourceDatabaseSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1