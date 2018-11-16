DELIMITER $$

CREATE FUNCTION `getTranslation`(
	`in_TableName` VARCHAR(150),
	`in_ColumnName` VARCHAR(150),
	`in_Text` VARCHAR(250)
)
RETURNS VARCHAR(250)
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

  /*
  in_TableName is the table name
  in_ColumnName is the column name
  in_Text is the text string to compare
  */

	-- Declare variables
	Declare wsTableName, wsColumnName, wsText, wsReturnText, wsReturn VarChar(255);
	Declare wsActive, wsCount int;

	-- Store the parameters
	set wsTableName = in_TableName;
	set wsColumnName = in_ColumnName;
	set wsText = in_Text;
	set wsActive = 0;
	set wsCount = 0;

  -- Get the translation
	select count(*) Total, ifnull(TranslationReplace, '') TranslationReplace
	into wsCount, wsReturnText
	from Translation
	where TranslationTableName = wsTableName
		and TranslationColumnName = wsColumnName
		and TranslationCurrent = wsText
	Limit 1;

  -- if no record found then return original text
  -- otherwise return the translation text
	if (wsCount = 0) then
		set wsReturn = wsText;
	else
		set wsReturn = wsReturnText;
	end if;

	Return wsReturn;

END$$

DELIMITER ;
