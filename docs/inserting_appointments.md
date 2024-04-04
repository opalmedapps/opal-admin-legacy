# Testing Inserting of Appointments

Prerequisite: A non-human user in opalAdmin.

Log in first to have a valid session.
By passing a `PHPSESSID` on the log in call, the session ID can be pre-defined.

```shell
curl -H "Cookie: PHPSESSID=0at874cofe0v0hos9rd83pal51;" --data "username=$USERNAME&password=$PASSWORD" http://localhost:8082/user/system-login -v
```

Insert an appointment. Ensure that the `scheduledTimestamp` is in the future, otherwise the appointment will not be ignored.

```shell
curl -H "Cookie: PHPSESSID=0at874cofe0v0hos9rd83pal51;" --data "sourceId=2797788&clinicDescription=John%20Kildea&scheduledTimestamp=2024-03-17%2016:00:00&site=RVH&sourceSystem=Aria&mrn=9999996&appointmentTypeCode=OUTGAS-daily&clinicCode=jkildea&appointmentTypeDescription=OUTGAS-daily&status=Completed" http://localhost:8082/appointment/update/appointment
```
