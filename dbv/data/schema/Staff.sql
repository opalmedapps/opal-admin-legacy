CREATE TABLE `Staff` (
  `StaffSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `StaffId` varchar(25) NOT NULL,
  `FirstName` varchar(30) NOT NULL,
  `LastName` varchar(30) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`StaffSerNum`),
  KEY `StaffId` (`StaffId`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1