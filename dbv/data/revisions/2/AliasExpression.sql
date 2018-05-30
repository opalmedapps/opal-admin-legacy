CREATE TABLE `AliasExpression` (
  `AliasExpressionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `AliasSerNum` int(11) NOT NULL DEFAULT '0',
  `ExpressionName` varchar(250) NOT NULL,
  `Description` varchar(250) NOT NULL,
  `LastTransferred` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`AliasExpressionSerNum`),
  UNIQUE KEY `ExpressionName` (`ExpressionName`,`Description`),
  KEY `AliasSerNum` (`AliasSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  CONSTRAINT `AliasExpression_ibfk_4` FOREIGN KEY (`AliasSerNum`) REFERENCES `Alias` (`AliasSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `AliasExpression_ibfk_5` FOREIGN KEY (`LastUpdatedBy`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1