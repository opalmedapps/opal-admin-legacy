CREATE DEFINER=robert@`%` TRIGGER `insert_message_trigger` AFTER INSERT ON `Messages`
 FOR EACH ROW BEGIN
INSERT INTO `MessagesMH`(`MessageSerNum`, `MessageRevSerNum`, `SessionId`, `SenderRole`, `ReceiverRole`, `SenderSerNum`, `ReceiverSerNum`, `MessageContent`, `ReadStatus`, `Attachment`, `MessageDate`, `LastUpdated`, `ModificationAction`) VALUES (NEW.MessageSerNum, NULL, New.SessionId, NEW.SenderRole, NEW.ReceiverRole, NEW.SenderSerNum, NEW.ReceiverSerNum, NEW.MessageContent, NEW.ReadStatus, NEW.Attachment, NEW.MessageDate, NOW(), 'INSERT');
END