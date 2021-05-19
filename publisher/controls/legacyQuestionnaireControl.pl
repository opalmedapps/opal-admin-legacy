#!/usr/bin/perl

#---------------------------------------------------------------------------------
# K.Agnew 2021 Cron Modularity Refactor ++
#---------------------------------------------------------------------------------
# Control script for the sending of questionnaires to patients. This script has its
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
use PostControl;
use Alias;
use EducationalMaterialControl;
use LegacyQuestionnaire;
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
print "--- Start legacyQuestionnaireControl --- ", $start_datetime, "\n";

# Log that the script is initialized in the cronlog
my $cronLogSer = Cron::setCronLog("Started legacyQuestionnaireControl", $start_datetime);


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
#
# Publishing LEGACY QUESTIONNAIRES
#
##########################################################################################
print "\n--- Start publishLegacyQuestionnaires: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
LegacyQuestionnaire::publishLegacyQuestionnaires($cronLogSer, @patientList);
print "--- End publishTxTeamMessages: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished Legacy Questionnaires\n" if $verbose;


# Once everything is complete, we update the "last transferred" field for all controls
# Patient control
Patient::setPatientLastTransferredIntoOurDB($start_datetime);

# Alias control
Alias::setAliasLastTransferIntoOurDB($start_datetime);

# Post control
PostControl::setPostControlLastPublishedIntoOurDB($start_datetime);
# Educational material control
EducationalMaterialControl::setEduMatControlLastPublishedIntoOurDB($start_datetime);


# Log that the script is finished in the cronlog
Cron::setCronLog("Completed legacyQuestionnaireControl", $current_datetime);
print "--- Completed ---- ", $current_datetime, "\n\n";

print "Start Time [legacyQuestionnaireControl]: -->> $start_datetime\n";
print "End Time [legacyQuestionnaireControl]: -->> $current_datetime\n";

# success restart flag and counter for next run
$json_log->{'start'} = 0;
$json_log->{'run'}->{'count'} = 0;
$json_log->{'crash'}->{'count'} = 0;

writeToLogFile($monitor_log, encode_json($json_log), ">");




