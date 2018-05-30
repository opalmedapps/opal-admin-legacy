CREATE TABLE `EducationalMaterial` (
  `EducationalMaterialSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialSerNum`),
  KEY `EducationalMaterialSerNum` (`EducationalMaterialControlSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`),
  CONSTRAINT `EducationalMaterial_ibfk_4` FOREIGN KEY (`CronLogSerNum`) REFERENCES `CronLog` (`CronLogSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `EducationalMaterial_ibfk_2` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `EducationalMaterial_ibfk_3` FOREIGN KEY (`EducationalMaterialControlSerNum`) REFERENCES `EducationalMaterialControl` (`EducationalMaterialControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1