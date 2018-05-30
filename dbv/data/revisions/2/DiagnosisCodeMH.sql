CREATE TABLE `DiagnosisCodeMH` (
  `DiagnosisTranslationSerNum` int(11) NOT NULL,
  `SourceUID` int(11) NOT NULL,
  `RevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `DiagnosisCode` varchar(100) NOT NULL,
  `Description` text NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`SourceUID`,`RevSerNum`),
  KEY `DiagnosisTranslationSerNum` (`DiagnosisTranslationSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  KEY `SourceUID` (`SourceUID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1