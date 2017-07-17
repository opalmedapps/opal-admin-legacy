# Tracking report for table `Role`
# 2017-07-17 12:44:03


INSERT INTO `Role` (`RoleSerNum`, `RoleName`, `DateAdded`, `LastUpdated`) VALUES (NULL, 'clinician', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP), (NULL, 'manager', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
INSERT INTO `Role` (`RoleSerNum`, `RoleName`, `DateAdded`, `LastUpdated`) VALUES (NULL, 'education creator', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
UPDATE `Role` SET `RoleName` = 'education-creator' WHERE `Role`.`RoleSerNum` = 7;