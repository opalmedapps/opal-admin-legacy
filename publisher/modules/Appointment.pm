#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 10-Aug-2015 ++ File: Appointment.pm
#---------------------------------------------------------------------------------
# Perl module that creates an appointment class. This module calls a constructor to 
# create an appointment object that contains appt information stored as object 
# variables.
#
# There exists various subroutines to set appt information, get appt information
# and compare appt information between two appt objects. 
# There exists various subroutines that use the Database.pm module to update the
# MySQL database and check if an appt exists already in this database.

package Appointment; # Declare package name


use Exporter; # To export subroutines and variables
use Database; # Use our custom database module Database.pm
use Date::Language; # To format date according to language
use Date::Format qw(time2str); # To format date
use DateTime::Format::Strptime;
use Time::Piece; # To parse and convert date time
use Storable qw(dclone); # for deep copies
use POSIX;
use String::Util 'trim';
use Data::Dumper;

use Patient; # Our patient module
use Alias; # Our alias module
use Resource; # Resource.pm
use Priority; # Priority.pm
use Diagnosis; # Diagnosis.pm
use PushNotification; # PushNotification.pm

#---------------------------------------------------------------------------------
# Connect to the database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Appointment class 
#====================================================================================
sub new
{
	my $class = shift;
	my $appointment = {
		_ser			    => undef,
        _sourcedbser        => undef,
		_sourceuid	        => undef,
		_patientser		    => undef,
        _aliasser           => undef,
		_aliasexpressionser	=> undef,
		_startdatetime		=> undef,
		_enddatetime		=> undef,
        _diagnosisser       => undef,
        _priorityser        => undef,
        _status             => undef,
        _state              => undef,
        _actualstartdate    => undef,
        _actualenddate      => undef,
        _checkin 			=> undef,
        _cronlogser			=> undef,
	};

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $appointment, $class; 
	return $appointment;
}

#====================================================================================
# Subroutine to set the Appointment Serial
#====================================================================================
sub setApptSer
{
	my ($appointment, $ser) = @_; # appt object with provided serial in arguments
	$appointment->{_ser} = $ser; # set the appt ser
	return $appointment->{_ser};
}


#======================================================================================
# Subroutine to set the Appointment Source DB Serial
#====================================================================================
sub setApptSourceDatabaseSer
{
	my ($appointment, $sourcedbser) = @_; # appt object with provided serial in arguments
	$appointment->{_sourcedbser} = $sourcedbser; # set the appt ser
	return $appointment->{_sourcedbser};
}
#
#==================================================================================
# Subroutine to set the Appointment Source UID
#====================================================================================
sub setApptSourceUID
{
	my ($appointment, $sourceuid) = @_; # appt object with provided serial in arguments
	$appointment->{_sourceuid} = $sourceuid; # set the appt serial
	return $appointment->{_sourceuid};
}

#====================================================================================
# Subroutine to set the Appointment Patient serial
#====================================================================================
sub setApptPatientSer
{
	my ($appointment, $patientser) = @_; # appt object with provided serial in arguments
	$appointment->{_patientser} = $patientser; # set the appt serial
	return $appointment->{_patientser};
}

#====================================================================================
# Subroutine to set the Appointment Alias Ser
#====================================================================================
sub setApptAliasSer
{
	my ($appointment, $aliasser) = @_; # appt object with provided serial in arguments
	$appointment->{_aliasser} = $aliasser;
	return $appointment->{_aliasser};
}

#====================================================================================
# Subroutine to set the Appointment Alias Expression Ser
#====================================================================================
sub setApptAliasExpressionSer
{
	my ($appointment, $aliasexpressionser) = @_; # appt object with provided serial in arguments
	$appointment->{_aliasexpressionser} = $aliasexpressionser;
	return $appointment->{_aliasexpressionser};
}

#====================================================================================
# Subroutine to set the Appointment Status
#====================================================================================
sub setApptStatus
{
	my ($appointment, $status) = @_; # appt object with provided status in arguments
	$appointment->{_status} = $status; # set the appt status
	return $appointment->{_status};
}

#====================================================================================
# Subroutine to set the Appointment State
#====================================================================================
sub setApptState
{
	my ($appointment, $state) = @_; # appt object with provided state in arguments
	$appointment->{_state} = $state; # set the appt state
	return $appointment->{_state};
}

#====================================================================================
# Subroutine to set the Appointment Actual Start DateTime
#====================================================================================
sub setApptStartDateTime
{
	my ($appointment, $startdatetime) = @_; # appt object with provided datetime in arguments
	$appointment->{_startdatetime} = $startdatetime; # set the appt datetime
	return $appointment->{_startdatetime};
}

#====================================================================================
# Subroutine to set the Appointment Actual End DateTime
#====================================================================================
sub setApptEndDateTime
{
	my ($appointment, $enddatetime) = @_; # appt object with provided datetime in arguments
	$appointment->{_enddatetime} = $enddatetime; # set the appt datetime
	return $appointment->{_enddatetime};
}

#====================================================================================
# Subroutine to set the Appointment Actual Start DateTime
#====================================================================================
sub setApptActualStartDate
{
	my ($appointment, $actualstartdate) = @_; # appt object with provided datetime in arguments
	$appointment->{_actualstartdate} = $actualstartdate; # set the appt datetime
	return $appointment->{_actualstartdate};
}

#====================================================================================
# Subroutine to set the Appointment Actual End DateTime
#====================================================================================
sub setApptActualEndDate
{
	my ($appointment, $actualenddate) = @_; # appt object with provided datetime in arguments
	$appointment->{_actualenddate} = $actualenddate; # set the appt datetime
	return $appointment->{_actualenddate};
}

#====================================================================================
# Subroutine to set the Appointment Priority serial
#====================================================================================
sub setApptPrioritySer
{
	my ($appointment, $priorityser) = @_; # appt object with provided serial in arguments
	$appointment->{_priorityser} = $priorityser; # set the appt serial
	return $appointment->{_priorityser};
}

