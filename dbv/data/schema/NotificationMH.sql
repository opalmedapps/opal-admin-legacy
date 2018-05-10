CREATE TABLE `NotificationMH` (
  `NotificationSerNum` int(11) NOT NULL,
  `NotificationRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `NotificationControlSerNum` int(11) NOT NULL,
  `RefTableRowSerNum` int(11) NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationSerNum`,`NotificationRevSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `NotificationControlSerNum` (`NotificationControlSerNum`),
  KEY `RefTableRowSerNum` (`RefTableRowSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1