CREATE TABLE `TestResultExpression` (
  `TestResultExpressionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `TestResultControlSerNum` int(11) NOT NULL,
  `ExpressionName` varchar(100) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastPublished` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `SessionId` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`TestResultExpressionSerNum`),
  UNIQUE KEY `ExpressionName` (`ExpressionName`),
  KEY `TestResultControlSerNum` (`TestResultControlSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  CONSTRAINT `TestResultExpression_ibfk_2` FOREIGN KEY (`LastUpdatedBy`) REFERENCES `OAUser` (`OAUserSerNum`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `TestResultExpression_ibfk_1` FOREIGN KEY (`TestResultControlSerNum`) REFERENCES `TestResultControl` (`TestResultControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1