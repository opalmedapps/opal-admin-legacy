CREATE DEFINER=robert@`%` TRIGGER `delete_message_trigger` AFTER DELETE ON `Messages`
 FOR EACH ROW BEGIN
INSERT INTO `MessagesMH`(`MessageSerNum`, `MessageRevSerNum`, `SessionId`, `SenderRole`, `ReceiverRole`, `SenderSerNum`, `ReceiverSerNum`, `MessageContent`, `ReadStatus`, `Attachment`, `MessageDate`, `LastUpdated`, `ModificationAction`) VALUES (OLD.MessageSerNum, NULL, OLD.SessionId, OLD.SenderRole, OLD.ReceiverRole, OLD.SenderSerNum, OLD.ReceiverSerNum, OLD.MessageContent, OLD.ReadStatus, OLD.Attachment, OLD.MessageDate, NOW(), 'DELETE');
END