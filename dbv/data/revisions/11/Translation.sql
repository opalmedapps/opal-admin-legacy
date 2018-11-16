CREATE TABLE `Translation` (
	`TranslationSerNum` BIGINT(20) NOT NULL AUTO_INCREMENT,
	`TranslationTableName` VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'Name of the Table',
	`TranslationColumnName` VARCHAR(150) NOT NULL DEFAULT '' COMMENT 'Name of the column',
	`TranslationCurrent` VARCHAR(512) NOT NULL DEFAULT '' COMMENT 'Current text',
	`TranslationReplace` VARCHAR(512) NOT NULL DEFAULT '' COMMENT 'Replace the current text',
	`Active` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '1 = Active / 0 = Not Active',
	`RefTableRecNo` BIGINT(20) NULL DEFAULT NULL COMMENT 'Record Number of the reference table',
	PRIMARY KEY (`TranslationSerNum`),
	INDEX `IX_Active` (`Active`),
	INDEX `IX_TranslationTableName` (`TranslationTableName`)
)
ENGINE=InnoDB;
