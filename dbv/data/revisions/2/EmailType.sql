CREATE TABLE `EmailType` (
  `EmailTypeSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EmailTypeId` varchar(100) NOT NULL,
  `EmailTypeName` varchar(200) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EmailTypeSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1