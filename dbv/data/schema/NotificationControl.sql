CREATE TABLE `NotificationControl` (
  `NotificationControlSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Name_EN` varchar(100) NOT NULL,
  `Name_FR` varchar(100) NOT NULL,
  `Description_EN` text NOT NULL,
  `Description_FR` text NOT NULL,
  `NotificationType` varchar(100) NOT NULL,
  `NotificationTypeSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`NotificationControlSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  KEY `NotificationTypeSerNum` (`NotificationTypeSerNum`),
  CONSTRAINT `NotificationControl_ibfk_1` FOREIGN KEY (`LastUpdatedBy`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `NotificationControl_ibfk_2` FOREIGN KEY (`NotificationTypeSerNum`) REFERENCES `NotificationTypes` (`NotificationTypeSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1