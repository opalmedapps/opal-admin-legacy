# Tracking report for table `PatientLocation`
# 2017-07-26 16:42:02

DROP TABLE IF EXISTS `PatientLocation`;

CREATE TABLE `PatientLocation` (
  `PatientLocationSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceUID` int(11) NOT NULL,
  `AppointmentSerNum` int(11) NOT NULL,
  `RevCount` int(11) NOT NULL,
  `CheckedInFlag` tinyint(4) NOT NULL,
  `ArrivalDateTime` datetime NOT NULL,
  `VenueSerNum` int(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientLocationSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `PatientLocation`  ADD `SourceDatabaseSerNum` INT NOT NULL AFTER `PatientLocationSerNum`;
ALTER TABLE `PatientLocation` ADD INDEX(`SourceDatabaseSerNum`);
ALTER TABLE `PatientLocation` ADD INDEX(`SourceUID`);
ALTER TABLE `PatientLocation` ADD INDEX(`AppointmentSerNum`);
ALTER TABLE `PatientLocation` ADD INDEX(`RevCount`);
ALTER TABLE `PatientLocation` ADD INDEX(`CheckedInFlag`);
ALTER TABLE `PatientLocation` ADD INDEX(`VenueSerNum`);
ALTER TABLE `PatientLocation` ADD FOREIGN KEY (`AppointmentSerNum`) REFERENCES `Appointment`(`AppointmentSerNum`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `PatientLocation` ADD FOREIGN KEY (`VenueSerNum`) REFERENCES `Venue`(`VenueSerNum`) ON DELETE RESTRICT ON UPDATE CASCADE;

# Tracking report for table `PatientLocationMH`
# 2017-07-26 16:42:13

DROP TABLE IF EXISTS `PatientLocationMH`;

CREATE TABLE `PatientLocationMH` (
  `PatientLocationMHSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `SourceUID` int(11) NOT NULL,
  `AppointmentSerNum` int(11) NOT NULL,
  `RevCount` int(11) NOT NULL,
  `CheckedInFlag` tinyint(4) NOT NULL,
  `ArrivalDateTime` datetime NOT NULL,
  `VenueSerNum` int(11) NOT NULL,
  `HstryDateTime` datetime NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PatientLocationMHSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `PatientLocationMH` ADD INDEX(`SourceDatabaseSerNum`);
ALTER TABLE `PatientLocationMH` ADD INDEX(`SourceUID`);
ALTER TABLE `PatientLocationMH` ADD INDEX(`AppointmentSerNum`);
ALTER TABLE `PatientLocationMH` ADD INDEX(`RevCount`);
ALTER TABLE `PatientLocationMH` ADD INDEX(`CheckedInFlag`);
ALTER TABLE `PatientLocationMH` ADD INDEX(`VenueSerNum`);
ALTER TABLE `PatientLocationMH` ADD FOREIGN KEY (`AppointmentSerNum`) REFERENCES `Appointment`(`AppointmentSerNum`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `PatientLocationMH` ADD FOREIGN KEY (`VenueSerNum`) REFERENCES `Venue`(`VenueSerNum`) ON DELETE RESTRICT ON UPDATE CASCADE;

# Tracking report for table `Venue`
# 2017-07-26 16:42:21

DROP TABLE IF EXISTS `Venue`;

CREATE TABLE `Venue` (
  `VenueSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SourceDatabaseSerNum` int(11) NOT NULL,
  `SourceUID` int(11) NOT NULL,
  `VenueId` varchar(100) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`VenueSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `Venue` ADD INDEX(`SourceDatabaseSerNum`);
ALTER TABLE `Venue` ADD INDEX(`SourceUID`);
