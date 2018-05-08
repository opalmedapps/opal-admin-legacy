CREATE TABLE `DiagnosisPermissions` (
  `DiagnosisPermissionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `DiagnosisSerNum` int(11) DEFAULT NULL,
  `PatientCaregiverSer` int(11) DEFAULT NULL,
  PRIMARY KEY (`DiagnosisPermissionSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1