#====================================================================================
# Subroutine to set the Appointment Diagnosis serial
#====================================================================================
sub setApptDiagnosisSer
{
	my ($appointment, $diagnosisser) = @_; # appt object with provided serial in arguments
	$appointment->{_diagnosisser} = $diagnosisser; # set the appt serial
	return $appointment->{_diagnosisser};
}

#====================================================================================
# Subroutine to set the Appointment Checkin flag
#====================================================================================
sub setApptCheckin
{
	my ($appointment, $checkin) = @_; # appt object with provided flag in arguments
	$appointment->{_checkin} = $checkin; # set the flag
	return $appointment->{_checkin};
}

#====================================================================================
# Subroutine to set the Appointment Cron Log Serial
#====================================================================================
sub setApptCronLogSer
{
	my ($appointment, $cronlogser) = @_; # appt object with provided serial in arguments
	$appointment->{_cronlogser} = $cronlogser; # set the ser
	return $appointment->{_cronlogser};
}

#====================================================================================
# Subroutine to get the Appointment Serial
#====================================================================================
sub getApptSer
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_ser};
}

#====================================================================================
# Subroutine to get the Appointment Source DB Serial
#====================================================================================
sub getApptSourceDatabaseSer
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_sourcedbser};
}

#====================================================================================
# Subroutine to get the Appointment Source UID
#====================================================================================
sub getApptSourceUID
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_sourceuid};
}

#====================================================================================
# Subroutine to get the Appointment patient serial
#====================================================================================
sub getApptPatientSer
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_patientser};
}

#====================================================================================
# Subroutine to get the Appointment Alias Ser
#====================================================================================
sub getApptAliasSer
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_aliasser};
}

#====================================================================================
# Subroutine to get the Appointment Alias Expression Ser
#====================================================================================
sub getApptAliasExpressionSer
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_aliasexpressionser};
}

#====================================================================================
# Subroutine to get the Appointment Status
#====================================================================================
sub getApptStatus
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_status};
}

#====================================================================================
# Subroutine to get the Appointment State
#====================================================================================
sub getApptState
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_state};
}

#====================================================================================
# Subroutine to get the Appointment Start DateTime
#====================================================================================
sub getApptStartDateTime
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_startdatetime};
}

#====================================================================================
# Subroutine to get the Appointment End DateTime
#====================================================================================
sub getApptEndDateTime
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_enddatetime};
}

#====================================================================================
# Subroutine to get the Appointment Actual Start Date
#====================================================================================
sub getApptActualStartDate
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_actualstartdate};
}

#====================================================================================
# Subroutine to get the Appointment Actual End Date
#====================================================================================
sub getApptActualEndDate
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_actualenddate};
}

#====================================================================================
# Subroutine to get the Appointment priority serial
#====================================================================================
sub getApptPrioritySer
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_priorityser};
}

#====================================================================================
# Subroutine to get the Appointment diagnosis serial
#====================================================================================
sub getApptDiagnosisSer
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_diagnosisser};
}

#====================================================================================
# Subroutine to get the Appointment checkin
#====================================================================================
sub getApptCheckin
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_checkin};
}

#====================================================================================
# Subroutine to get the Appointment Cron Log Serial
#====================================================================================
sub getApptCronLogSer
{
	my ($appointment) = @_; # our appt object
	return $appointment->{_cronlogser};
}

