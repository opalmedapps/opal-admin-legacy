CREATE TABLE `AnnouncementMH` (
  `AnnouncementSerNum` int(11) NOT NULL,
  `AnnouncementRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronLogSerNum` int(11) DEFAULT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `PostControlSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `ReadStatus` int(11) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AnnouncementSerNum`,`AnnouncementRevSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `PostControlSerNum` (`PostControlSerNum`),
  KEY `CronLogSerNum` (`CronLogSerNum`),
  KEY `ReadStatus` (`ReadStatus`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1