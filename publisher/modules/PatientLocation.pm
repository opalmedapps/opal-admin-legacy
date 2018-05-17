#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 25-Jul-2017 ++ File: PatientLocation.pm
#---------------------------------------------------------------------------------
# Perl module that creates a patient location (PL) class. This module calls a constructor to 
# create a PL object that contains PL information stored as object 
# variables.
#
# There exists various subroutines to set PL information, get PL information
# and compare PL information between two PL objects. 
# There exists various subroutines that use the Database.pm module to update the
# MySQL database and check if a PL exists already in this database.

package PatientLocation; # Declare package name

use Database; # Our custom Database module
use Time::Piece; # To parse and convert date time
use Appointment; # Our custom Appointment module
use Venue; # Our custom Venue module
use Alias; # Our custom Alias module
use Storable qw(dclone);

#---------------------------------------------------------------------------------
# Connect to our database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Patient Location class 
#====================================================================================
sub new
{
	my $class = shift;
	my $patientlocation = {
		_ser 				=> undef,
		_appointmentser 	=> undef,
		_sourcedbser 		=> undef,
		_sourceuid 			=> undef,
		_revcount 			=> undef,
		_checkedinflag 		=> undef,
		_arrivaldatetime 	=> undef,
		_venueser 			=> undef,
		_hstrydatetime 		=> undef,
	};

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $patientlocation, $class; 
	return $patientlocation;
}

#====================================================================================
# Subroutine to set the PL serial 
#====================================================================================
sub setPatientLocationSer
{
	my ($patientlocation, $ser) = @_; # PL object with provided serial in arguments
	$patientlocation->{_ser} = $ser; # set the ser
	return $patientlocation->{_ser};
}

#====================================================================================
# Subroutine to set the PL appointment serial 
#====================================================================================
sub setPatientLocationAppointmentSer
{
	my ($patientlocation, $appointmentser) = @_; # PL object with provided serial in arguments
	$patientlocation->{_appointmentser} = $appointmentser; # set the ser
	return $patientlocation->{_appointmentser};
}

#====================================================================================
# Subroutine to set the PL source database serial
#====================================================================================
sub setPatientLocationSourceDatabaseSer
{
	my ($patientlocation, $sourcedbser) = @_; # PL object with provided serial in arguments
	$patientlocation->{_sourcedbser} = $sourcedbser; # set the serial
	return $patientlocation->{_sourcedbser};
}

#====================================================================================
# Subroutine to set the PL source UID
#====================================================================================
sub setPatientLocationSourceUID
{
	my ($patientlocation, $sourceuid) = @_; # PL object with provided uid in arguments
	$patientlocation->{_sourceuid} = $sourceuid; # set the uid
	return $patientlocation->{_sourceuid};
}

#====================================================================================
# Subroutine to set the PL revision count 
#====================================================================================
sub setPatientLocationRevisionCount
{
	my ($patientlocation, $revcount) = @_; # PL object with provided count in arguments
	$patientlocation->{_revcount} = $revcount; # set the count
	return $patientlocation->{_revcount};
}

#====================================================================================
# Subroutine to set the PL checked in flag
#====================================================================================
sub setPatientLocationCheckedInFlag
{
	my ($patientlocation, $checkedinflag) = @_; # PL object with provided flag in arguments
	$patientlocation->{_checkedinflag} = $checkedinflag; # set the flag
	return $patientlocation->{_checkedinflag};
}

#====================================================================================
# Subroutine to set the PL arrival date time  
#====================================================================================
sub setPatientLocationArrivalDateTime
{
	my ($patientlocation, $arrivaldatetime) = @_; # PL object with provided date in arguments
	$patientlocation->{_arrivaldatetime} = $arrivaldatetime; # set the date
	return $patientlocation->{_arrivaldatetime};
}

#====================================================================================
# Subroutine to set the PL venue serial 
#====================================================================================
sub setPatientLocationVenueSer
{
	my ($patientlocation, $venueser) = @_; # PL object with provided serial in arguments
	$patientlocation->{_venueser} = $venueser; # set the ser
	return $patientlocation->{_venueser};
}

#====================================================================================
# Subroutine to set the PL history date time 
#====================================================================================
sub setPatientLocationHstryDateTime
{
	my ($patientlocation, $hstrydatetime) = @_; # PL object with provided date in arguments
	$patientlocation->{_hstrydatetime} = $hstrydatetime; # set the date
	return $patientlocation->{_hstrydatetime};
}

#====================================================================================
# Subroutine to get the PL serial
#====================================================================================
sub getPatientLocationSer
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_ser};
}

#====================================================================================
# Subroutine to get the PL appointment serial
#====================================================================================
sub getPatientLocationAppointmentSer
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_appointmentser};
}

#====================================================================================
# Subroutine to get the PL source database serial
#====================================================================================
sub getPatientLocationSourceDatabaseSer
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_sourcedbser};
}

#====================================================================================
# Subroutine to get the PL source UID
#====================================================================================
sub getPatientLocationSourceUID
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_sourceuid};
}

