CREATE TABLE `TestResultExpressionMH` (
  `TestResultControlSerNum` int(11) NOT NULL,
  `ExpressionName` varchar(100) NOT NULL,
  `RevSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `LastPublished` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ExpressionName`,`RevSerNum`),
  KEY `TestResultControlSerNum` (`TestResultControlSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  KEY `ExpressionName` (`ExpressionName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1