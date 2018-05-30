CREATE TABLE `CheckinLog` (
  `CheckinLogSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `AppointmentSerNum` int(11) NOT NULL,
  `DeviceId` varchar(100) NOT NULL,
  `Latitude` double NOT NULL COMMENT 'In meters, from 45.474127399999996, -73.6011402',
  `Longitude` double NOT NULL,
  `Accuracy` double NOT NULL COMMENT 'Accuracy in meters',
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`CheckinLogSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1