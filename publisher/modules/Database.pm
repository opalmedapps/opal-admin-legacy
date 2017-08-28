#!/usr/bin/perl

#---------------------------------------------------------------------------------
# A.Joseph 18-Dec-2013 ++ File: Database.pm
#---------------------------------------------------------------------------------
# Perl module that creates a database class. It connects first to the ARIA 
# database. A database object is created for the MySQL database connection.
# This module calls a constructor to create this object and then calls a 
# subroutine to connect to the MySQL database with the parameters given.
#
# Although all these object variables are set within this module, I provide 
# setter subroutines in case the user wishes to changed the object variables.

package Database; # Declare package name

use Configs; # Custom Config.pm to get constants (i.e. configurations)
use Exporter; # To export subroutines and variables
use DBI;
use DBD::Sybase;

# Create a database object
our $databaseObject = new Database(
        $Configs::OPAL_DB_DSN,
        $Configs::OPAL_DB_USERNAME,
        $Configs::OPAL_DB_PASSWORD
    );

# Connect to our MySQL database
our $targetDatabase = $databaseObject->connectToTargetDatabase();

#====================================================================================
# Constructor for our Databases class 
#====================================================================================
sub new 
{
	my $class = shift;
	my $database = {
		_dsn		=> shift,
		_user		=> shift,
		_password	=> shift,
	};

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $database, $class; 
	return $database;
}

#======================================================================================
# Subroutine to connect to the source database
#======================================================================================
sub connectToSourceDatabase
{
	my ($sourceDBser) = @_; # source database serial

	my $db_connect = undef;

	if (sourceDatabaseIsEnabled($sourceDBser)) {
	    my $sourceDBCredentials = Configs::fetchSourceCredentials($sourceDBser);

	    $db_connect = DBI->connect(
	            $sourceDBCredentials->{_dsn},
	            $sourceDBCredentials->{_user},
	            $sourceDBCredentials->{_password},
	        )
		    or die "Could not connect to the source database: " . DBI->errstr;
	}

	return $db_connect;
}

#======================================================================================
# Subroutine to check whether a source database is enabled for use
#======================================================================================
sub sourceDatabaseIsEnabled
{
	my ($sourceDBSer) = @_; # source database serial

	my $enabled = undef;

	my $enabled_sql = "
		SELECT 
			sdb.Enabled 
		FROM
			SourceDatabase sdb
		WHERE
			sdb.SourceDatabaseSerNum = $sourceDBSer
	";
	# prepare query
	my $query = $targetDatabase->prepare($enabled_sql)
		or die "Could not prepare query: " . $targetDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {
		$enabled = $data[0];
	}

	return $enabled;
}

#======================================================================================
# Subroutine to connect to the MySQL database
#======================================================================================
sub connectToTargetDatabase
{
	my ($database) = @_; # database object	
	my $db_connect = DBI->connect_cached(
            $database->{_dsn},
            $database->{_user},
            $database->{_password}
        )
		or die "Could not connect to the MySQL db: " . DBI->errstr;
	return $db_connect;
}

#======================================================================================
# Subroutine to set the database dsn
#======================================================================================
sub setDatabaseDSN
{
	my ($database, $dsn) = @_; # database object with provided dsn in arguments
	$database->{_dsn} = $dsn if defined($dsn); # set the dsn
	return $database->{_dsn};
}

#======================================================================================
# Subroutine to set the database username
#======================================================================================
sub setDatabaseUser
{
	my ($database, $user) = @_; # database object with provided user name in arguments
	$database->{_user} = $user if defined($user); # set the user name
	return $database->{_user};
}

#======================================================================================
# Subroutine to set the database password
#======================================================================================
sub setDatabasePassword
{
	my ($database, $password) = @_; # database object with provided password in arguments
	$database->{_password} = $password if defined($password); # set the password
	return $database->{_password};
}

# To exit/return always true (for the module itself)
1;
