DELIMITER $$

CREATE FUNCTION `getDiagnosisDescription`(
	`in_DiagnosisCode` VARCHAR(100),
	`in_Language` VARCHAR(2)
)
RETURNS varchar(2056) CHARSET latin1
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT 'Return the description of the Diagnosis Code'
BEGIN
	Declare wsReturn varchar(2056);
	Declare wsLanguage varchar(2);
	Declare wsDiagnosisCode varchar(100);

	set wsLanguage = in_Language;
	set wsDiagnosisCode = in_DiagnosisCode;

	if (wsLanguage = 'EN') then

		set wsReturn  = (select DT.Name_EN from DiagnosisCode DC, DiagnosisTranslation DT
			where DC.DiagnosisTranslationSerNum = DT.DiagnosisTranslationSerNum
				and DC.DiagnosisCode = in_DiagnosisCode
			limit 1);

	else

		set wsReturn  = (select DT.Name_FR from DiagnosisCode DC, DiagnosisTranslation DT
			where DC.DiagnosisTranslationSerNum = DT.DiagnosisTranslationSerNum
				and DC.DiagnosisCode = in_DiagnosisCode
			limit 1);

	end if;

	set wsReturn = (IfNull(wsReturn, 'N/A'));

	RETURN wsReturn;
END$$

DELIMITER ;
