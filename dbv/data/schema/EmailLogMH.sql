CREATE TABLE `EmailLogMH` (
  `EmailLogSerNum` int(11) NOT NULL,
  `EmailLogRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `EmailControlSerNum` int(11) NOT NULL,
  `Status` varchar(5) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  PRIMARY KEY (`EmailLogSerNum`,`EmailLogRevSerNum`),
  KEY `EmailControlSerNum` (`EmailControlSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1