CREATE TABLE `Doctor` (
  `DoctorSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `DoctorAriaSer` int(20) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `LastName` varchar(100) NOT NULL,
  `Role` varchar(100) NOT NULL,
  `Workplace` varchar(100) NOT NULL,
  `Email` text,
  `Phone` int(20) DEFAULT NULL,
  `Address` text,
  `ProfileImage` varchar(255) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DoctorSerNum`),
  KEY `DoctorAriaSer` (`DoctorAriaSer`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1