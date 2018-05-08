CREATE TABLE `PostControlMH` (
  `PostControlSerNum` int(11) NOT NULL,
  `RevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PostType` varchar(100) NOT NULL,
  `PublishFlag` int(11) NOT NULL DEFAULT '0',
  `PostName_FR` varchar(100) NOT NULL,
  `PostName_EN` varchar(100) NOT NULL,
  `Body_FR` text NOT NULL,
  `Body_EN` text NOT NULL,
  `PublishDate` datetime DEFAULT NULL,
  `Disabled` tinyint(1) NOT NULL DEFAULT '0',
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastPublished` datetime NOT NULL DEFAULT '2002-01-01 00:00:00',
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`PostControlSerNum`,`RevSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1