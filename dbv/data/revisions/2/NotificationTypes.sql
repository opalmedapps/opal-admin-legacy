CREATE TABLE `NotificationTypes` (
  `NotificationTypeSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `NotificationTypeId` varchar(100) NOT NULL,
  `NotificationTypeName` varchar(200) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`NotificationTypeSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1