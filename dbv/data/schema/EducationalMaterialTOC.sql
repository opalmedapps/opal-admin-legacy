CREATE TABLE `EducationalMaterialTOC` (
  `EducationalMaterialTOCSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `OrderNum` int(11) NOT NULL,
  `ParentSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialTOCSerNum`),
  KEY `EducationalMaterialControlSerNum` (`EducationalMaterialControlSerNum`),
  KEY `OrderNum` (`OrderNum`),
  KEY `ParentSerNum` (`ParentSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1