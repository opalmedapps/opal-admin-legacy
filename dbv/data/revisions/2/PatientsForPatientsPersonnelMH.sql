CREATE TABLE `PatientsForPatientsPersonnelMH` (
  `PatientsForPatientsPersonnelSerNum` int(11) NOT NULL,
  `PatientsForPatientsPersonnelRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(255) NOT NULL,
  `LastName` int(11) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `Bio_EN` text NOT NULL,
  `Bio_FR` text NOT NULL,
  `ProfileImage` varchar(255) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientsForPatientsPersonnelSerNum`,`PatientsForPatientsPersonnelRevSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1