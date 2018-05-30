CREATE TABLE `CronLog` (
  `CronLogSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `CronSerNum` int(11) NOT NULL,
  `CronStatus` varchar(25) NOT NULL,
  `CronDateTime` datetime NOT NULL,
  PRIMARY KEY (`CronLogSerNum`),
  KEY `CronSerNum` (`CronSerNum`),
  CONSTRAINT `CronLog_ibfk_1` FOREIGN KEY (`CronSerNum`) REFERENCES `Cron` (`CronSerNum`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1