#====================================================================================
# Subroutine to get the PL revision count
#====================================================================================
sub getPatientLocationRevisionCount
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_revcount};
}

#====================================================================================
# Subroutine to get the PL checked in flag
#====================================================================================
sub getPatientLocationCheckedInFlag
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_checkedinflag};
}

#====================================================================================
# Subroutine to get the PL arrival date time
#====================================================================================
sub getPatientLocationArrivalDateTime
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_arrivaldatetime};
}

#====================================================================================
# Subroutine to get the PL venue serial
#====================================================================================
sub getPatientLocationVenueSer
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_venueser};
}

#====================================================================================
# Subroutine to get the PL history date time
#====================================================================================
sub getPatientLocationHstryDateTime
{
	my ($patientlocation) = @_; # our PL object
	return $patientlocation->{_hstrydatetime};
}

#====================================================================================
# Subroutine to get patient location data from source DBs
#====================================================================================
sub getPatientLocationsFromSourceDB
{
	my (@patientList) = @_; # a list of patients

	my @patientLocationList = (); # initialize a list for PL objects

	my ($appointmentser, $sourceuid, $revcount, $checkedinflag, $arrivaldatetime, $venueser); # for query results
	my $lasttransfer;

	my @aliasList = Alias::getAliasesMarkedForUpdate('Appointment');

 	######################################
    # ARIA
    ######################################
    my $sourceDBSer = 1;
	{
        my $sourceDatabase	= Database::connectToSourceDatabase($sourceDBSer);

		my $expressionHash = {};
		my $expressionDict = {};
		foreach my $Alias (@aliasList) {
			my $aliasSourceDBSer 	= $Alias->getAliasSourceDatabaseSer();
			my $aliasSer 			= $Alias->getAliasSer();
			my @expressions         = $Alias->getAliasExpressions(); 

			if ($sourceDBSer eq $aliasSourceDBSer) {
		        if (!exists $expressionHash{$sourceDBSer}) {
		        	$expressionHash{$sourceDBSer} = {}; # intialize key value
		        }

		        foreach my $Expression (@expressions) {

		        	my $expressionSer = $Expression->{_ser};
		        	my $expressionName = $Expression->{_name};
		        	my $expressionLastTransfer = $Expression->{_lasttransfer};

		        	# append expression (surrounded by single quotes) to string
		        	if (exists $expressionHash{$sourceDBSer}{$expressionLastTransfer}) {
		        		$expressionHash{$sourceDBSer}{$expressionLastTransfer} .= ",'$expressionName'";
		        	} else {
		        		# start a new string 
		        		$expressionHash{$sourceDBSer}{$expressionLastTransfer} = "'$expressionName'";
		        	}

		        	$expressionDict{$expressionName} = $aliasSer;

		        }
		    }

		}

        my $patientInfo_sql = "
	    	WITH PatientInfo (SSN, LastTransfer, PatientSerNum) AS (
	    ";
	    my $numOfPatients = @patientList;
	    my $counter = 0;
	    foreach my $Patient (@patientList) {
	    	my $patientSer 			= $Patient->getPatientSer();
	    	my $patientSSN          = $Patient->getPatientSSN(); # get ssn
			my $patientLastTransfer	= $Patient->getPatientLastTransfer(); # get last updated

			$patientInfo_sql .= "
				SELECT '$patientSSN', '$patientLastTransfer', '$patientSer'
			";

			$counter++;
			if ( $counter < $numOfPatients ) {
				$patientInfo_sql .= "UNION";
			}
		}
		$patientInfo_sql .= ")";

		my $plInfo_sql = $patientInfo_sql . 
			"
				SELECT DISTINCT
					sa.ScheduledActivitySer,
					pl.PatientLocationSer,
					pl.PatientLocationRevCount,
					pl.CheckedInFlag,
					CONVERT(VARCHAR, pl.ArrivalDateTime, 120),
					pl.ResourceSer,
					PatientInfo.PatientSerNum,
					lt.Expression1
				FROM
					variansystem.dbo.Patient pt,
					variansystem.dbo.ScheduledActivity sa,
					variansystem.dbo.PatientLocation pl,
					variansystem.dbo.ActivityInstance ai,
					variansystem.dbo.Activity act,
					variansystem.dbo.LookupTable lt,
					PatientInfo
				WHERE
					sa.ActivityInstanceSer 			= ai.ActivityInstanceSer
				AND	sa.PatientSer 					= pt.PatientSer
				AND	LEFT(LTRIM(pt.SSN), 12)			= PatientInfo.SSN
				AND	ai.ActivitySer					= act.ActivitySer
				AND	act.ActivityCode 				= lt.LookupValue
				AND	sa.ScheduledActivitySer 		= pl.ScheduledActivitySer
				AND (
			";
		my $numOfExpressions = keys %{$expressionHash{$sourceDBSer}}; 
        my $counter = 0;
		# loop through each transfer date
        foreach my $lastTransferDate (keys %{$expressionHash{$sourceDBSer}}) {

            # concatenate query
    		$plInfo_sql .= "
				(REPLACE(lt.Expression1, '''', '')    	IN ($expressionHash{$sourceDBSer}{$lastTransferDate})
	        	AND pl.HstryDateTime	 				> (SELECT CASE WHEN '$lastTransferDate' > PatientInfo.LastTransfer THEN PatientInfo.LastTransfer ELSE '$lastTransferDate' END) )
    		";
    		$counter++;
    		# concat "UNION" until we've reached the last query
    		if ($counter < $numOfExpressions) {
    			$plInfo_sql .= "OR";
    		}
			# close bracket at end
			else {
				$plInfo_sql .= ")";
			}
    	}
    	# print "$plInfo_sql\n";
		# prepare query
		my $query = $sourceDatabase->prepare($plInfo_sql)
			or die "Could not prepare PL query: " . $sourceDatabase->errstr;

		# execute query
    	$query->execute()
        	or die "Could not execute query: " . $query->errstr;

		# Fetched all data, instead of fetching each row
		my $data = $query->fetchall_arrayref();
        foreach my $row (@$data) {

			my $patientlocation = new PatientLocation(); # new PL object

			my $patientSer 			= $row->[6];
			my $expressionName 		= $row->[7];

			$appointmentser 		= Appointment::reassignAppointment($row->[0], $sourceDBSer, $expressionDict{$expressionName}, $patientSer);
			$sourceuid 				= $row->[1];
			$revcount 				= $row->[2];
			$checkedinflag 			= $row->[3];
			$arrivaldatetime 		= $row->[4];
			$venueser 				= Venue::reassignVenue($row->[5], $sourceDBSer);

			$patientlocation->setPatientLocationAppointmentSer($appointmentser);
			$patientlocation->setPatientLocationSourceDatabaseSer($sourceDBSer);
			$patientlocation->setPatientLocationSourceUID($sourceuid);
			$patientlocation->setPatientLocationRevisionCount($revcount);
			$patientlocation->setPatientLocationCheckedInFlag($checkedinflag);
			$patientlocation->setPatientLocationArrivalDateTime($arrivaldatetime);
			$patientlocation->setPatientLocationVenueSer($venueser);

			push(@patientLocationList, $patientlocation);
		}

		# empty hash
    	for (keys %{$expressionHash{$sourceDBSer}}) { delete $expressionHash{$sourceDBSer}{$_}; }

		$sourceDatabase->disconnect();
	}

	######################################
    # MediVisit
    ######################################
    my $sourceDBSer = 2;
	{
        my $sourceDatabase	= Database::connectToSourceDatabase($sourceDBSer);

        my $expressionHash = {};
		my $expressionDict = {};
		foreach my $Alias (@aliasList) {
			my $aliasSourceDBSer 	= $Alias->getAliasSourceDatabaseSer();
			my $aliasSer 			= $Alias->getAliasSer();
			my @expressions         = $Alias->getAliasExpressions(); 

			if ($sourceDBSer eq $aliasSourceDBSer) {
		        if (!exists $expressionHash{$sourceDBSer}) {
		        	$expressionHash{$sourceDBSer} = {}; # intialize key value
		        }

		        foreach my $Expression (@expressions) {

		        	my $expressionSer = $Expression->{_ser};
		        	my $expressionName = $Expression->{_name};
		        	my $expressionDesc = $Expression->{_description};
		        	my $expressionLastTransfer = $Expression->{_lasttransfer};

		        	# append expression (surrounded by single quotes) to string
		        	if (exists $expressionHash{$sourceDBSer}{$expressionLastTransfer}) {
		        		$expressionHash{$sourceDBSer}{$expressionLastTransfer} .= ",('$expressionName','$expressionDesc')";
		        	} else {
		        		# start a new string 
		        		$expressionHash{$sourceDBSer}{$expressionLastTransfer} = "('$expressionName','$expressionDesc')";
		        	}

		        	$expressionDict{$expressionName}{$expressionDesc} = $aliasSer;

		        }

		    }

		}

        my $patientInfo_sql = "";
	    my $numOfPatients = @patientList;
	    my $counter = 0;
	    foreach my $Patient (@patientList) {
	    	my $patientSer 			= $Patient->getPatientSer();
	    	my $patientSSN          = $Patient->getPatientSSN(); # get ssn
			my $patientLastTransfer	= $Patient->getPatientLastTransfer(); # get last updated

			$patientInfo_sql .= "
				SELECT '$patientSSN' as SSN, '$patientLastTransfer' as LastTransfer, '$patientSer' as PatientSerNum
			";

			$counter++;
			if ( $counter < $numOfPatients ) {
				$patientInfo_sql .= "UNION";
			}
		}

		my $plInfo_sql = "
			SELECT DISTINCT
				mval.AppointmentSerNum,
				pl.PatientLocationSerNum,
				pl.PatientLocationRevCount,
				'1' as CheckedInFlag,
				pl.ArrivalDateTime,
				Venue.ResourceSer,
				pi.PatientSerNum,
				mval.AppointmentCode,
				mval.ResourceDescription
			FROM
				MediVisitAppointmentList mval,
				PatientLocation pl,
				Venue,
				Patient pt
			JOIN ($patientInfo_sql) pi
            ON 	LEFT(LTRIM(pt.SSN), 12)  = pi.SSN	
			WHERE
				mval.PatientSerNum 			= pt.PatientSerNum
			AND mval.AppointmentSerNum		= pl.AppointmentSerNum
			AND Venue.VenueId 				= pl.CheckinVenueName
			AND (
		";

		my $numOfExpressions = keys %{$expressionHash{$sourceDBSer}}; 
        my $counter = 0;
		# loop through each transfer date
        foreach my $lastTransferDate (keys %{$expressionHash{$sourceDBSer}}) {

            # concatenate query
    		$plInfo_sql .= "
    			((mval.AppointmentCode, mval.ResourceDescription) IN ($expressionHash{$sourceDBSer}{$lastTransferDate})
    			AND mval.LastUpdated	> (SELECT IF ('$lastTransferDate' > pi.LastTransfer, pi.LastTransfer, '$lastTransferDate')))
			";
    		$counter++;
    		# concat "UNION" until we've reached the last query
    		if ($counter < $numOfExpressions) {
    			$plInfo_sql .= "OR";
    		}
			# close bracket at end
			else {
				$plInfo_sql .= ")";
			}
    	}

    	#print "$plInfo_sql\n";	
        # prepare query
	    my $query = $sourceDatabase->prepare($plInfo_sql)
		    or die "Could not prepare query: " . $sourceDatabase->errstr;

		# execute query
    	$query->execute()
        	or die "Could not execute query: " . $query->errstr;

		# Fetched all data, instead of fetching each row
		my $data = $query->fetchall_arrayref();
        foreach my $row (@$data) {

			my $patientlocation = new PatientLocation(); # new PL object

			my $patientSer 			= $row->[6];
			my $expressionName 		= $row->[7];
			my $expressionDesc 		= $row->[8];
			$appointmentser 		= Appointment::reassignAppointment($row->[0], $sourceDBSer, $aliasSer, $patientSer);
			$sourceuid 				= $row->[1];
			$revcount 				= $row->[2];
			$checkedinflag 			= $row->[3];
			$arrivaldatetime 		= $row->[4];
			$venueser 				= Venue::reassignVenue($row->[5], $sourceDBSer);

			$patientlocation->setPatientLocationAppointmentSer($appointmentser);
			$patientlocation->setPatientLocationSourceDatabaseSer($sourceDBSer);
			$patientlocation->setPatientLocationSourceUID($sourceuid);
			$patientlocation->setPatientLocationRevisionCount($revcount);
			$patientlocation->setPatientLocationCheckedInFlag($checkedinflag);
			$patientlocation->setPatientLocationArrivalDateTime($arrivaldatetime);
			$patientlocation->setPatientLocationVenueSer($venueser);

			push(@patientLocationList, $patientlocation);
		}

		# empty hash
    	for (keys %{$expressionHash{$sourceDBSer}}) { delete $expressionHash{$sourceDBSer}{$_}; }

		$sourceDatabase->disconnect();

	}

	 ######################################
    # MOSAIQ
    ######################################
    my $sourceDBSer = 3;
	# {
  #       my $sourceDatabase	= Database::connectToSourceDatabase($sourceDBSer);

  #       my $expressionHash = {};
		# my $expressionDict = {};
		# foreach my $Alias (@aliasList) {
			# my $aliasSourceDBSer 	= $Alias->getAliasSourceDatabaseSer();
		# 	my @expressions         = $Alias->getAliasExpressions(); 

			# if ($sourceDBSer eq $aliasSourceDBSer) {
		 #        if (!exists $expressionHash{$sourceDBSer}) {
		 #        	$expressionHash{$sourceDBSer} = {}; # intialize key value
		 #        }

		 #        foreach my $Expression (@expressions) {

		 #        	my $expressionSer = $Expression->{_ser};
		 #        	my $expressionName = $Expression->{_name};
		 #        	my $expressionDesc = $Expression->{_description};
		 #        	my $expressionLastTransfer = $Expression->{_lasttransfer};

		 #        	# append expression (surrounded by single quotes) to string
		 #        	if (exists $expressionHash{$sourceDBSer}{$expressionLastTransfer}) {
		 #        		$expressionHash{$sourceDBSer}{$expressionLastTransfer} .= ",('$expressionName','$expressionDesc')";
		 #        	} else {
		 #        		# start a new string 
		 #        		$expressionHash{$sourceDBSer}{$expressionLastTransfer} = "('$expressionName','$expressionDesc')";
		 #        	}

		 #        	$expressionDict{$expressionName}{$expressionDesc} = $expressionSer;

		 #        }
				# }

		# }

		# patient and appointment query here
        # $sourceDatabase->disconnect();

	# }


	return @patientLocationList;

}

#====================================================================================
# Subroutine to get patient location MH data from source DBs
#====================================================================================
sub getPatientLocationsMHFromSourceDB
{
	my ($patientList, $PLList) = @_; # a list of patients and patient locations

	my @patientLocationMHList = (); # initialize a list for PL objects

	my ($appointmentser, $sourceuid, $revcount, $checkedinflag, $arrivaldatetime, $venueser, $hstrydatetime); # for query results
	my $lasttransfer;

	foreach my $Patient (@$patientList) {

		my $patientSer 				= $Patient->getPatientSer();
		my $patientSSN 				= $Patient->getPatientSSN();
		my $patientLastTransfer		= $Patient->getPatientLastTransfer();

		# reformat patient last transfer date 
		my $formatted_PLU = Time::Piece->strptime($patientLastTransfer, "%Y-%m-%d %H:%M:%S");

		foreach my $patientLocation (@$PLList) {

			my $sourceDBSer 		= $patientLocation->getPatientLocationSourceDatabaseSer();
			my $sourceuid 			= $patientLocation->getPatientLocationSourceUID();
			my $appointmentser 		= $patientLocation->getPatientLocationAppointmentSer();

			######################################
		    # ARIA
		    ######################################
			if ($sourceDBSer eq 1) {

				my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);

				my $plInfo_sql = "
					SELECT DISTINCT
						plmh.PatientLocationSer,
						plmh.PatientLocationRevCount,
						plmh.CheckedInFlag,
						CONVERT(VARCHAR, plmh.ArrivalDateTime, 120),
						plmh.ResourceSer,
						CONVERT(VARCHAR, plmh.HstryDateTime, 120)
					FROM
						variansystem.dbo.Patient pt,
						variansystem.dbo.ScheduledActivity sa,
						variansystem.dbo.PatientLocationMH plmh
					WHERE
						sa.PatientSer 					= pt.PatientSer
					AND	LEFT(LTRIM(pt.SSN), 12)			= '$patientSSN'
					AND	sa.ScheduledActivitySer 		= plmh.ScheduledActivitySer
					AND plmh.PatientLocationSer 		= '$sourceuid'
				";

				# print "$plInfo_sql\n";
				# prepare query
				my $query = $sourceDatabase->prepare($plInfo_sql)
					or die "Could not prepare PL query: " . $sourceDatabase->errstr;

				# execute query
	        	$query->execute()
		        	or die "Could not execute query: " . $query->errstr;
    
        		# Fetched all data, instead of fetching each row
        		my $data = $query->fetchall_arrayref();
		        
		        foreach my $row (@$data) {

					my $patientlocationMH = new PatientLocation(); # new PL object

					$sourceuid 				= $row->[0];
					$revcount 				= $row->[1];
					$checkedinflag 			= $row->[2];
					$arrivaldatetime 		= $row->[3];
					$venueser 				= Venue::reassignVenue($row->[4], $sourceDBSer);
					$hstrydatetime 			= $row->[5];

					$patientlocationMH->setPatientLocationAppointmentSer($appointmentser);
					$patientlocationMH->setPatientLocationSourceDatabaseSer($sourceDBSer);
					$patientlocationMH->setPatientLocationSourceUID($sourceuid);
					$patientlocationMH->setPatientLocationRevisionCount($revcount);
					$patientlocationMH->setPatientLocationCheckedInFlag($checkedinflag);
					$patientlocationMH->setPatientLocationArrivalDateTime($arrivaldatetime);
					$patientlocationMH->setPatientLocationVenueSer($venueser);
					$patientlocationMH->setPatientLocationHstryDateTime($hstrydatetime);
					push(@patientLocationMHList, $patientlocationMH);
				}

				$sourceDatabase->disconnect();
			}

			######################################
		    # MediVisit
		    ######################################
			if ($sourceDBSer eq 2) {

				my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);

				my $plInfo_sql = "
					SELECT DISTINCT
						plmh.PatientLocationSerNum,
						plmh.PatientLocationRevCount,
						'1' as CheckedInFlag,
						plmh.ArrivalDateTime,
						Venue.ResourceSer,
						plmh.DichargeThisLocationDateTime
					FROM
						Patient pt,
						MediVisitAppointmentList mval,
						PatientLocation pl,
						PatientLocationMH plmh,
						Venue
					WHERE
						mval.PatientSerNum 			= pt.PatientSerNum
					AND	LEFT(LTRIM(pt.SSN), 12)		= '$patientSSN'
					AND mval.AppointmentSerNum		= plmh.AppointmentSerNum
					AND plmh.CheckinVenueName  		= Venue.VenueId
					AND pl.PatientLocationSerNum 	= '$sourceuid'
				";

                # print "$plInfo_sql\n";	
		        # prepare query
    		    my $query = $sourceDatabase->prepare($plInfo_sql)
	    		    or die "Could not prepare query: " . $sourceDatabase->errstr;

        		# execute query
	        	$query->execute()
		        	or die "Could not execute query: " . $query->errstr;
    
        		# Fetched all data, instead of fetching each row
        		my $data = $query->fetchall_arrayref();
		        foreach my $row (@$data) {

					my $patientlocationMH = new PatientLocation(); # new PL object

					$sourceuid 				= $row->[0];
					$revcount 				= $row->[1];
					$checkedinflag 			= $row->[2];
					$arrivaldatetime 		= $row->[3];
					$venueser 				= Venue::reassignVenue($row->[4], $sourceDBSer);
					$hstrydatetime 			= $row->[5];

					$patientlocationMH->setPatientLocationAppointmentSer($appointmentser);
					$patientlocationMH->setPatientLocationSourceDatabaseSer($sourceDBSer);
					$patientlocationMH->setPatientLocationSourceUID($sourceuid);
					$patientlocationMH->setPatientLocationRevisionCount($revcount);
					$patientlocationMH->setPatientLocationCheckedInFlag($checkedinflag);
					$patientlocationMH->setPatientLocationArrivalDateTime($arrivaldatetime);
					$patientlocationMH->setPatientLocationVenueSer($venueser);
					$patientlocationMH->setPatientLocationHstryDateTime($hstrydatetime);

					push(@patientLocationMHList, $patientlocationMH);
				}

				$sourceDatabase->disconnect();

			}

			######################################
		    # MOSAIQ
		    ######################################
            if ($sourceDBSer eq 3) {
  
                my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);

                my $numOfExpressions = @expressions; 
                my $counter = 0;
                my $plInfo_sql = "";

                foreach my $Expression (@expressions) {

                	my $expressionser = $Expression->{_ser};
                	my $expressionName = $Expression->{_name};
                	my $expressionLastTransfer = $Expression->{_lasttransfer};
                	my $formatted_ELU = Time::Piece->strptime($expressionLastTransfer, "%Y-%m-%d %H:%M:%S");

                	# compare last updates to find the earliest date 
		            # get the diff in seconds
		            my $date_diff = $formatted_PLU - $formatted_ELU;
		            if ($date_diff < 0) {
		                $lasttransfer = $patientLastTransfer;
		            } else {
		                $lasttransfer = $expressionLastTransfer;
		            }

	        		$plInfo_sql .= "SELECT 'QUERY_HERE' ";

	        		$counter++;
	        		# concat "UNION" until we've reached the last query
	        		if ($counter < $numOfExpressions) {
	        			$plInfo_sql .= "UNION";
	        		}
	        	}

		        # prepare query
    		    my $query = $sourceDatabase->prepare($plInfo_sql)
	    		    or die "Could not prepare query: " . $sourceDatabase->errstr;

        		# execute query
	        	$query->execute()
		        	or die "Could not execute query: " . $query->errstr;
    
        		# Fetched all data, instead of fetching each row
        		my $data = $query->fetchall_arrayref();
		        foreach my $row (@$data) {
			    
    			    #my $patientlocation = new PatientLocation(); # uncomment to use
                
                	# use setters to set appropriate PL information from query

                	#push(@patientLocationMHList, $patientlocation);
                }

                $sourceDatabase->disconnect();
            }

		}
	}

	return @patientLocationMHList;

}

#======================================================================================
# Subroutine to check if a particular PL exists in our MySQL db
#	@return: PL object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
	my ($patientlocation) = @_; # PL object

	my $sourceUID = $patientlocation->getPatientLocationSourceUID();
	my $sourceDBSer = $patientlocation->getPatientLocationSourceDatabaseSer();

	my $PLSourceUIDInDB = 0; # false by default. Will be true if PL exists
	my $ExistingPL = (); # data to be entered if PL exists 

	# Other pl variables, if pl exists
	my ($ser, $appointmentser, $revcount, $checkedinflag, $arrivaldatetime, $venueser);

	my $inDB_sql = "
		SELECT DISTINCT
			pl.PatientLocationSerNum,
			pl.SourceUID,
			pl.AppointmentSerNum,
			pl.RevCount,
			pl.CheckedInFlag,
			pl.ArrivalDateTime,
			pl.VenueSerNum
		FROM
			PatientLocation pl 
		WHERE
			pl.SourceDatabaseSerNum  	= $sourceDBSer
		AND pl.SourceUID				= $sourceUID
	";

	# prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

		$ser 				= $data[0];
		$PLSourceUIDInDB	= $data[1];
		$appointmentser		= $data[2];
		$revcount 			= $data[3];
		$checkedinflag		= $data[4];
		$arrivaldatetime 	= $data[5];
		$venueser 			= $data[6];
	}

	if ($PLSourceUIDInDB) {

		$ExistingPL = new PatientLocation(); # initialize pl object

		$ExistingPL->setPatientLocationSer($ser);
		$ExistingPL->setPatientLocationSourceDatabaseSer($sourceDBSer);
		$ExistingPL->setPatientLocationSourceUID($PLSourceUIDInDB);
		$ExistingPL->setPatientLocationAppointmentSer($appointmentser);
		$ExistingPL->setPatientLocationRevisionCount($revcount);
		$ExistingPL->setPatientLocationCheckedInFlag($checkedinflag);
		$ExistingPL->setPatientLocationArrivalDateTime($arrivaldatetime);
		$ExistingPL->setPatientLocationVenueSer($venueser);

		return $ExistingPL; # this is true (i.e. PL exists, return object)
	}

	else {return $ExistingPL;} # this is false (i.e. PL DNE, return empty)
}

#======================================================================================
# Subroutine to check if a particular PLMH exists in our MySQL db
#	@return: PLMH object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabaseMH
{
	my ($patientlocation) = @_; # PL object

	my $sourceUID = $patientlocation->getPatientLocationSourceUID();
	my $sourceDBSer = $patientlocation->getPatientLocationSourceDatabaseSer();

	my $PLSourceUIDInDB = 0; # false by default. Will be true if PL exists
	my $ExistingPL = (); # data to be entered if PL exists 

	# Other pl variables, if pl exists
	my ($ser, $appointmentser, $revcount, $checkedinflag, $arrivaldatetime, $venueser, $hstrydatetime);

	my $inDB_sql = "
		SELECT DISTINCT
			plmh.PatientLocationMHSerNum,
			plmh.SourceUID,
			plmh.AppointmentSerNum,
			plmh.RevCount,
			plmh.CheckedInFlag,
			plmh.ArrivalDateTime,
			plmh.VenueSerNum,
			plmh.HstryDateTime
		FROM
			PatientLocationMH plmh 
		WHERE
			plmh.SourceDatabaseSerNum  	= $sourceDBSer
		AND plmh.SourceUID				= $sourceUID
	";

	# prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

		$ser 				= $data[0];
		$PLSourceUIDInDB	= $data[1];
		$appointmentser		= $data[2];
		$revcount 			= $data[3];
		$checkedinflag		= $data[4];
		$arrivaldatetime 	= $data[5];
		$venueser 			= $data[6];
		$hstrydatetime 		= $data[7];
	}

	if ($PLSourceUIDInDB) {

		$ExistingPL = new PatientLocation(); # initialize pl object

		$ExistingPL->setPatientLocationSer($ser);
		$ExistingPL->setPatientLocationSourceDatabaseSer($sourceDBSer);
		$ExistingPL->setPatientLocationSourceUID($PLSourceUIDInDB);
		$ExistingPL->setPatientLocationAppointmentSer($appointmentser);
		$ExistingPL->setPatientLocationRevisionCount($revcount);
		$ExistingPL->setPatientLocationCheckedInFlag($checkedinflag);
		$ExistingPL->setPatientLocationArrivalDateTime($arrivaldatetime);
		$ExistingPL->setPatientLocationVenueSer($venueser);
		$ExistingPL->setPatientLocationHstryDateTime($hstrydatetime);

		return $ExistingPL; # this is true (i.e. PL exists, return object)
	}

	else {return $ExistingPL;} # this is false (i.e. PL DNE, return empty)
}

#======================================================================================
# Subroutine to insert our patient location info in our database
#======================================================================================
sub insertPatientLocationIntoOurDB
{
	my ($patientlocation) = @_; # our patient location object

	my $sourcedbser 		= $patientlocation->getPatientLocationSourceDatabaseSer();
	my $sourceuid			= $patientlocation->getPatientLocationSourceUID();
	my $appointmentser		= $patientlocation->getPatientLocationAppointmentSer();
	my $revcount			= $patientlocation->getPatientLocationRevisionCount();
	my $checkedinflag		= $patientlocation->getPatientLocationCheckedInFlag();
	my $arrivaldatetime 	= $patientlocation->getPatientLocationArrivalDateTime();
	my $venueser			= $patientlocation->getPatientLocationVenueSer();

	my $insert_sql = "
		INSERT INTO 
			PatientLocation (
				SourceDatabaseSerNum,
				SourceUID,
				AppointmentSerNum,
				RevCount,
				CheckedInFlag,
				ArrivalDateTime,
				VenueSerNum,
				DateAdded
			)
		VALUE (
			'$sourcedbser',
			'$sourceuid',
			'$appointmentser',
			'$revcount',
			'$checkedinflag',
			'$arrivaldatetime',
			'$venueser',
			NOW()
		)
	";

	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the PL serial
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the serial in our PL object
	$patientlocation->setPatientLocationSer($ser);

	return $patientlocation;
}

#======================================================================================
# Subroutine to insert our patient location MH info in our database
#======================================================================================
sub insertPatientLocationMHIntoOurDB
{
	my ($patientlocation) = @_; # our patient location object

	my $sourcedbser 		= $patientlocation->getPatientLocationSourceDatabaseSer();
	my $sourceuid			= $patientlocation->getPatientLocationSourceUID();
	my $appointmentser		= $patientlocation->getPatientLocationAppointmentSer();
	my $revcount			= $patientlocation->getPatientLocationRevisionCount();
	my $checkedinflag		= $patientlocation->getPatientLocationCheckedInFlag();
	my $arrivaldatetime 	= $patientlocation->getPatientLocationArrivalDateTime();
	my $venueser			= $patientlocation->getPatientLocationVenueSer();
	my $hstrydatetime 		= $patientlocation->getPatientLocationHstryDateTime();

	my $insert_sql = "
		INSERT INTO 
			PatientLocationMH (
				SourceDatabaseSerNum,
				SourceUID,
				AppointmentSerNum,
				RevCount,
				CheckedInFlag,
				ArrivalDateTime,
				VenueSerNum,
				HstryDateTime,
				DateAdded
			)
		VALUE (
			'$sourcedbser',
			'$sourceuid',
			'$appointmentser',
			'$revcount',
			'$checkedinflag',
			'$arrivaldatetime',
			'$venueser',
			'$hstrydatetime',
			NOW()
		)
	";

	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the PL serial
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the serial in our PL object
	$patientlocation->setPatientLocationSer($ser);

	return $patientlocation;
}

#======================================================================================
# Subroutine to update our database with the PL's updated info
#======================================================================================
sub updateDatabase
{
	my ($patientlocation) = @_; # our patient location object

	my $sourcedbser 		= $patientlocation->getPatientLocationSourceDatabaseSer();
	my $sourceuid			= $patientlocation->getPatientLocationSourceUID();
	my $revcount			= $patientlocation->getPatientLocationRevisionCount();
	my $checkedinflag		= $patientlocation->getPatientLocationCheckedInFlag();
	my $arrivaldatetime 	= $patientlocation->getPatientLocationArrivalDateTime();
	my $venueser			= $patientlocation->getPatientLocationVenueSer();

	my $update_sql = "
		UPDATE
			PatientLocation
		SET
			RevCount		= '$revcount',
			CheckedInFlag 	= '$checkedinflag',
			ArrivalDateTime = '$arrivaldatetime',
			VenueSerNum 	= '$venueser'
		WHERE
			SourceDatabaseSerNum 	= '$sourcedbser'
		AND	SourceUID				= '$sourceuid'
	";
	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
}

#======================================================================================
# Subroutine to update our database with the PL's updated MH info
#======================================================================================
sub updateDatabaseMH
{
	my ($patientlocation) = @_; # our patient location object

	my $sourcedbser 		= $patientlocation->getPatientLocationSourceDatabaseSer();
	my $sourceuid			= $patientlocation->getPatientLocationSourceUID();
	my $revcount			= $patientlocation->getPatientLocationRevisionCount();
	my $checkedinflag		= $patientlocation->getPatientLocationCheckedInFlag();
	my $arrivaldatetime 	= $patientlocation->getPatientLocationArrivalDateTime();
	my $venueser			= $patientlocation->getPatientLocationVenueSer();

	my $update_sql = "
		UPDATE
			PatientLocationMH
		SET
			RevCount		= '$revcount',
			CheckedInFlag 	= '$checkedinflag',
			ArrivalDateTime = '$arrivaldatetime',
			VenueSerNum 	= '$venueser'
		WHERE
			SourceDatabaseSerNum 	= '$sourcedbser'
		AND	SourceUID				= '$sourceuid'
	";
	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
}

#======================================================================================
# Subroutine to compare two PL objects. If different, use setter functions
# to update PL object.
#======================================================================================
sub compareWith
{
	my ($SuspectPL, $OriginalPL) = @_; # our two PL objects from arguments
	my $UpdatedPL = dclone($OriginalPL);

	# retrive params
	# Suspect PL...
	my $SRevCount 			= $SuspectPL->getPatientLocationRevisionCount();
	my $SCheckedInFlag 		= $SuspectPL->getPatientLocationCheckedInFlag();
	my $SArrivalDateTime 	= $SuspectPL->getPatientLocationArrivalDateTime();
	my $SVenueSer 			= $SuspectPL->getPatientLocationVenueSer();

	# Original PL...
	my $ORevCount 			= $OriginalPL->getPatientLocationRevisionCount();
	my $OCheckedInFlag 		= $OriginalPL->getPatientLocationCheckedInFlag();
	my $OArrivalDateTime 	= $OriginalPL->getPatientLocationArrivalDateTime();
	my $OVenueSer 			= $OriginalPL->getPatientLocationVenueSer();

	# go through each param

	if ($SRevCount ne $ORevCount) {
		print "Patient Location revision count has changed from '$ORevCount' to '$SRevCount'\n";
		my $updatedRevCount = $UpdatedPL->setPatientLocationRevisionCount($SRevCount); # update count
		print "Will update database entry to '$updatedRevCount'.\n";
	}
	if ($SCheckedInFlag ne $OCheckedInFlag) {
		print "Patient Location checked in flag has changed from '$OCheckedInFlag' to '$SCheckedInFlag'\n";
		my $updatedCheckedInFlag = $UpdatedPL->setPatientLocationCheckedInFlag($SCheckedInFlag); # update flag
		print "Will update database entry to '$updatedCheckedInFlag'.\n";
	}
	if ($SArrivalDateTime ne $OArrivalDateTime) {
		print "Patient Location arrival datetime has changed from '$OArrivalDateTime' to '$SArrivalDateTime'\n";
		my $updatedArrivalDT = $UpdatedPL->setPatientLocationArrivalDateTime($SArrivalDateTime); # update date
		print "Will update database entry to '$updatedArrivalDT'.\n";
	}
	if ($SVenueSer ne $OVenueSer) {
		print "Patient Location venue serial has changed from '$OVenueSer' to '$SVenueSer'\n";
		my $updatedVenueSer = $UpdatedPL->setPatientLocationVenueSer($SVenueSer); # update serial
		print "Will update database entry to '$updatedVenueSer'.\n";
	}

	return $UpdatedPL;
}

# To exit/return always true (for the module itself)
1;
