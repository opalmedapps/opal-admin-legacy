CREATE TABLE `EmailLog` (
  `EmailLogSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `EmailControlSerNum` int(11) NOT NULL,
  `Status` varchar(5) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EmailLogSerNum`),
  KEY `EmailControlSerNum` (`EmailControlSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  CONSTRAINT `EmailLog_ibfk_3` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `EmailLog_ibfk_1` FOREIGN KEY (`EmailControlSerNum`) REFERENCES `EmailControl` (`EmailControlSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `EmailLog_ibfk_2` FOREIGN KEY (`CronLogSerNum`) REFERENCES `CronLog` (`CronLogSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1