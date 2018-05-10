CREATE TABLE `PatientsForPatientsPersonnel` (
  `PatientsForPatientsPersonnelSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Bio_EN` text NOT NULL,
  `Bio_FR` text NOT NULL,
  `ProfileImage` varchar(255) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientsForPatientsPersonnelSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1