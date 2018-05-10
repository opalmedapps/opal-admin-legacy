CREATE TABLE `NotificationControlMH` (
  `NotificationControlSerNum` int(11) NOT NULL,
  `RevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Name_EN` varchar(100) NOT NULL,
  `Name_FR` varchar(100) NOT NULL,
  `Description_EN` text NOT NULL,
  `Description_FR` text NOT NULL,
  `NotificationTypeSerNum` varchar(100) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`NotificationControlSerNum`,`RevSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  KEY `NotificationTypeSerNum` (`NotificationTypeSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1