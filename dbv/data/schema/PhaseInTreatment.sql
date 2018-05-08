CREATE TABLE `PhaseInTreatment` (
  `PhaseInTreatmentSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `Name_EN` varchar(200) NOT NULL,
  `Name_FR` varchar(200) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PhaseInTreatmentSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1