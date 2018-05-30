CREATE TABLE `PatientDoctor` (
  `PatientDoctorSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `DoctorSerNum` int(11) NOT NULL,
  `OncologistFlag` int(11) NOT NULL,
  `PrimaryFlag` int(11) NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientDoctorSerNum`),
  KEY `PatientSerNum` (`PatientSerNum`),
  KEY `DoctorSerNum` (`DoctorSerNum`),
  CONSTRAINT `PatientDoctor_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`) ON UPDATE CASCADE,
  CONSTRAINT `PatientDoctor_ibfk_2` FOREIGN KEY (`DoctorSerNum`) REFERENCES `Doctor` (`DoctorSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1