#======================================================================================
# Subroutine to get our appointment info from the ARIA db for automatic cron
#======================================================================================
sub getApptsFromSourceDB
{
	my ($cronLogSer, @patientList) = @_; # patient list and cron log serial from args

	my @apptList = (); # initialize a list for appointment objects

	# when we retrieve query results
	my ($sourceuid, $startdatetime, $enddatetime, $resourceser, $priorityser, $diagnosisser);
    my ($status, $state, $actualstartdate, $actualenddate);
    my $lasttransfer; 

    # retrieve all aliases that are marked for update
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

		        	$expressionDict{$expressionName} = $expressionSer;

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

		my $apptInfo_sql = $patientInfo_sql . 
			"
				SELECT DISTINCT
					sa.ScheduledActivitySer,
					CONVERT(VARCHAR, sa.ScheduledStartTime, 120),
					CONVERT(VARCHAR, sa.ScheduledEndTime, 120),
					sa.ScheduledActivityCode,
					sa.ObjectStatus,
					CONVERT(VARCHAR, sa.ActualStartDate, 120),
					CONVERT(VARCHAR, sa.ActualEndDate, 120),
					REPLACE(lt.Expression1, '''', ''),
					PatientInfo.PatientSerNum
				FROM 
					variansystem.dbo.Patient pt, 
					variansystem.dbo.ScheduledActivity sa, 
					variansystem.dbo.ActivityInstance ai, 
					variansystem.dbo.Activity act, 
					variansystem.dbo.LookupTable lt,
					PatientInfo
				WHERE 
					sa.ActivityInstanceSer 		= ai.ActivityInstanceSer 
				AND ai.ActivitySer 			    = act.ActivitySer 
				AND act.ActivityCode 		    = lt.LookupValue 
				AND pt.PatientSer 				= sa.PatientSer 
				AND LEFT(LTRIM(pt.SSN), 12)		= PatientInfo.SSN
				AND (

			";
		my $numOfExpressions = keys %{$expressionHash{$sourceDBSer}}; 
        my $counter = 0;
		# loop through each transfer date
        foreach my $lastTransferDate (keys %{$expressionHash{$sourceDBSer}}) {

            # concatenate query
    		$apptInfo_sql .= "
				(REPLACE(lt.Expression1, '''', '')    	IN ($expressionHash{$sourceDBSer}{$lastTransferDate})
	        	AND sa.HstryDateTime	 				> (SELECT CASE WHEN '$lastTransferDate' > PatientInfo.LastTransfer THEN PatientInfo.LastTransfer ELSE '$lastTransferDate' END) )
    		";
    		$counter++;
    		# concat "UNION" until we've reached the last query
    		if ($counter < $numOfExpressions) {
    			$apptInfo_sql .= "OR";
    		}
			# close bracket at end
			else {
				$apptInfo_sql .= ")";
			}
    	}
    	#$apptInfo_sql .= ")";

		#print "$apptInfo_sql\n";

    	# prepare query
	    my $query = $sourceDatabase->prepare($apptInfo_sql)
		    or die "Could not prepare query: " . $sourceDatabase->errstr;

	    # execute query
		$query->execute()
    		or die "Could not execute query: " . $query->errstr;

        my $data = $query->fetchall_arrayref();
		foreach my $row (@$data) {

    		my $appointment = new Appointment(); # new appointment object 
    
	        $sourceuid	    = $row->[0];
		    $startdatetime	= $row->[1]; 
    	    $enddatetime	= $row->[2];
            $status         = $row->[3];
            $state          = $row->[4];
            $actualstartdate    = $row->[5];
            $actualenddate      = $row->[6];
            $expressionname 	= $row->[7];
            $patientSer 		= $row->[8];

            $priorityser    = Priority::getClosestPriority($patientSer, $startdatetime);
            $diagnosisser   = Diagnosis::getClosestDiagnosis($patientSer, $startdatetime);

    		$appointment->setApptPatientSer($patientSer);
	    	$appointment->setApptSourceUID($sourceuid);
            $appointment->setApptSourceDatabaseSer($sourceDBSer);
        	$appointment->setApptAliasExpressionSer($expressionDict{$expressionname});
    	    $appointment->setApptStartDateTime($startdatetime); 
	    	$appointment->setApptEndDateTime($enddatetime); 
            $appointment->setApptPrioritySer($priorityser);
            $appointment->setApptDiagnosisSer($diagnosisser);
	        $appointment->setApptStatus($status);
		    $appointment->setApptState($state);
    	    $appointment->setApptActualStartDate($actualstartdate); 
	        $appointment->setApptActualEndDate($actualenddate);
	        $appointment->setApptCronLogSer($cronLogSer);

    		push(@apptList, $appointment);
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

		        	$expressionDict{$expressionName}{$expressionDesc} = $expressionSer;

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

		my $apptInfo_sql = "
             SELECT DISTINCT
                mval.AppointmentSerNum,
                mval.ScheduledDateTime,
                mval.Status,
                mval.AppointmentCode,
                mval.ResourceDescription,
                pi.PatientSerNum
            FROM
                MediVisitAppointmentList mval,
                Patient pt
            JOIN ($patientInfo_sql) pi
            ON 	LEFT(LTRIM(pt.SSN), 12)  = pi.SSN
            WHERE
                mval.PatientSerNum      = pt.PatientSerNum
            AND (
        ";

        my $numOfExpressions = keys %{$expressionHash{$sourceDBSer}}; 
        my $counter = 0;
		# loop through each transfer date
        foreach my $lastTransferDate (keys %{$expressionHash{$sourceDBSer}}) {

            # concatenate query
    		$apptInfo_sql .= "
    			((mval.AppointmentCode, mval.ResourceDescription) IN ($expressionHash{$sourceDBSer}{$lastTransferDate})
    			AND mval.LastUpdated	> (SELECT IF ('$lastTransferDate' > pi.LastTransfer, pi.LastTransfer, '$lastTransferDate')))
			";
    		$counter++;
    		# concat "UNION" until we've reached the last query
    		if ($counter < $numOfExpressions) {
    			$apptInfo_sql .= "OR";
    		}
			# close bracket at end
			else {
				$apptInfo_sql .= ")";
			}
    	}
    	#print "$apptInfo_sql\n";		    
        my $query = $sourceDatabase->prepare($apptInfo_sql)
		    or die "Could not prepare query: " . $sourceDatabase->errstr;

	    # execute query
		$query->execute()
    		or die "Could not execute query: " . $query->errstr;

        my $data = $query->fetchall_arrayref();
		foreach my $row (@$data) {

            my $appointment = new Appointment(); # new appointment object

            $sourceuid      = $row->[0];
            $startdatetime  = $row->[1];
            $enddatetime    = $row->[1];
            $status         = $row->[2];
            $expressionname = $row->[3];
            $expressiondesc = $row->[4];
            $patientSer 	= $row->[5];

            $appointment->setApptPatientSer($patientSer);
            $appointment->setApptSourceUID($sourceuid);
            $appointment->setApptSourceDatabaseSer($sourceDBSer);
        	$appointment->setApptAliasExpressionSer($expressionDict{$expressionname}{$expressiondesc});
            $appointment->setApptStartDateTime($startdatetime);
            $appointment->setApptEndDateTime($enddatetime);
            $appointment->setApptStatus($status);
            $appointment->setApptState('Active'); # Set default for WRM
	        $appointment->setApptCronLogSer($cronLogSer);

            push(@apptList, $appointment);
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

	return @apptList;
}

#======================================================================================
# Subroutine to get patient appointment(s) from our db given a patient serial and date
#======================================================================================
sub getPatientsAppointmentsFromDateInOurDB
{
    my ($patientSer, $dateOfInterest, $dayInterval) = @_; # args

    my @appointments = (); 

    my $select_sql = "
        SELECT DISTINCT
            ap.AppointmentSerNum,
            ap.AliasExpressionSerNum,
            ap.AppointmentAriaSer,
            ap.ScheduledStartTime,
            ap.ScheduledEndTime,
            ap.DiagnosisSerNum,
            ap.SourceDatabaseSerNum
        FROM 
            Appointment ap
        WHERE
            ap.PatientSerNum            = '$patientSer'
        AND ap.ScheduledStartTime       <= '$dateOfInterest 23:59:59'
        AND ap.ScheduledStartTime       >= DATE_SUB('$dateOfInterest 00:00:00', INTERVAL $dayInterval DAY) 
        AND ap.State 					= 'Active'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

        my $ser             = $data[0];
        my $expressionser   = $data[1];
        my $sourceUID       = $data[2];
        my $scheduledST     = $data[3];
        my $scheduledET     = $data[4];
        my $diagnosisser    = $data[5];
        my $sourceDBSer     = $data[6];

        $appointment = new Appointment();

        $appointment->setApptSer($ser);
        $appointment->setApptAliasExpressionSer($expressionser);
        $appointment->setApptSourceUID($sourceUID);
        $appointment->setApptPatientSer($patientSer);
        $appointment->setApptStartDateTime($scheduledST);
        $appointment->setApptEndDateTime($scheduledET);
        $appointment->setApptDiagnosisSer($diagnosisser);
        $appointment->setApptSourceDatabaseSer($sourceDBSer);

        push(@appointments, $appointment);

    }

    return @appointments;

}

#======================================================================================
# Subroutine to get patient's today's appointment(s) from our db given a patient serial and date
#======================================================================================
sub getTodaysPatientsAppointmentsFromOurDB
{
    my ($patientSer) = @_; # args

    my @appointments = (); 

    my $select_sql = "
        SELECT DISTINCT
            ap.AppointmentSerNum,
            ap.AliasExpressionSerNum,
            ap.AppointmentAriaSer,
            ap.ScheduledStartTime,
            ap.ScheduledEndTime,
            ap.DiagnosisSerNum,
            ap.SourceDatabaseSerNum,
            sa.Name,
            ap.Checkin
        FROM 
            Appointment ap,
            StatusAlias sa
        WHERE
            ap.PatientSerNum            = '$patientSer'
        AND DATE(ap.ScheduledStartTime) = CURDATE()
        AND ap.State 					= 'Active'
        AND ap.Status 					= sa.Expression
        AND ap.SourceDatabaseSerNum		= sa.SourceDatabaseSerNum
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

        my $ser             = $data[0];
        my $expressionser   = $data[1];
        my $sourceUID       = $data[2];
        my $scheduledST     = $data[3];
        my $scheduledET     = $data[4];
        my $diagnosisser    = $data[5];
        my $sourceDBSer     = $data[6];
        my $status 			= $data[7];
        my $checkin 		= $data[8];

        $appointment = new Appointment();

        $appointment->setApptSer($ser);
        $appointment->setApptAliasExpressionSer($expressionser);
        $appointment->setApptSourceUID($sourceUID);
        $appointment->setApptPatientSer($patientSer);
        $appointment->setApptStartDateTime($scheduledST);
        $appointment->setApptEndDateTime($scheduledET);
        $appointment->setApptDiagnosisSer($diagnosisser);
        $appointment->setApptSourceDatabaseSer($sourceDBSer);
        $appointment->setApptStatus($status);
        $appointment->setApptCheckin($checkin);

        push(@appointments, $appointment);

    }

    return @appointments;

}

