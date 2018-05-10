CREATE TABLE `TaskMH` (
  `TaskSerNum` int(11) NOT NULL,
  `TaskRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `AliasExpressionSerNum` int(11) NOT NULL,
  `PrioritySerNum` int(11) NOT NULL,
  `DiagnosisSerNum` int(11) NOT NULL,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `TaskAriaSer` int(11) NOT NULL,
  `Status` varchar(100) NOT NULL,
  `State` varchar(25) NOT NULL,
  `DueDateTime` datetime NOT NULL,
  `CreationDate` datetime NOT NULL,
  `CompletionDate` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TaskSerNum`,`TaskRevSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `AliasExpressionSerNum` (`AliasExpressionSerNum`),
  KEY `PrioritySerNum` (`PrioritySerNum`),
  KEY `DiagnosisSerNum` (`DiagnosisSerNum`),
  KEY `TaskAriaSer` (`TaskAriaSer`),
  KEY `SourceDatabaseSerNum` (`SourceDatabaseSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC