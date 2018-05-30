CREATE TABLE `TestResultAdditionalLinks` (
  `TestResultAdditionalLinksSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `TestResultControlSerNum` int(11) NOT NULL,
  `Name_EN` varchar(1028) NOT NULL,
  `Name_FR` varchar(1028) NOT NULL,
  `URL_EN` varchar(2056) NOT NULL,
  `URL_FR` varchar(2056) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`TestResultAdditionalLinksSerNum`),
  KEY `TestResultControlSerNum` (`TestResultControlSerNum`),
  CONSTRAINT `TestResultAdditionalLinks_ibfk_1` FOREIGN KEY (`TestResultControlSerNum`) REFERENCES `TestResultControl` (`TestResultControlSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1