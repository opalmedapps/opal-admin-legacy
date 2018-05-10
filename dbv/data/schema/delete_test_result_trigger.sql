CREATE DEFINER=`ackeem`@`%` TRIGGER `delete_test_result_trigger` AFTER DELETE ON `TestResult`
 FOR EACH ROW BEGIN
INSERT INTO `TestResultMH`(`TestResultSerNum`, `CronLogSerNum`, `TestResultGroupSerNum`, `TestResultExpressionSerNum`, `PatientSerNum`, `SourceDatabaseSerNum`, `TestResultAriaSer`, `ComponentName`, `FacComponentName`, `AbnormalFlag`, `TestDate`, `MaxNorm`, `MinNorm`, `ApprovedFlag`, `TestValue`, `TestValueString`, `UnitDescription`, `ValidEntry`, `DateAdded`, `ReadStatus`, `ModificationAction`) VALUES (OLD.TestResultSerNum, OLD.CronLogSerNum, OLD.TestResultGroupSerNum, OLD.TestResultExpressionSerNum, OLD.PatientSerNum, OLD.SourceDatabaseSerNum, OLD.TestResultAriaSer, OLD.ComponentName, OLD.FacComponentName, OLD.AbnormalFlag, OLD.TestDate, OLD.MaxNorm, OLD.MinNorm, OLD.ApprovedFlag, OLD.TestValue, OLD.TestValueString, OLD.UnitDescription, OLD.ValidEntry, OLD.DateAdded, OLD.ReadStatus, 'DELETE');
END