#======================================================================================
# Subroutine to get all patient's appointment(s) up until tomorrow
#======================================================================================
sub getAllPatientsAppointmentsFromOurDB
{
    my ($patientSer) = @_; # args 

    my @appointments = (); # initialize a list 

    my $select_sql = "
        SELECT DISTINCT
            ap.AppointmentSerNum,
            ap.AliasExpressionSerNum,
            ap.AppointmentAriaSer,
            ap.ScheduledStartTime,
            ap.ScheduledEndTime,
            ap.DiagnosisSerNum,
            ap.SourceDatabaseSerNum
        FROM
            Appointment ap
        WHERE
            ap.PatientSerNum            = '$patientSer'
        AND ap.ScheduledStartTime       <= DATE_ADD(NOW(), INTERVAL 1 DAY)
        AND ap.State 					= 'Active'
    ";
    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

        my $ser             = $data[0];
        my $expressionser   = $data[1];
        my $sourceUID       = $data[2];
        my $scheduledST     = $data[3];
        my $scheduledET     = $data[4];
        my $diagnosisser    = $data[5];
        my $sourcedbser     = $data[6];

        $appointment = new Appointment();

        $appointment->setApptSer($ser);
        $appointment->setApptAliasExpressionSer($expressionser);
        $appointment->setApptSourceUID($sourceUID);
        $appointment->setApptPatientSer($patientSer);
        $appointment->setApptStartDateTime($scheduledST);
        $appointment->setApptEndDateTime($scheduledET);
        $appointment->setApptDiagnosisSer($diagnosisser);
        $appointment->setApptSourceDatabaseSer($sourcedbser);

        push(@appointments, $appointment);

    }

    return @appointments;

}

