CREATE TABLE `PatientsForPatientsMH` (
  `PatientsForPatientsSerNum` int(11) NOT NULL,
  `PatientsForPatientsRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `PostControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientsForPatientsSerNum`,`PatientsForPatientsRevSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `PostControlSerNum` (`PostControlSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1