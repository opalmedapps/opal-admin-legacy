ALTER TABLE `Notification`
	ADD COLUMN `RefTableRowTitle_EN` VARCHAR(500) NOT NULL AFTER `LastUpdated`,
	ADD COLUMN `RefTableRowTitle_FR` VARCHAR(500) NOT NULL AFTER `RefTableRowTitle_EN`;
