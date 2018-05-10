CREATE TABLE `DiagnosisCode` (
  `DiagnosisCodeSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `DiagnosisTranslationSerNum` int(11) NOT NULL,
  `SourceUID` int(11) NOT NULL,
  `DiagnosisCode` varchar(100) NOT NULL,
  `Description` text NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`DiagnosisCodeSerNum`),
  UNIQUE KEY `SourceUID` (`SourceUID`),
  KEY `DiagnosisTranslationSerNum` (`DiagnosisTranslationSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  CONSTRAINT `DiagnosisCode_ibfk_1` FOREIGN KEY (`DiagnosisTranslationSerNum`) REFERENCES `DiagnosisTranslation` (`DiagnosisTranslationSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `DiagnosisCode_ibfk_2` FOREIGN KEY (`LastUpdatedBy`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1