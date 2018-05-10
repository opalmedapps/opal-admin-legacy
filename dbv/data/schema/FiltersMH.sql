CREATE TABLE `FiltersMH` (
  `FilterSerNum` int(11) NOT NULL,
  `ControlTable` varchar(100) NOT NULL,
  `ControlTableSerNum` int(11) NOT NULL,
  `FilterType` varchar(100) NOT NULL,
  `FilterId` varchar(150) NOT NULL,
  `ModificationAction` varchar(25) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  `SessionId` varchar(255) DEFAULT NULL,
  KEY `FilterSerNum` (`FilterSerNum`),
  KEY `LastUpdatedBy` (`LastUpdatedBy`),
  KEY `ControlTableSerNum` (`ControlTableSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1