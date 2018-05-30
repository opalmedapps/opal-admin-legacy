CREATE TABLE `OAUser` (
  `OAUserSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(1000) NOT NULL,
  `Password` varchar(1000) NOT NULL,
  `Language` enum('EN','FR') NOT NULL DEFAULT 'EN',
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`OAUserSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1