CREATE TABLE `Users` (
  `UserSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `UserType` varchar(255) NOT NULL,
  `UserTypeSerNum` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL COMMENT 'This field is Firebase User UID',
  `Password` varchar(512) NOT NULL,
  `SessionId` text,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserSerNum`),
  KEY `UserTypeSerNum` (`UserTypeSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1