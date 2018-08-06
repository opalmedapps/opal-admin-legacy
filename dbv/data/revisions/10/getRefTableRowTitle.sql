DELIMITER $$

CREATE FUNCTION `getRefTableRowTitle`(
	`in_RefTableRowSerNum` INT,
	`in_NotificationRequestType` VARCHAR(50),
	`in_Language` VARCHAR(2)
)
RETURNS varchar(2056) CHARSET latin1
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT 'Return the title for the notification'
BEGIN

	Declare wsReturn varchar(2000);
	Declare wsLanguage varchar(2);
	Declare wsRefTableRowSerNum int;
	Declare wsNotificationRequestType varchar(50);

	Declare wsLanguage_EN, wsLanguage_FR varchar(2000);

	set wsReturn = '';
	set wsLanguage = in_Language;
	set wsRefTableRowSerNum = in_RefTableRowSerNum;
	set wsNotificationRequestType = in_NotificationRequestType;

/*
* Notification that uses ALIAS
*/
	if (ucase(wsNotificationRequestType) = 'ALIAS') then
		Select A.AliasName_EN, A.AliasName_FR
			into wsLanguage_EN, wsLanguage_FR
		from Alias A, AliasExpression AE
		where A.AliasSerNum = AE.AliasSerNum
			and AE.AliasExpressionSerNum = wsRefTableRowSerNum;
	end if;

/*
* Notification that uses DOCUMENTS
*/
	if (ucase(wsNotificationRequestType) = 'DOCUMENT') then
		Select A.AliasName_EN, A.AliasName_FR
			into wsLanguage_EN, wsLanguage_FR
		from Document D, Alias A, AliasExpression AE
		where A.AliasSerNum = AE.AliasSerNum
			and AE.AliasExpressionSerNum = D.AliasExpressionSerNum
			and D.DocumentSerNum = wsRefTableRowSerNum;
	end if;

/*
* Notification that uses APPOINTMENTS
*/
	if (ucase(wsNotificationRequestType) = 'APPOINTMENT') then
		Select A.AliasName_EN, A.AliasName_FR
			into wsLanguage_EN, wsLanguage_FR
		from Appointment Apt, Alias A, AliasExpression AE
		where A.AliasSerNum = AE.AliasSerNum
			and AE.AliasExpressionSerNum = Apt.AliasExpressionSerNum
			and Apt.AppointmentSerNum = wsRefTableRowSerNum;
	end if;

/*
* Notification that uses POST
*/
	if (ucase(wsNotificationRequestType) = 'POST') then
		select PC.PostName_EN, PC.PostName_FR
			into wsLanguage_EN, wsLanguage_FR
		from PostControl PC
		where PC.PostControlSerNum = wsRefTableRowSerNum;
	end if;

/*
* Notification that uses EDUCATIONAL MATERIAL
*/
	if (ucase(wsNotificationRequestType) = 'EDUCATIONAL') then
		select EC.Name_EN, EC.Name_FR
			into wsLanguage_EN, wsLanguage_FR
		from 	EducationalMaterial E, EducationalMaterialControl EC
		where E.EducationalMaterialControlSerNum = EC.EducationalMaterialControlSerNum
			and E.EducationalMaterialSerNum = wsRefTableRowSerNum;

	end if;

/*
* Notification that uses QUESTIONNAIRE
*/
	if (ucase(wsNotificationRequestType) = 'QUESTIONNAIRE') then
		select QC.QuestionnaireName_EN, QC.QuestionnaireName_FR
			into wsLanguage_EN, wsLanguage_FR
		from QuestionnaireControl QC
		where QC.QuestionnaireControlSerNum = wsRefTableRowSerNum;
	end if;

	if (wsLanguage = 'EN') then
		set wsReturn  = wsLanguage_EN;
	else
		set wsReturn  = wsLanguage_FR;
	end if;

	set wsReturn = (IfNull(wsReturn, ''));

	return wsReturn;
END$$

DELIMITER ;
