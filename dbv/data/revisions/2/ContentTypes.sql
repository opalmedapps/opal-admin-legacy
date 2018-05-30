CREATE TABLE `ContentTypes` (
  `ContentTypeSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `TypeId` varchar(100) NOT NULL,
  `TypeName` varchar(100) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ContentTypeSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1