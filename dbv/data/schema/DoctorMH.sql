CREATE TABLE `DoctorMH` (
  `DoctorSerNum` int(11) NOT NULL,
  `DoctorRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
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
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DoctorSerNum`,`DoctorRevSerNum`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1