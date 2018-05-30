CREATE TABLE `UsersMH` (
  `UserSerNum` int(11) NOT NULL,
  `UserRevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SessionId` text NOT NULL,
  `UserType` varchar(255) NOT NULL,
  `UserTypeSerNum` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(512) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserSerNum`,`UserRevSerNum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1