#======================================================================================
# Subroutine to get appointment info from the source db given a serial
#======================================================================================
sub getApptInfoFromSourceDB 
{

	my ($appointment) = @_; # Appt object
	my $apptSourceUID   = $appointment->getApptSourceUID();
	my $aliasSer	    = $appointment->getApptAliasSer();
    my $apptSourceDBSer = $appointment->getApptSourceDatabaseSer();
    my $patientSer      = $appointment->getApptPatientSer();

	# get the list of expressions for this alias (they will be of appointment type)
	my $alias = new Alias(); # initialize object
	$alias->setAliasSer($aliasSer);
	my @expressions = Alias::getAliasExpressionsFromOurDB($alias);

	# when we retrieve query results
	my ($expressionname, $startdatetime, $enddatetime);
	my ($priorityser, $diagnosisser);
    my ($status, $state, $actualstartdate, $actualenddate);

    ######################################
    # ARIA
    ######################################
    if ($apptSourceDBSer eq 1) {
                
        my $sourceDatabase	= Database::connectToSourceDatabase($apptSourceDBSer);

    	my $apptInfo_sql = "
	    	SELECT DISTINCT
		    	lt.Expression1,
			    CONVERT(VARCHAR, sa.ScheduledStartTime, 120),
    			CONVERT(VARCHAR, sa.ScheduledEndTime, 120),
                sa.ScheduledActivityCode,
                sa.ObjectStatus,
                CONVERT(VARCHAR, sa.ActualStartDate, 120),
                CONVERT(VARCHAR, sa.ActualEndDate, 120)
	    	FROM 
		    	variansystem.dbo.Patient pt, 
			    variansystem.dbo.ScheduledActivity sa, 
    			variansystem.dbo.ActivityInstance ai, 
	    		variansystem.dbo.Activity act, 
				variansystem.dbo.LookupTable lt
	    	WHERE 
		        sa.ActivityInstanceSer 	    = ai.ActivityInstanceSer 
    		AND ai.ActivitySer 			    = act.ActivitySer 
	    	AND act.ActivityCode 			= lt.LookupValue 
		    AND pt.PatientSer 				= sa.PatientSer 
    		AND sa.ScheduledActivitySer     = '$apptSourceUID'  
	    
    	";
    	#print "$apptInfo_sql\n";
	    # prepare query
    	my $query = $sourceDatabase->prepare($apptInfo_sql)
	    	or die "Could not prepare query: " . $sourceDatabase->errstr;

    	# execute query
	    $query->execute()
		    or die "Could not execute query: " . $query->errstr;

    	while (my @data = $query->fetchrow_array()) {

    		$expressionname	= $data[0];
	    	$startdatetime	= $data[1]; 
		    $enddatetime	= $data[2];
            $status         = $data[3];
            $state          = $data[4];
            $actualstartdate    = $data[5];
            $actualenddate      = $data[6];

    		$priorityser	= Priority::getClosestPriority($patientSer, $startdatetime);
	    	$diagnosisser	= Diagnosis::getClosestDiagnosis($patientSer, $startdatetime);
				
    		# Search through alias expression list to find associated
	    	# expression serial number (in our DB)
    		my $expressionSer;
	    	foreach my $checkExpression (@expressions) {
    
	    		if ($checkExpression->{_name} eq $expressionname) { # match
		    		$expressionSer = $checkExpression->{_ser};
			    	last; # break out of loop
    			}
	    	}

    		$appointment->setApptPatientSer($patientSer);
	    	$appointment->setApptAliasExpressionSer($expressionSer);
		    $appointment->setApptStartDateTime($startdatetime); 
    		$appointment->setApptEndDateTime($enddatetime); 
	    	$appointment->setApptPrioritySer($priorityser);
		    $appointment->setApptDiagnosisSer($diagnosisser);
    		$appointment->setApptState($state);
	    	$appointment->setApptStatus($status);
		    $appointment->setApptActualStartDate($actualstartdate); 
    		$appointment->setApptActualEndDate($actualenddate); 
    	}

        $sourceDatabase->disconnect();

    }

    ######################################
    # MediVisit
    ######################################
    if ($apptSourceDBSer eq 2) {

        my $sourceDatabase = Database::connectToSourceDatabase($apptSourceDBSer);
        my $apptInfo_sql = "
            SELECT DISTINCT
                mval.AppointmentCode,
                mval.ScheduledDateTime
            FROM
                MediVisitAppointmentList mval
            WHERE
                mval.AppointmentSerNum  = '$apptSourceUID'
        ";
                  		    
        my $query = $sourceDatabase->prepare($apptInfo_sql)
	    	or die "Could not prepare query: " . $sourceDatabase->errstr;

        # execute query
    	$query->execute()
	        or die "Could not execute query: " . $query->errstr;

        my $data = $query->fetchall_arrayref();
    	foreach my $row (@$data) {

            $expressionname = $row->[0];
            $startdatetime  = $row->[1];
            $enddatetime    = $row->[1];

            # Search through alias expression list to find associated
        	# expression serial number (in our DB)
	        my $expressionser;
	        foreach my $checkExpression (@expressions) {
    
    	    	if ($checkExpression->{_name} eq $expressionname) { # match
        
					$expressionser = $checkExpression->{_ser};
	        		last; # break out of loop
				}
        	}

            $appointment->setApptAliasExpressionSer($expressionser);
            $appointment->setApptStartDateTime($startdatetime);
            $appointment->setApptEndDateTime($enddatetime);
                
        }

        $sourceDatabase->disconnect();
    }

    ######################################
    # MOSAIQ
    ######################################
    if ($apptSourceDBSer eq 3) {

    	my $sourceDatabase = Database::connectToSourceDatabase($apptSourceDBSer);
        my $apptInfo_sql = "SELECT 'QUERY_HERE'";

        my $query = $sourceDatabase->prepare($apptInfo_sql)
	    	or die "Could not prepare query: " . $sourceDatabase->errstr;

        # execute query
    	$query->execute()
	        or die "Could not execute query: " . $query->errstr;

        my $data = $query->fetchall_arrayref();
    	foreach my $row (@$data) {

    		# use setters to set appropriate appointment information from query

    	}

    	$sourceDatabase->disconnect();
    }

	return $appointment;
}

