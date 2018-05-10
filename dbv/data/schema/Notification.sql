CREATE TABLE `Notification` (
  `NotificationSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `NotificationControlSerNum` int(11) NOT NULL,
  `RefTableRowSerNum` int(11) NOT NULL,
  `DateAdded` datetime DEFAULT NULL,
  `ReadStatus` tinyint(1) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationSerNum`),
  KEY `NotificationControlSerNum` (`NotificationControlSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `RefTableRowSerNum` (`RefTableRowSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`),
  CONSTRAINT `Notification_ibfk_3` FOREIGN KEY (`CronLogSerNum`) REFERENCES `CronLog` (`CronLogSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Notification_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Notification_ibfk_2` FOREIGN KEY (`NotificationControlSerNum`) REFERENCES `NotificationControl` (`NotificationControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1