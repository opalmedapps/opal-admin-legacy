CREATE TABLE `AppointmentCheckin` (
  `AliasSerNum` int(11) NOT NULL,
  `CheckinPossible` tinyint(4) NOT NULL,
  `CheckinInstruction_EN` text NOT NULL,
  `CheckinInstruction_FR` text NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`AliasSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  CONSTRAINT `AppointmentCheckin_ibfk_3` FOREIGN KEY (`LastUpdatedBy`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `AppointmentCheckin_ibfk_1` FOREIGN KEY (`AliasSerNum`) REFERENCES `Alias` (`AliasSerNum`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1