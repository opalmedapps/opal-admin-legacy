CREATE TABLE `AliasExpressionMH` (
  `AliasSerNum` int(11) NOT NULL DEFAULT '0',
  `ExpressionName` varchar(250) NOT NULL,
  `Description` varchar(250) NOT NULL,
  `RevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `LastTransferred` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ExpressionName`,`Description`,`RevSerNum`),
  KEY `AliasSerNum` (`AliasSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1