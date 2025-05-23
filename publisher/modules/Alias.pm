# SPDX-FileCopyrightText: Copyright (C) 2014 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

#---------------------------------------------------------------------------------
# A.Joseph 19-Feb-2014 ++ File: Alias.pm
#---------------------------------------------------------------------------------
# Perl module that creates an alias class. This module calls a constructor to
# create an alias object that contains alias information stored as object
# variables.
#
# There exists various subroutines to set alias information, get alias information (to/from objects)
# There exists subroutines that use the Database.pm module to set/get alias information to/from MySQL

package Alias; # Declare package name


use Exporter; # To export subroutines and variables
use Database; # Use our custom database module Database.pm
use Time::Piece; # To parse and convert date time
use Try::Tiny;

#---------------------------------------------------------------------------------
# Connect to the database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

my $verbose = 0;

#====================================================================================
# Constructor for our Alias class
#====================================================================================
sub new
{
	my $class = shift;
	my $alias = {
		_ser		    => undef,
		_name		    => undef,
		_type		    => undef,
		_update		    => undef,
		_lasttransfer	=> undef,
        _sourcedbser    => undef,
		_expressions	=> undef,
	};

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $alias, $class;
	return $alias;
}

#======================================================================================
# Subroutine to set the alias serial
#======================================================================================
sub setAliasSer
{
	my ($alias, $ser) = @_; # alias object with provided serial in arguments
	$alias->{_ser} = $ser; # set the serial
	return $alias->{_ser};
}

#======================================================================================
# Subroutine to set the alias name
#======================================================================================
sub setAliasName
{
	my ($alias, $name) = @_; # alias object with provided name in arguments
	$alias->{_name} = $name; # set the name
	return $alias->{_name};
}

#======================================================================================
# Subroutine to set the alias type
#======================================================================================
sub setAliasType
{
	my ($alias, $type) = @_; # alias object with provided type in arguments
	$alias->{_type} = $type; # set the type
	return $alias->{_type};
}

#======================================================================================
# Subroutine to set the alias update
#======================================================================================
sub setAliasUpdate
{
	my ($alias, $update) = @_; # alias object with provided update in arguments
	$alias->{_update} = $update; # set the update
	return $alias->{_update};
}

#======================================================================================
# Subroutine to set the alias last transfer
#======================================================================================
sub setAliasLastTransfer
{
	my ($alias, $lasttransfer) = @_; # alias object with provided LT in arguments
	$alias->{_lasttransfer} = $lasttransfer; # set the LT
	return $alias->{_lasttransfer};
}

#======================================================================================
# Subroutine to set the source db serial
#======================================================================================
sub setAliasSourceDatabaseSer
{
	my ($alias, $sourcedbser) = @_; # alias object with provided id in arguments
	$alias->{_sourcedbser} = $sourcedbser; # set the serial
	return $alias->{_sourcedbser};
}

#======================================================================================
# Subroutine to set the alias expressions (ie. task, appts, or docs assigned to this alias)
#======================================================================================
sub setAliasExpressions
{
	my ($alias, @expressions) = @_; # alias object with provided expressions in arguments
	@{$alias->{_expressions}} = @expressions; # set the expressions array
	return @{$alias->{_expressions}};
}

#======================================================================================
# Subroutine to get the alias serial
#======================================================================================
sub getAliasSer
{
	my ($alias) = @_; # our alias object
	return $alias->{_ser};
}

#======================================================================================
# Subroutine to get the alias name
#======================================================================================
sub getAliasName
{
	my ($alias) = @_; # our alias object
	return $alias->{_name};
}

#======================================================================================
# Subroutine to get the alias type
#======================================================================================
sub getAliasType
{
	my ($alias) = @_; # our alias object
	return $alias->{_type};
}

#======================================================================================
# Subroutine to get the alias update
#======================================================================================
sub getAliasUpdate
{
	my ($alias) = @_; # our alias object
	return $alias->{_update};
}

#======================================================================================
# Subroutine to get the alias last transfer
#======================================================================================
sub getAliasLastTransfer
{
	my ($alias) = @_; # our alias object
	return $alias->{_lasttransfer};
}

#======================================================================================
# Subroutine to get the alias source database serial
#======================================================================================
sub getAliasSourceDatabaseSer
{
	my ($alias) = @_; # our alias object
	return $alias->{_sourcedbser};
}

