CREATE TABLE `Patient` (
  `PatientSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientAriaSer` int(11) NOT NULL,
  `PatientId` varchar(50) NOT NULL,
  `PatientId2` varchar(50) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Alias` varchar(100) DEFAULT NULL,
  `ProfileImage` longtext,
  `Sex` varchar(25) NOT NULL,
  `DateOfBirth` datetime NOT NULL,
  `Age` int(11) NOT NULL,
  `TelNum` bigint(11) DEFAULT NULL,
  `EnableSMS` tinyint(4) NOT NULL DEFAULT '0',
  `Email` varchar(50) NOT NULL,
  `Language` enum('EN','FR','SN') NOT NULL,
  `SSN` varchar(16) NOT NULL,
  `AccessLevel` enum('1','2','3') NOT NULL DEFAULT '1',
  `RegistrationDate` datetime NOT NULL DEFAULT '2018-01-01 00:00:00',
  `ConsentFormExpirationDate` datetime DEFAULT NULL,
  `BlockedStatus` tinyint(4) NOT NULL DEFAULT '0',
  `StatusReasonTxt` text NOT NULL,
  `DeathDate` datetime DEFAULT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientSerNum`),
  UNIQUE KEY `SSN` (`SSN`),
  KEY `PatientAriaSer` (`PatientAriaSer`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1