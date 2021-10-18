#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 17-Nov-2015 ++ File: Cron.pm
#---------------------------------------------------------------------------------
# Perl module that handles the cron controls (logs)
#

package Cron; # Declare package name


use Time::Piece;
use Time::Seconds;
use POSIX;
use Database;

#---------------------------------------------------------------------------------
# Connect to the database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Subroutine to set the cron log
#====================================================================================
sub setCronLog
{
    my ($status, $datetime) = @_; # cron information

    my $insert_sql = "
        INSERT INTO
            CronLog (
                CronSerNum,
                CronStatus,
                CronDateTime
            )
        VALUES (
            '1',
            '$status',
            '$datetime'
        )
    ";
	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

        # get cron log serial
    my $logSer = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

    return $logSer;

}



# To exit/return always true (for the module itself)
1;
