CREATE TABLE `Diagnosis` (
  `DiagnosisSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `DiagnosisAriaSer` int(32) NOT NULL,
  `DiagnosisCode` varchar(50) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Description_EN` text NOT NULL,
  `Description_FR` text NOT NULL,
  `Stage` varchar(32) DEFAULT NULL,
  `StageCriteria` varchar(32) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`DiagnosisSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `DiagnosisAriaSer` (`DiagnosisAriaSer`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  CONSTRAINT `Diagnosis_ibfk_2` FOREIGN KEY (`SourceDatabaseSerNum`) REFERENCES `SourceDatabase` (`SourceDatabaseSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Diagnosis_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1