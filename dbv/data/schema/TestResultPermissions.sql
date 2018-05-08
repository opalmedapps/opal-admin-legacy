CREATE TABLE `TestResultPermissions` (
  `TestResultPermissionSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `TestResultSerNum` int(11) DEFAULT NULL,
  `PatientCaregiverSer` int(11) DEFAULT NULL,
  PRIMARY KEY (`TestResultPermissionSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1