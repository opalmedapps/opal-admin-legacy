CREATE TABLE `EducationalMaterialRating` (
  `EducationalMaterialRatingSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `EducationalMaterialControlSerNum` int(11) NOT NULL,
  `PatientSerNum` int(11) NOT NULL,
  `RatingValue` tinyint(6) NOT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EducationalMaterialRatingSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1