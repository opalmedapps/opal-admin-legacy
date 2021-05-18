#!/usr/bin/perl

#---------------------------------------------------------------------------------
# K.Agnew 2021 Cron Modularity Refactor ++
#---------------------------------------------------------------------------------
# Control script for the sending of notifications to patients. This script has its
# own dedicated cron job.
# We use our custom Perl Modules to help us with getting information and
# setting them into the appropriate place.


#-----------------------------------------------------------------------
# Packages/Modules
#-----------------------------------------------------------------------
use Time::Piece;
use POSIX;
use Storable qw(dclone);
use File::Basename;
use File::Spec;
use JSON;
use MIME::Lite;
use Data::Dumper;
use Net::Address::IP::Local;
use Cwd 'abs_path';

use lib dirname($0) . '/../modules'; # specify where our modules are -- $0 = this script's location
use Configs;
use Database;
use Patient;
use Cron;

#====================================================================================
# Helper Subroutine to log json content to a file
#====================================================================================
sub writeToLogFile
{
	my ($monitor_log, $contents, $writeOption) = @_; # args
	open my $file_handler, $writeOption, $monitor_log;
	print $file_handler $contents;
	close $file_handler;

	return;
}


# Get the current time (for last-updates/logs)
my $start_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));
print "--- Start --- ", $start_datetime, "\n";

# Log that the script is initialized in the cronlog
my $cronLogSer = Cron::setCronLog("Started", $start_datetime);


#=========================================================================================
# Retrieve all patients that are marked for update
#=========================================================================================
print "\n--- Start getPatientsMarkedForUpdate: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@registeredPatients = Patient::getPatientsMarkedForUpdate($cronLogSer);
print "--- End getPatientsMarkedForUpdate: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got patient list\n" if $verbose;

print "--- Start Loop over each patient: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
#=========================================================================================
# Loop over each patient.
#=========================================================================================
foreach my $Patient (@registeredPatients) {

    # retrieve information from source databases
    my @sourcePatients = $Patient->getPatientInfoFromSourceDBs();

    foreach my $SourcePatient (@sourcePatients) {

        # check if patient exists in our database (it should by default)
        my $patientExists = $SourcePatient->inOurDatabase();

        if ($patientExists) { # patient exists

            my $ExistingPatient = dclone($patientExists); # reassign variable

            # compare our source patient with the existing patient
            # update is done on the existing patient
            my ($UpdatedPatient, $change) = $SourcePatient->compareWith($ExistingPatient);

			# if there was an actual change in comparison
			if ($change) {
				# update the database
				$UpdatedPatient->updateDatabase();
			}

            # push to patient list
            push(@patientList, $UpdatedPatient);

        } else { # patient DNE

    		# insert Patient into our database
	    	$SourcePatient = $SourcePatient->insertPatientIntoOurDB();

            # push to patient list
            push(@patientList, $SourcePatient);

	    }
    }
}

print "-- End Loop over each patient: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished patient list\n" if $verbose;

##########################################################################################
# @Kelly Agnew 2021-02-22 Cron Refactor
# Data pre-loading: improve cron speed by preloading the patientInfo_sql list and passing to various modules
#
##########################################################################################
print "-- Start global_patientInfo_sql pre-load: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
my $numPats = @patientList;
my $c = 0;
foreach my $Patient (@patientList) {
	my $patientSer 			= $Patient->getPatientSer();
	my $id		   			= $Patient->getPatientId(); #patient ID
	my $patientLastTransfer = $Patient->getPatientLastTransfer(); # last updated

	$global_patientInfo_sql .= "
		SELECT '$id', '$patientLastTransfer', '$patientSer'
	";

	$c++;
	if($c < $numPats ){
		$global_patientInfo_sql .= "UNION";
	}

}
print "-- End global_patientInfo_sql pre-load: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got global patientInfo list\n" if $verbose;