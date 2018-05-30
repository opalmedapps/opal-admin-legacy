CREATE TABLE `Cron` (
  `CronSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `NextCronDate` date NOT NULL,
  `RepeatUnits` varchar(50) NOT NULL,
  `NextCronTime` time NOT NULL,
  `RepeatInterval` int(11) NOT NULL,
  `LastCron` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`CronSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1