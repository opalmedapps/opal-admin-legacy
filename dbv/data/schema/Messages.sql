CREATE TABLE `Messages` (
  `MessageSerNum` int(11) NOT NULL AUTO_INCREMENT,
  `SenderRole` enum('Doctor','Patient','Admin') NOT NULL,
  `ReceiverRole` enum('Doctor','Patient','Admin') NOT NULL,
  `SenderSerNum` int(10) unsigned NOT NULL COMMENT 'Sender''s SerNum',
  `ReceiverSerNum` int(11) unsigned NOT NULL COMMENT 'Recipient''s SerNum',
  `MessageContent` varchar(255) NOT NULL,
  `ReadStatus` smallint(6) NOT NULL COMMENT 'Whether it  has been answered by the medical team',
  `Attachment` varchar(255) NOT NULL,
  `MessageDate` datetime NOT NULL,
  `SessionId` text NOT NULL,
  `LastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`MessageSerNum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1