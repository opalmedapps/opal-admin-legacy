CREATE TABLE `ResourceAppointment` (
  `ResourceAppointmentSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `ResourceSerNum` int(11) NOT NULL,
  `AppointmentSerNum` int(11) NOT NULL,
  `ExclusiveFlag` varchar(11) NOT NULL,
  `PrimaryFlag` varchar(11) NOT NULL,
  `DateAdded` datetime NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ResourceAppointmentSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1