ALTER TABLE  `Alias` ADD  `HospitalMapSerNum` INT NULL AFTER  `EducationalMaterialControlSerNum`;
ALTER TABLE  `Alias` ADD INDEX (  `HospitalMapSerNum` );
ALTER TABLE  `Alias` ADD FOREIGN KEY (  `HospitalMapSerNum` ) REFERENCES  `HospitalMap` (
`HospitalMapSerNum`
) ON DELETE SET NULL ON UPDATE CASCADE ;