#======================================================================================
# Subroutine to check if our appointment exists in our MySQL db
#	@return: appt object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
	my ($appointment) = @_; # our appt object	
	my $sourceUID   = $appointment->getApptSourceUID(); # retrieve appt source uid
    my $sourceDBSer = $appointment->getApptSourceDatabaseSer(); 

	my $ApptSourceUIDInDB = 0; # false by default. Will be true if appointment exists
	my $ExistingAppt = (); # data to be entered if appt exists

	# Other appt variables, if appt exists
	my ($ser, $patientser, $aliasexpressionser, $startdatetime, $enddatetime);
    my ($priorityser, $diagnosisser, $sourcedbser);
    my ($status, $state, $actualstartdate, $actualenddate, $cronlogser);

	my $inDB_sql = "
		SELECT DISTINCT
			Appointment.AppointmentAriaSer,
			Appointment.AliasExpressionSerNum,
			Appointment.ScheduledStartTime,
			Appointment.ScheduledEndTime,
			Appointment.AppointmentSerNum,
			Appointment.PatientSerNum,
            Appointment.PrioritySerNum,
            Appointment.DiagnosisSerNum,
            Appointment.SourceDatabaseSerNum,
            Appointment.Status,
            Appointment.State,
            Appointment.ActualStartDate,
            Appointment.ActualEndDate,
            Appointment.CronLogSerNum
		FROM
			Appointment
		WHERE
			Appointment.AppointmentAriaSer      = '$sourceUID'
        AND Appointment.SourceDatabaseSerNum    = '$sourceDBSer'
	";

	# prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

		$ApptSourceUIDInDB	= $data[0];
		$aliasexpressionser	= $data[1];
		$startdatetime		= $data[2];
		$enddatetime		= $data[3];
		$ser			    = $data[4];
		$patientser		    = $data[5];
        $priorityser        = $data[6];
        $diagnosisser       = $data[7];
        $sourcedbser        = $data[8];
        $status             = $data[9];
        $state              = $data[10];
        $actualstartdate    = $data[11];
        $actualenddate      = $data[12];
        $cronlogser 		= $data[13];
	}

	if ($ApptSourceUIDInDB) {

		$ExistingAppt = new Appointment(); # initialize appointment object

		$ExistingAppt->setApptSourceUID($ApptSourceUIDInDB); # set the Appt aria serial
		$ExistingAppt->setApptAliasExpressionSer($aliasexpressionser); # set expression serial
		$ExistingAppt->setApptStartDateTime($startdatetime); # set the appt start datetime
		$ExistingAppt->setApptEndDateTime($enddatetime); # set the appt end datetime
		$ExistingAppt->setApptSer($ser); # set the serial
		$ExistingAppt->setApptPatientSer($patientser); 
        $ExistingAppt->setApptPrioritySer($priorityser);
        $ExistingAppt->setApptDiagnosisSer($diagnosisser);
        $ExistingAppt->setApptSourceDatabaseSer($sourcedbser);
		$ExistingAppt->setApptStatus($status); # set the appt status
		$ExistingAppt->setApptState($state); # set the appt state
		$ExistingAppt->setApptActualStartDate($actualstartdate); # set the appt start datetime
		$ExistingAppt->setApptActualEndDate($actualenddate); # set the appt end datetime
		$ExistingAppt->setApptCronLogSer($cronlogser); # set the cron log serial

		return $ExistingAppt; # this is true (ie. appt exists, return object)
	}
	
	else {return $ExistingAppt;} # this is false (ie. appt DNE, return empty)
}

#======================================================================================
# Subroutine to insert our appointment info in our database
#======================================================================================
sub insertApptIntoOurDB
{
	my ($appointment) = @_; # our appointment object 
	
	my $patientser		    = $appointment->getApptPatientSer();
	my $sourceuid           = $appointment->getApptSourceUID();
    my $sourcedbser         = $appointment->getApptSourceDatabaseSer();
	my $aliasexpressionser	= $appointment->getApptAliasExpressionSer();
	my $startdatetime	    = $appointment->getApptStartDateTime();
	my $enddatetime		    = $appointment->getApptEndDateTime();
    my $priorityser         = $appointment->getApptPrioritySer();
    my $diagnosisser        = $appointment->getApptDiagnosisSer();
	my $status		        = $appointment->getApptStatus();
	my $state		        = $appointment->getApptState();
	my $actualstartdate	    = $appointment->getApptActualStartDate();
	my $actualenddate		= $appointment->getApptActualEndDate();
	my $cronlogser 			= $appointment->getApptCronLogSer();

	my $insert_sql = "
		INSERT INTO 
			Appointment (
				AppointmentSerNum,
				PatientSerNum,
				CronLogSerNum,
                SourceDatabaseSerNum,
				AppointmentAriaSer,
				AliasExpressionSerNum,
                Status,
                State,
				ScheduledStartTime,		
				ScheduledEndTime,
                ActualStartDate,
                ActualEndDate,
                PrioritySerNum,
                DiagnosisSerNum,
                DateAdded,
				LastUpdated
			)
		VALUES (
			NULL,
			'$patientser',
			'$cronlogser',
            '$sourcedbser',
			'$sourceuid',
			'$aliasexpressionser',
            '$status',
            '$state',
			'$startdatetime',
			'$enddatetime',
            '$actualstartdate',
            '$actualenddate',
            '$priorityser',
            '$diagnosisser',
            NOW(),
			NULL
		)
	";

	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the ApptSer
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the Serial in our appointment object
	$appointment->setApptSer($ser);
	
	return $appointment;
}

#======================================================================================
# Subroutine to update our database with the appointment's updated info
#======================================================================================
sub updateDatabase
{
	my ($appointment) = @_; # our appt object to update

	my $sourceuid	        = $appointment->getApptSourceUID();
    my $sourcedbser         = $appointment->getApptSourceDatabaseSer();
	my $aliasexpressionser	= $appointment->getApptAliasExpressionSer();
	my $startdatetime	    = $appointment->getApptStartDateTime();
	my $enddatetime		    = $appointment->getApptEndDateTime();
    my $priorityser         = $appointment->getApptPrioritySer();
    my $diagnosisser        = $appointment->getApptDiagnosisSer();
	my $status				= $appointment->getApptStatus();
	my $state				= $appointment->getApptState();
	my $actualstartdate		= $appointment->getApptActualStartDate();
	my $actualenddate		= $appointment->getApptActualEndDate();
	my $cronlogser 			= $appointment->getApptCronLogSer();

	my $update_sql = "

		UPDATE
			Appointment
		SET
			AliasExpressionSerNum	= '$aliasexpressionser',
            Status                  = '$status',
            State                   = '$state',
			ScheduledStartTime	    = '$startdatetime',
			ScheduledEndTime	    = '$enddatetime',
            ActualStartDate         = '$actualstartdate',
            ActualEndDate           = '$actualenddate',
            PrioritySerNum          = '$priorityser',
            DiagnosisSerNum         = '$diagnosisser',
            ReadStatus              = 0,
            CronLogSerNum 			= '$cronlogser'
		WHERE
			AppointmentAriaSer	    = '$sourceuid'
        AND SourceDatabaseSerNum    = '$sourcedbser'
		";

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
}

