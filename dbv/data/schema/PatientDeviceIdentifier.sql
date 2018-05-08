CREATE TABLE `PatientDeviceIdentifier` (
  `PatientDeviceIdentifierSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `PatientSerNum` int(11) NOT NULL,
  `DeviceId` varchar(255) NOT NULL,
  `RegistrationId` varchar(256) NOT NULL,
  `DeviceType` tinyint(4) NOT NULL,
  `SessionId` text NOT NULL,
  `SecurityAnswerSerNum` int(11) DEFAULT NULL,
  `Trusted` tinyint(4) NOT NULL DEFAULT '0',
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Attempt` int(11) DEFAULT '0',
  `TimeoutTimestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`PatientDeviceIdentifierSerNum`),
  UNIQUE KEY `patient_device` (`PatientSerNum`,`DeviceId`),
  CONSTRAINT `PatientDeviceIdentifier_ibfk_1` FOREIGN KEY (`PatientSerNum`) REFERENCES `Patient` (`PatientSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1