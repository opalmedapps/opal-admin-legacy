CREATE TABLE `OAActivityLog` (
  `ActivitySerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Activity` varchar(255) NOT NULL,
  `OAUserSerNum` int(11) NOT NULL,
  `SessionId` varchar(255) NOT NULL,
  `DateAdded` datetime NOT NULL,
  PRIMARY KEY (`ActivitySerNum`),
  KEY `OAUserSerNum` (`OAUserSerNum`),
  CONSTRAINT `OAActivityLog_ibfk_1` FOREIGN KEY (`OAUserSerNum`) REFERENCES `OAUser` (`OAUserSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1