#======================================================================================
# Subroutine to compare two appt objects. If different, use setter functions
# to update appt object.
#======================================================================================
sub compareWith
{
	my ($SuspectAppt, $OriginalAppt) = @_; # our two appt objects from arguments
	my $UpdatedAppt = dclone($OriginalAppt); 

	# retrieve parameters
	# Suspect Appointment...
	my $SAliasExpressionSer	= $SuspectAppt->getApptAliasExpressionSer();
	my $SStartDateTime	    = $SuspectAppt->getApptStartDateTime();
	my $SEndDateTime	    = $SuspectAppt->getApptEndDateTime();
    my $SPrioritySer        = $SuspectAppt->getApptPrioritySer();
    my $SDiagnosisSer       = $SuspectAppt->getApptDiagnosisSer();
	my $SStatus		        = $SuspectAppt->getApptStatus();
	my $SState		        = $SuspectAppt->getApptState();
	my $SActualStartDate	= $SuspectAppt->getApptActualStartDate();
    my $SActualEndDate	    = $SuspectAppt->getApptActualEndDate();
    my $SCronLogSer		    = $SuspectAppt->getApptCronLogSer();

	# Original Appointment...
	my $OAliasExpressionSer	= $OriginalAppt->getApptAliasExpressionSer();
	my $OStartDateTime	    = $OriginalAppt->getApptStartDateTime();
	my $OEndDateTime	    = $OriginalAppt->getApptEndDateTime();
    my $OPrioritySer        = $OriginalAppt->getApptPrioritySer();
    my $ODiagnosisSer       = $OriginalAppt->getApptDiagnosisSer();
	my $OStatus		        = $OriginalAppt->getApptStatus();
	my $OState		        = $OriginalAppt->getApptState();
	my $OActualStartDate	= $OriginalAppt->getApptActualStartDate();
	my $OActualEndDate	    = $OriginalAppt->getApptActualEndDate();
	my $OCronLogSer		    = $OriginalAppt->getApptCronLogSer();

	# go through each parameter
	
	if ($SAliasExpressionSer ne $OAliasExpressionSer) {
		print "Appointment Alias Expression Serial has changed from '$OAliasExpressionSer' to '$SAliasExpressionSer'\n";
		my $updatedAESer = $UpdatedAppt->setApptAliasExpressionSer($SAliasExpressionSer); # update serial
		print "Will update database entry to '$updatedAESer'.\n";
	}
	if ($SStartDateTime ne $OStartDateTime) {
		print "Appointment Scheduled Start DateTime has change from '$OStartDateTime' to '$SStartDateTime'\n";
		my $updatedSDT = $UpdatedAppt->setApptStartDateTime($SStartDateTime); # update start datetime
		print "Will update database entry to '$updatedSDT'.\n";

		# Section to notify patient on appointment change
		$SStartDateTimeForm = Time::Piece->strptime($SStartDateTime, "%Y-%m-%d %H:%M:%S");
		$OStartDateTimeForm = Time::Piece->strptime($OStartDateTime, "%Y-%m-%d %H:%M:%S");
		# if difference is greater than an hour (in seconds)
		if ( abs($SStartDateTimeForm - $OStartDateTimeForm) >= 3600 ) {
			print "Sending push notification on appointment time change\n";

			# parser
			my $strp = DateTime::Format::Strptime->new(
				pattern => "%Y-%m-%d %H:%M:%S",
		        time_zone => 'America/New_York'
			);
			# formatter
			my $timestamp = DateTime::Format::Strptime->new(
		        pattern   => '%s',
		        time_zone => 'America/New_York'
		    );
			$SStartDateTime = $timestamp->format_datetime($strp->parse_datetime($SStartDateTime)); # convert to timestamp
			$OStartDateTime = $timestamp->format_datetime($strp->parse_datetime($OStartDateTime)); # convert to timestamp

			$patientSer = $OriginalAppt->getApptPatientSer();
			$appointmentSer = $OriginalAppt->getApptSer();
			$langEN = Date::Language->new('English'); # for english dates
			$langFR = Date::Language->new('French'); # for french dates
			# create a hash for string replacement in notification message
			# see for datetime formats: http://search.cpan.org/~gbarr/TimeDate-2.30/lib/Date/Format.pm#strftime
			%replacementMap = (
				"\\\$oldAppointmentDateEN"	=> $langEN->time2str("%A, %B %e, %Y", $OStartDateTime), 
				"\\\$oldAppointmentTimeEN"	=> trim($langEN->time2str("%l:%M %p", $OStartDateTime)), # trim leading space in time
				"\\\$newAppointmentDateEN"	=> $langEN->time2str("%A, %B %e, %Y", $SStartDateTime),
				"\\\$newAppointmentTimeEN"	=> trim($langEN->time2str("%l:%M %p", $SStartDateTime)), # trim leading space in time
				"\\\$oldAppointmentDateFR"	=> $langFR->time2str("%A %e %B %Y", $OStartDateTime),
				"\\\$oldAppointmentTimeFR"	=> $langFR->time2str("%R", $OStartDateTime),
				"\\\$newAppointmentDateFR"	=> $langFR->time2str("%A %e %B %Y", $SStartDateTime),
				"\\\$newAppointmentTimeFR"	=> $langFR->time2str("%R", $SStartDateTime)
			);
            PushNotification::sendPushNotification($patientSer, $appointmentSer, 'AppointmentTimeChange', %replacementMap);

		}

	}
	if ($SEndDateTime ne $OEndDateTime) {
		print "Appointment Scheduled End DateTime has changed from '$OEndDateTime' to '$SEndDateTime'\n";
		my $updatedEDT = $UpdatedAppt->setApptEndDateTime($SEndDateTime); # update end datetime
		print "Will update database entry to '$updatedEDT'.\n";
	}
    if ($SPrioritySer ne $OPrioritySer) {
		print "Appointment Priority has changed from '$OPrioritySer' to '$SPrioritySer'\n";
		my $updatedPrioritySer = $UpdatedAppt->setApptPrioritySer($SPrioritySer); # update 
		print "Will update database entry to '$updatedPrioritySer'.\n";
	}
	if ($SDiagnosisSer ne $ODiagnosisSer) {
		print "Appointment Diagnosis has changed from '$ODiagnosisSer' to '$SDiagnosisSer'\n";
		my $updatedDiagnosisSer = $UpdatedAppt->setApptDiagnosisSer($SDiagnosisSer); # update 
		print "Will update database entry to '$updatedDiagnosisSer'.\n";
	}
	if ($SStatus ne $OStatus) {
		print "Appointment Status has changed from '$OStatus' to '$SStatus'\n";
		my $updatedStatus = $UpdatedAppt->setApptStatus($SStatus); # update status
		print "Will update database entry to '$updatedStatus'.\n";

		# Section to notify patient of cancelled appointment
		# new status is cancelled and new state still active
		if (index($SStatus, 'Cancelled') != -1 and index($SState, 'Active') != -1) {
			print "Sending push notification on appointment cancellation\n";
			# parser
			my $strp = DateTime::Format::Strptime->new(
				pattern => "%Y-%m-%d %H:%M:%S",
		        time_zone => 'America/New_York'
			);
			# formatter
			my $timestamp = DateTime::Format::Strptime->new(
		        pattern   => '%s',
		        time_zone => 'America/New_York'
		    );
			$SStartDateTime = $timestamp->format_datetime($strp->parse_datetime($SStartDateTime)); # convert to timestamp

			$patientSer = $OriginalAppt->getApptPatientSer();
			$appointmentSer = $OriginalAppt->getApptSer();
			$langEN = Date::Language->new('English'); # for english dates
			$langFR = Date::Language->new('French'); # for french dates
			%replacementMap = (
				"\\\$appointmentDateEN"	=> $langEN->time2str("%A, %B %e, %Y", $SStartDateTime), 
				"\\\$appointmentTimeEN"	=> trim($langEN->time2str("%l:%M %p", $SStartDateTime)), # trim leading space in time
				"\\\$appointmentDateFR"	=> $langFR->time2str("%A %e %B %Y", $SStartDateTime),
				"\\\$appointmentTimeFR"	=> $langFR->time2str("%R", $SStartDateTime)
			);
            PushNotification::sendPushNotification($patientSer, $appointmentSer, 'AppointmentCancelled', %replacementMap);
		}
	}
    if ($SState ne $OState) {
		print "Appointment State has changed from '$OState' to '$SState'\n";
		my $updatedState = $UpdatedAppt->setApptState($SState); # update state
		print "Will update database entry to '$updatedState'.\n";
	}
	if ($SActualStartDate ne $OActualStartDate) {
		print "Appointment Actual Scheduled Start Date has change from '$OActualStartDate' to '$SActualStartDate'\n";
		my $updatedSDT = $UpdatedAppt->setApptActualStartDate($SActualStartDate); # update start datetime
		print "Will update database entry to '$updatedSDT'.\n";
	}
	if ($SActualEndDate ne $OActualEndDate) {
		print "Appointment Actual Scheduled End Date has changed from '$OActualEndDate' to '$SActualEndDate'\n";
		my $updatedEDT = $UpdatedAppt->setApptActualEndDate($SActualEndDate); # update end datetime
		print "Will update database entry to '$updatedEDT'.\n";
	}
	 if ($SCronLogSer ne $OCronLogSer) {
		print "Appointment Cron Log Serial has changed from '$OCronLogSer' to '$SCronLogSer'\n";
		my $updatedCronLogSer = $UpdatedAppt->setApptCronLogSer($SCronLogSer); # update serial
		print "Will update database entry to '$updatedCronLogSer'.\n";
	}


	return $UpdatedAppt;
}

