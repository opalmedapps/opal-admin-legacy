CREATE TABLE `EducationalMaterialPackageContent` (
  `EducationalMaterialPackageContentSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EducationalMaterialControlSerNum` int(11) NOT NULL COMMENT 'Material contained in a package.',
  `OrderNum` int(11) NOT NULL COMMENT 'Position of the material in the package, starting at 1.',
  `ParentSerNum` int(11) NOT NULL COMMENT 'EducationalMaterialControlSerNum of the parent package.',
  `DateAdded` datetime NOT NULL,
  `AddedBy` int(11) DEFAULT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedBy` int(11) DEFAULT NULL,
  PRIMARY KEY (`EducationalMaterialPackageContentSerNum`),
  KEY `EducationalMaterialControlSerNum` (`EducationalMaterialControlSerNum`),
  KEY `ParentSerNum` (`ParentSerNum`),
  KEY `LastUpdated` (`LastUpdated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Directory of each material that is contained in an educational material package. No foreign keys to facilitate order changes.';
