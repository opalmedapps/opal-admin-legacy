CREATE TABLE `SMSSurvey` (
  `SMSSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SentToNumber` bigint(11) NOT NULL,
  `Provider` text NOT NULL,
  `ReceivedInTime` text NOT NULL,
  `SubmissionTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`SMSSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1