#======================================================================================
# Subroutine to reassign our appointment ser in ARIA to an appointment serial in MySQL. 
# In the process, insert appointment into our database if it DNE
#======================================================================================
sub reassignAppointment
{
	my ($sourceUID, $sourceDBSer, $aliasSer, $patientSer) = @_; # appt ser from arguments
	
	my $Appointment = new Appointment(); # initialize appt object

	$Appointment->setApptSourceUID($sourceUID); # assign our uid
    $Appointment->setApptSourceDatabaseSer($sourceDBSer);
    $Appointment->setApptAliasSer($aliasSer);
    $Appointment->setApptPatientSer($patientSer);

	# check if our appointment exists in our database
	my $ApptExists = $Appointment->inOurDatabase();

	if ($ApptExists) {

		my $ExistingAppt = dclone($ApptExists); # reassign variable

		my $apptSerNum = $ExistingAppt->getApptSer(); # get serial

		return $apptSerNum;
	}
	else { # appointment DNE

		# get appt info from source database (ARIA)
		$Appointment = $Appointment->getApptInfoFromSourceDB();

		# insert appointment into our database
		$Appointment = $Appointment->insertApptIntoOurDB();

		# get serial
		my $apptSerNum = $Appointment->getApptSer();

		return $apptSerNum;
	}
}
# To exit/return always true (for the module itself)
1;	




