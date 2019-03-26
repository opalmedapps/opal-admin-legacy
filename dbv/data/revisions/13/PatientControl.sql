ALTER TABLE `PatientControl`
	ADD COLUMN `TransferFlag` SMALLINT(6) NOT NULL DEFAULT '0' AFTER `LastUpdated`;

ALTER TABLE `PatientControl`
	ADD INDEX `TransferFlag_IDX` (`TransferFlag`);