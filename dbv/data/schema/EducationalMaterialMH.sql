CREATE TABLE `EducationalMaterialMH` (
  `EducationalMaterialSerNum` int(11) NOT NULL,
  `EducationalMaterialRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialSerNum`,`EducationalMaterialRevSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`),
  KEY `EducationalMaterialControlSerNum` (`EducationalMaterialControlSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1