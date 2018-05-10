CREATE TABLE `Announcement` (
  `AnnouncementSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `PostControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AnnouncementSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`),
  KEY `PostControlSerNum` (`PostControlSerNum`),
  KEY `ReadStatus` (`ReadStatus`),
  CONSTRAINT `Announcement_ibfk_3` FOREIGN KEY (`CronLogSerNum`) REFERENCES `CronLog` (`CronLogSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Announcement_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `Announcement_ibfk_2` FOREIGN KEY (`PostControlSerNum`) REFERENCES `PostControl` (`PostControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1