#======================================================================================
# Subroutine to get the alias expressions
#======================================================================================
sub getAliasExpressions
{
	my ($alias) = @_; # our alias object
	return @{$alias->{_expressions}};
}

#======================================================================================
# Subroutine to get the aliases marked for update
#======================================================================================
sub getAliasesMarkedForUpdate
{
	my ($aliasType) = @_; # the type of alias from args
	my @aliasList = (); # initialize our list of alias objects
	my ($ser, $type, $sourcedbser, $lasttransfer);
	my @expressions;

	# eval {
		#======================================================================================
		# Retrieve the alias info
		#======================================================================================
		my $aliasInfo_sql = "
			SELECT DISTINCT
				Alias.AliasSerNum,
				Alias.AliasType,
				Alias.LastTransferred,
	            Alias.SourceDatabaseSerNum
			FROM
				Alias,
				SourceDatabase
			WHERE
				Alias.AliasUpdate			= 1
			AND	Alias.AliasType				= \"$aliasType\"
			AND Alias.SourceDatabaseSerNum 	= SourceDatabase.SourceDatabaseSerNum
			AND SourceDatabase.Enabled 		= 1
		";

		print "$aliasInfo_sql\n" if $verbose;

		# prepare query
		my $query = $SQLDatabase->prepare($aliasInfo_sql)
			or die "Could not prepare query: " . $SQLDatabase->errstr;

		# execute query
		$query->execute()
			or die "Could not execute query: " . $query->errstr;

		while (my @data = $query->fetchrow_array()) {

			my $Alias = new Alias(); # alias object

			$ser		    = $data[0];
			$type		    = $data[1];
			$lasttransfer	= $data[2];
	        $sourcedbser    = $data[3];

			# set alias information
			$Alias->setAliasSer($ser);
			$Alias->setAliasType($type);
			$Alias->setAliasLastTransfer($lasttransfer);
	        $Alias->setAliasSourceDatabaseSer($sourcedbser);

			# get expressions for this alias
			@expressions	= $Alias->getAliasExpressionsFromOurDB();

			# finally, set expressions
			$Alias->setAliasExpressions(@expressions);

			push(@aliasList, $Alias);
		}

	return @aliasList;
}
# retrieve aliases marked for update from cronControlAlias
sub getAliasesMarkedForUpdateModularCron
{
	my ($aliasType) = @_; # the type of alias from args - Appointment, Document, task
	my @aliasList = (); # initialize our list of alias objects
	my ($ser, $type, $sourcedbser, $lasttransfer);
	my @expressions;

	# eval {
		#======================================================================================
		# Retrieve the alias info
		#======================================================================================
		my $aliasInfo_sql = "
			SELECT DISTINCT
				cronControlAlias.cronControlAliasSerNum as AliasSerNum,
				Alias.AliasType,
				cronControlAlias.lastTransferred,
	            Alias.SourceDatabaseSerNum
			FROM
				Alias,
				cronControlAlias,
				SourceDatabase
			WHERE
				cronControlAlias.aliasUpdate = 2
			AND cronControlAlias.cronControlAliasSerNum = Alias.AliasSerNum
			AND	cronControlAlias.cronType 	= '$aliasType'
			AND Alias.SourceDatabaseSerNum 	= SourceDatabase.SourceDatabaseSerNum
			AND SourceDatabase.Enabled 		= 1;
		";

		print "$aliasInfo_sql\n" if $verbose;

		# prepare query
		my $query = $SQLDatabase->prepare($aliasInfo_sql)
			or die "Could not prepare query: " . $SQLDatabase->errstr;

		# execute query
		$query->execute()
			or die "Could not execute query: " . $query->errstr;

		while (my @data = $query->fetchrow_array()) {

			my $Alias = new Alias(); # alias object

			$ser		    = $data[0];
			$type		    = $data[1];
			$lasttransfer	= $data[2];
	        $sourcedbser    = $data[3];

			# set alias information
			$Alias->setAliasSer($ser);
			$Alias->setAliasType($type);
			$Alias->setAliasLastTransfer($lasttransfer);
	        $Alias->setAliasSourceDatabaseSer($sourcedbser);

			# get expressions for this alias
			@expressions	= $Alias->getAliasExpressionsFromOurDB();

			# finally, set expressions
			$Alias->setAliasExpressions(@expressions);

			push(@aliasList, $Alias);
		}

	return @aliasList;
}

