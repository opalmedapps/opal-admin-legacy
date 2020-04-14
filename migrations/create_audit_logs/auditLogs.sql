--
-- Table structure for table `auditLogs`
--

CREATE TABLE `auditLogs` (
  `ID` bigint(20) NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `username` varchar(1000) DEFAULT NULL,
  `activityType` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `messageType` varchar(1000) CHARACTER SET utf8 DEFAULT NULL,
  `dateTime` datetime NOT NULL DEFAULT current_timestamp(),
  `date` date DEFAULT NULL,
  `time` time DEFAULT NULL,
  `patientModifiedId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `auditLogs`
--
ALTER TABLE `auditLogs`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `OAUserSernum` (`userId`,`username`),
  ADD KEY `PatientModifiedSerNum` (`patientModifiedId`),
  ADD KEY `fk_auditLogs_username_oaUser_username` (`username`);

--
-- Constraints for table `auditLogs`
--
ALTER TABLE `auditLogs`
  ADD CONSTRAINT `fk_auditLogs_oaUserSerNum_oaUser_userId` FOREIGN KEY (`userId`) REFERENCES `OAUser` (`OAUserSerNum`),
  ADD CONSTRAINT `fk_auditLogs_username_oaUser_username` FOREIGN KEY (`username`) REFERENCES `OAUser` (`Username`);
COMMIT;


