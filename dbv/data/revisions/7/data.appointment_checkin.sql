INSERT INTO AppointmentCheckin (`AliasSerNum`, `CheckinPossible`, `DateAdded`)
SELECT al.AliasSerNum, 1, NOW()
FROM   Alias al
WHERE  al.AliasType = 'Appointment';