#======================================================================================
# Subroutine to get the expressions for a particular alias from MySQL
#======================================================================================
sub getAliasExpressionsFromOurDB
{
	my ($Alias) = @_; # our alias object

	my @expressions = (); # initialize a list of expressions

	# get alias serial
	my $ser = $Alias->getAliasSer();

	#======================================================================================
	# Retrieve the alias expressions
	#======================================================================================
	my $expressionInfo_sql = "
		SELECT DISTINCT
			AliasExpression.AliasExpressionSerNum,
			REPLACE(AliasExpression.ExpressionName, '''', ''),
			REPLACE(AliasExpression.Description, '''', ''),
			AliasExpression.LastTransferred
		FROM
			Alias,
			AliasExpression
		WHERE
			Alias.AliasSerNum		    = $ser
		AND AliasExpression.AliasSerNum	= Alias.AliasSerNum
	";

	print "$expressionInfo_sql\n" if $verbose;

	# prepare query
	my $query = $SQLDatabase->prepare($expressionInfo_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {
		my $aliasExpression = {
			_ser			=> $data[0],
			_name			=> $data[1],
			_description 	=> $data[2],
			_lasttransfer 	=> $data[3]
		};
		push(@expressions, $aliasExpression); # push in our list
	}
	return @expressions;
}

#======================================================================================
# Subroutine to set/update the "last transferred" field to current time
#======================================================================================
sub setAliasLastTransferIntoOurDB
{
	my ($current_datetime) = @_; # our current datetime in arguments

	my $update_sql = "

		UPDATE
			Alias,
			AliasExpression
		SET
			Alias.LastTransferred			= '$current_datetime',
            Alias.LastUpdated     			= Alias.LastUpdated,
            AliasExpression.LastTransferred	= '$current_datetime',
            AliasExpression.LastUpdated 	= AliasExpression.LastUpdated
		WHERE
			AliasUpdate			= 1
		AND Alias.AliasSerNum 	= AliasExpression.AliasSerNum
	";

	print "$update_sql\n" if $verbose;

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
}


#======================================================================================
# Subroutine to set/update the "last transferred" field to current time for modular controllers only (dataControl still uses the setAliasLastTransferIntoOurDB sub)
#======================================================================================
sub setAliasLastTransferredModularControllers
{
	my ($current_datetime, $module) = @_; # our current datetime in arguments

	my $update_sql = "
	UPDATE
		cronControlAlias,
		Alias,
		AliasExpression
	SET Alias.LastTransferred = '$current_datetime',
		AliasExpression.LastTransferred	= '$current_datetime',
		cronControlAlias.lastTransferred = '$current_datetime',
		cronControlAlias.aliasUpdate = 1
	WHERE
		cronControlAlias.aliasUpdate = 2
		AND cronControlAlias.cronType = '$module'
		AND cronControlAlias.cronControlAliasSerNum = Alias.AliasSerNum
		AND Alias.AliasSerNum = AliasExpression.AliasSerNum;
	";

	print "$update_sql\n" if $verbose;

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
}

#======================================================================================
# Subroutine to get expression name from our db given an expression serial
#======================================================================================
sub getExpressionNameFromOurDB
{
    my ($expressionSer) = @_; # args

    my $expressionName; #  initialize

    my $select_sql = "
        SELECT DISTINCT
            ae.ExpressionName
        FROM
            AliasExpression ae
        WHERE
            ae.AliasExpressionSerNum = '$expressionSer'
    ";

	print "$select_sql\n" if $verbose;

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        $expressionName = $data[0];

    }

    return $expressionName;

}

#======================================================================================
# Subroutine to get alias serial from our db given an expression serial
#======================================================================================
sub getAliasFromOurDB
{
    my ($expressionSer) = @_; # args

    my $aliasSer; #  initialize

    my $select_sql = "
        SELECT DISTINCT
            ae.AliasSerNum
        FROM
            AliasExpression ae
        WHERE
            ae.AliasExpressionSerNum = '$expressionSer'
    ";

	print "$select_sql\n" if $verbose;

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        $aliasSer = $data[0];

    }

    return $aliasSer;

}
# To exit/return always true (for the module itself)
1;
