#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 07-Aug-2015 ++ File: dataControl.pl
#---------------------------------------------------------------------------------
# Perl script that acts as a cronjob for populating the MySQL database with selected
# information. This script is called from the crontab.
#
# We use our custom Perl Modules to help us with getting information and
# setting them into the appropriate place.

#---------------------------------------------------------------------------------
# Yick Mo 24-Sept-2018 ++ Note: write query to a text file
#---------------------------------------------------------------------------------
# open(my $fh, '>>', 'ym.txt');
# print $fh $trInfo_sql;
# close $fh;
#---------------------------------------------------------------------------------

#---------------------------------------------------------------------------------
# TODO: Need to improve the importing of records. Should only retrieve records that
#		are required. Example, only select patient that contain modified records.
#---------------------------------------------------------------------------------

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

#-----------------------------------------------------------------------
# Monitor this script's execution
# - What this section does is
# 	1. Checks for hanging jobs
# 	2. Checks for crash reports
# Both recorded in monitor_log.json
#-----------------------------------------------------------------------

our $wsSlash = File::Spec->catfile('', '');

$execution_log = dirname($0) . '/logs/executions.log';
$monitor_log = dirname($0) . '/logs/monitor_log.json';

my $this_script = abs_path($0);
my $ip_address = Net::Address::IP::Local->public;
my $json_log;
if (-e $monitor_log) { # file exists
	{
	  local $/; # Enable 'slurp' mode
	  open my $file_handler, "<", $monitor_log;
	  $json_log = <$file_handler>;
	  close $file_handler;
	}
	$json_log = decode_json($json_log); # get json

	my $pid = $json_log->{'pid'}; # process id
	# check if process in file is running
	system("ps -p $pid > /dev/null 2>&1");

	if ($? eq 0) { # this script is already running

		my $run_count = $json_log->{'run'}->{'count'}; # get execution count
		$run_count++;
		$json_log->{'run'}->{'count'} = $run_count; # to set back in file

		if ($run_count % 10 eq 9) { # email execution report every 10 times

			unless(-e $execution_log) {
			    # Create the file if it doesn't exist
			    open my $file_handler, ">", $execution_log;
			    close $file_handler;
			}
			open(my $file_handler, "<", $execution_log);
			my @lines = reverse <$file_handler>; # read file from tail
			my @logs;
			push (@logs, "IP: <strong>$ip_address</strong> at <strong>" . $this_script . "</strong><br>"); # push first line
			my $count = 0;
			foreach $line (@lines) {
				push (@logs, "$line<br>");
				$count++;
				if ($count > 50) { last; } # only read last 50 lines
			}

			# email error
			my $mime = MIME::Lite->new(
				'From'		=> "opal\@muhc.mcgill.ca",
				'To'		=> "ackeem.berry\@gmail.com",
				'Cc'			=> "yickmo\@gmail.com",
				'Subject'	=> "Potential hanging script - Opal dataControl.pl",
				'Type'		=> 'text/html',
				'Data'		=> \@logs,
			);

			my $response = $mime->send('smtp', '172.25.123.208');

			# set last emailed date
			$json_log->{'run'}->{'last_emailed'} = strftime("%Y-%m-%d %H:%M:%S", localtime(time)); # now

		}

		# write back to file
		writeToLogFile($monitor_log, encode_json($json_log), ">");

		exit 0; # quit this script since already running on another process
	}
	else { # new process

		my $pid = $$; # get process id

		my $start_flag = $json_log->{'start'}; # get start flag from file
		if ($start_flag ne 0) { # script has been crashing

			$json_log->{'pid'} = $pid; # set pid

			my $crash_count = $json_log->{'crash'}->{'count'}; # get crash count
			$crash_count++;
			$json_log->{'crash'}->{'count'} = $crash_count; # to set crash count back to file

			if ($crash_count % 10 eq 9) { # email crash report after 10 crashes

				unless(-e $execution_log) {
				    #Create the file if it doesn't exist
				    open my $file_handler, ">", $execution_log;
				    close $file_handler;
				}
				open(my $file_handler, "<", $execution_log);
				my @lines = reverse <$file_handler>; # read file from tail
				my @logs;
				push (@logs, "IP: <strong>$ip_address</strong> at <strong>" . $this_script . "</strong><br>"); # push first line
				my $count = 0;
				foreach $line (@lines) {
					push (@logs, "$line<br>");
					$count++;
					if ($count > 50) { last; } # only get last 50 lines
				}

				# email error
				my $mime = MIME::Lite->new(
					'From'		=> "opal\@muhc.mcgill.ca",
					'To'		=> "ackeem.berry\@gmail.com",
					'Cc'			=> "yickmo\@gmail.com",
					'Subject'	=> "Script crash - Opal dataControl.pl",
					'Type'		=> 'text/html',
					'Data'		=> \@logs,
				);

				my $response = $mime->send('smtp', '172.25.123.208');

				# set last emailed date
				$json_log->{'crash'}->{'last_emailed'} = strftime("%Y-%m-%d %H:%M:%S", localtime(time)); # now
			}


		}
		else { # clean new process

			# flush/new log
			$json_log = {
				'pid'	=> $pid,
				'start'	=> 0,
				'run'	=> {
					'count'	=> 0,
					'last_emailed' 	=> 0
				},
				'crash'	=> {
					'count'	=> 0,
					'last_emailed'	=> 0
				}
			};
		}

		writeToLogFile($monitor_log, encode_json($json_log), ">");
	}
}
else { # log file DNE

	# to write new process
	my $pid = $$;
	$json_log = {
		'pid'	=> $pid,
		'start'	=> 0,
		'run'	=> {
			'count'	=> 0,
			'last_emailed' 	=> 0
		},
		'crash'	=> {
			'count'	=> 0,
			'last_emailed'	=> 0
		}
	};

	# write new file
	writeToLogFile($monitor_log, encode_json($json_log), ">");

}

# set start flag to signify script execution
$json_log->{'start'} = 1;
writeToLogFile($monitor_log, encode_json($json_log), ">");

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

#####################################################################################################
#
# START PROGRAM EXECUTION
#
#####################################################################################################

#-----------------------------------------------------------------------
# Custom Modules
#-----------------------------------------------------------------------
use lib dirname($0) . '/modules'; # specify where are modules are -- $0 = this script's location
use Configs;
use Database;
use Patient;
use Task;
use Appointment;
use ResourceAppointment;
use Document;
use Alias;
use Doctor;
use Diagnosis;
use PatientDoctor;
use TestResult;
use TestResultControl;
use Cron;
use PostControl;
use Announcement;
use TxTeamMessage;
use PatientsForPatients; # custom PatientsForPatients.pm
use EducationalMaterialControl;
use EducationalMaterial;
use Priority;
use PatientLocation;
use Questionnaire;
use LegacyQuestionnaire;

# Get the current time (for last-updates/logs)
my $start_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));
print "--- Start --- ", $start_datetime, "\n";

# Log that the script is initialized in the cronlog
my $cronLogSer = Cron::setCronLog("Started", $start_datetime);

#-----------------------------------------------------------------------
# Parameter initialization
#-----------------------------------------------------------------------
my @registeredPatients = ();
my @patientList = ();
my @PDList = ();
my @TaskList = ();
my @ApptList = ();
my @DocList = ();
my @DiagnosisList = ();
my @PriorityList = ();
my @TRList = ();
my @RAList = ();
my @PLList = ();
my @PLMHList = ();

my $global_patientInfo_sql = "";
my $verbose = 1;

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
		print "For source patient: \n";
		print Dumper($sourcePatient);
        # check if patient exists in our database (it should by default)
        my $patientExists = $SourcePatient->inOurDatabase();
		print "Patient exists?: $patientExists \n";
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
	my $patientAriaSer		= $Patient->getPatientSourceUID(); #patientAriaSer
	my $patientLastTransfer = $Patient->getPatientLastTransfer(); # last updated

	$global_patientInfo_sql .= "
		SELECT '$patientAriaSer', '$patientLastTransfer', '$patientSer'
	";

	$c++;
	if($c < $numPats ){
		$global_patientInfo_sql .= "UNION";
	}

}
print "-- End global_patientInfo_sql pre-load: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got global patientInfo list\n" if $verbose;

##########################################################################################
#
# Data Retrieval PATIENTDOCTORS - get list of patient-doctor info updated since last update
#
##########################################################################################
print "\n--- Start getPatientDoctorsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@PDList = PatientDoctor::getPatientDoctorsFromSourceDB(@patientList);
print "--- End getPatientDoctorsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got patient doctor list\n" if $verbose;

#=========================================================================================
# Loop over each PD. Various functions are done.
#=========================================================================================
print "-- Start Loop over each PD: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
foreach my $PatientDoctor (@PDList) {

	# check if patient exists in our database
	my $PDExists = $PatientDoctor->inOurDatabase();

	if ($PDExists) { # patientdoctor exists

		my $ExistingPD = dclone($PDExists); # reassign variable

		# compare our retrieve PatientDoctor with existing PD
		# update is done on the original (existing) PD
		my $UpdatedPD = $PatientDoctor->compareWith($ExistingPD);

		# after updating our PatientDoctor object, update the database
		$UpdatedPD->updateDatabase();

	} else { # patient doctor DNE

		# insert PatientDoctor into our database
		$PatientDoctor->insertPatientDoctorIntoOurDB();
	}
}
print "-- End Loop over each PD: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished patient doctor list\n" if $verbose;

##########################################################################################
#
# Data Retrieval DIAGNOSES - get list of diagnosis info updated since last update
#
##########################################################################################
print "\n--- Start getDiagnosesFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@DiagnosisList = Diagnosis::getDiagnosesFromSourceDB(\@patientList, $global_patientInfo_sql);
print "--- End getDiagnosesFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got diagnosis list\n" if $verbose;

#=========================================================================================
# Loop over each diagnosis. Various functions are done.
#=========================================================================================
print "-- Start Loop over each diagnosis: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
foreach my $Diagnosis (@DiagnosisList) {

	# check if diagnosis exists in our database
	my $DiagnosisExists = $Diagnosis->inOurDatabase();

	if ($DiagnosisExists) { # diagnosis exists

		my $ExistingDiagnosis = dclone($DiagnosisExists); # reassign variable

		# compare our retrieve Diagnosis with existing Diagnosis
		# update is done on the original (existing) Diagnosis
		my $UpdatedDiagnosis = $Diagnosis->compareWith($ExistingDiagnosis);

		# after updating our Diagnosis object, update the database
		$UpdatedDiagnosis->updateDatabase();

	} else { # diagnosis DNE

		# insert Diagnosis into our database
		$Diagnosis->insertDiagnosisIntoOurDB();
	}
}

print "-- End Loop over each diagnosis: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished diagnosis list\n" if $verbose;

##########################################################################################
#
# Data Retrieval PRIORITIES - get list of priority info updated since last update
#
##########################################################################################
print "\n--- Start getPrioritiesFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@PriorityList = Priority::getPrioritiesFromSourceDB(\@patientList, $global_patientInfo_sql);
print "--- End getPrioritiesFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got priority list\n" if $verbose;

#=========================================================================================
# Loop over each priority. Various functions are done.
#=========================================================================================
print "-- Start Loop over each priority: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
foreach my $Priority (@PriorityList) {

	# check if priority exists in our database
	my $PriorityExists = $Priority->inOurDatabase();

	if ($PriorityExists) { # priority exists

		my $ExistingPriority = dclone($PriorityExists); # reassign variable

		# compare our retrieve Priority with existing Priority
		# update is done on the original (existing) Priority
		my $UpdatedPriority = $Priority->compareWith($ExistingPriority);

		# after updating our Priority object, update the database
		$UpdatedPriority->updateDatabase();

	} else { # priority DNE

		# insert Priority into our database
		$Priority->insertPriorityIntoOurDB();
	}
}
print "-- End Loop over each priority: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished priority list\n" if $verbose;

##########################################################################################
#
# Data Retrieval TASKS - get list of patients with tasks updated since last update
#
##########################################################################################
print "\n--- Start getTasksFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@TaskList = Task::getTasksFromSourceDB($cronLogSer, \@patientList, $global_patientInfo_sql);
print "--- End getTasksFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got task list\n" if $verbose;

#=========================================================================================
# Loop over each task. Various functions are done.
#=========================================================================================
print "-- Start Loop over each task: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
foreach my $Task (@TaskList) {

	# check if task exists in our database
	my $TaskExists = $Task->inOurDatabase();

	if ($TaskExists) { # task exists

		my $ExistingTask = dclone($TaskExists); # reassign variable

		# compare our retrieve Task with existing Task
		# update is done on the original (existing) Task
		my $UpdatedTask = $Task->compareWith($ExistingTask);

		# after updating our Task object, update the database
		$UpdatedTask->updateDatabase();

	} else { # task DNE

		# insert Task into our database
		$Task = $Task->insertTaskIntoOurDB();
	}

}
print "-- End Loop over each task: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished task list\n" if $verbose;
##########################################################################################
#
# Data Retrieval APPOINTMENTS - get list of patients with appointments updated since last update
#
##########################################################################################
# print "\n--- Start getApptsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# @ApptList = Appointment::getApptsFromSourceDB($cronLogSer, \@patientList, $global_patientInfo_sql);
# print "--- End getApptsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Got appointment list\n" if $verbose;
# #=========================================================================================
# # Loop over each appointment. Various functions are done.
# #=========================================================================================
# print "-- Start Loop over each Appointment: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# foreach my $Appointment (@ApptList) {

# 	# check if appointment exists in our database
# 	my $AppointmentExists = $Appointment->inOurDatabase();

# 	if ($AppointmentExists) { # appointment exists

# 		my $ExistingAppointment = dclone($AppointmentExists); # reassign variable

# 		# compare our retrieve Appointment with existing Appointment
# 		# update is done on the original (existing) Appointment
# 		my ($UpdatedAppointment, $change)  = $Appointment->compareWith($ExistingAppointment);

# 		# after updating our Appointment object, update the database
#         # if there was an actual change in comparison
#         if ($change) {
# 			$UpdatedAppointment->updateDatabase();
# 		};

# 	} else { # appointment DNE

# 		# insert Appointment into our database
# 		$Appointment = $Appointment->insertApptIntoOurDB();
# 	}
# }
# print "-- End Loop over each task: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Finished appointment list\n" if $verbose;

##########################################################################################
#
# Data Retrieval RESOURCEAPPOINTMENT - get list of resourceappt info updated since last update
#
##########################################################################################
print "\n--- Start getResourceAppointmentsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@RAList = ResourceAppointment::getResourceAppointmentsFromSourceDB(\@patientList, $global_patientInfo_sql);
print "--- End getResourceAppointmentsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";

print "Got resource appointment list\n" if $verbose;

#=========================================================================================
# Loop over each RA. Various functions are done.
#=========================================================================================
print "-- Start Loop over each RA: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
foreach my $ResourceAppointment (@RAList) {

	# check if RA exists in our database
	my $RAExists = $ResourceAppointment->inOurDatabase();

	if ($RAExists) { # RA exists

		my $ExistingRA = dclone($RAExists); # reassign variable

		# compare our retrieve RA with existing RA
		# update is done on the original (existing) RA
		my $UpdatedRA = $ResourceAppointment->compareWith($ExistingRA);

		# after updating our RA object, update the database
		$UpdatedRA->updateDatabase();

	} else { # RA DNE

		# insert RA into our database
		$ResourceAppointment->insertResourceAppointmentIntoOurDB();
	}
}
print "-- End Loop over each RA: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished resource appointment list\n" if $verbose;

##########################################################################################
#
# Data Retrieval PATIENTLOCATION - get list of PL info updated since last update
#
##########################################################################################
print "\n--- Start getPatientLocationsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@PLList = PatientLocation::getPatientLocationsFromSourceDB(\@patientList, $global_patientInfo_sql);
print "--- End getPatientLocationsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got patient location list\n" if $verbose;

#=========================================================================================
# Loop over each PL. Various functions are done.
#=========================================================================================
print "-- Start Loop over each PL: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
foreach my $PatientLocation (@PLList) {

	# check if PL exists in our database
	my $PLExists = $PatientLocation->inOurDatabase();

	if ($PLExists) { # PL exists

		my $ExistingPL = dclone($PLExists); # reassign variable

		# compare our retrieved PL with the existing PL
		# update is done on the original (existing) PL
		my $UpdatedPL = $PatientLocation->compareWith($ExistingPL);

		# after updating our PL object, update the database
		$UpdatedPL->updateDatabase();

	} else { #PL DNE

		# insert PL into our database
		$PatientLocation->insertPatientLocationIntoOurDB();
	}
}
print "-- End Loop over each PL: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished patient location list\n" if $verbose;

##########################################################################################
#
# Data Retrieval PATIENTLOCATIONMH - get list of PL MH info updated since last update
#
##########################################################################################
print "\n--- Start getPatientLocationsMHFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@PLMHList = PatientLocation::getPatientLocationsMHFromSourceDB(\@patientList, \@PLList);
print "--- End getPatientLocationsMHFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got patient location MH list\n" if $verbose;

#=========================================================================================
# Loop over each PL MH. Various functions are done.
#=========================================================================================
print "-- Start Loop over each PL MH: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
foreach my $PatientLocation (@PLMHList) {

	# check if PL exists in our database
	my $PLExists = $PatientLocation->inOurDatabaseMH();

	if ($PLExists) { # PL exists

		my $ExistingPL = dclone($PLExists); # reassign variable

		# compare our retrieved PL with the existing PL
		# update is done on the original (existing) PL
		my $UpdatedPL = $PatientLocation->compareWith($ExistingPL);

		# after updating our PL object, update the database
		$UpdatedPL->updateDatabaseMH();

	} else { #PL DNE

		# insert PL into our database
		$PatientLocation->insertPatientLocationMHIntoOurDB();
	}
}
print "-- End Loop over each PL MH: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished patient location MH list\n" if $verbose;

##########################################################################################
#
# Data Retrieval DOCUMENTS - get list of patients with documents updated since last update
#
##########################################################################################
# print "\n--- Start getDocsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# @DocList = Document::getDocsFromSourceDB($cronLogSer, \@patientList, $global_patientInfo_sql);
# print "--- End getDocsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Got document list\n" if $verbose;

# # Transfer and log patient documents
# print "\n--- Start transferPatientDocuments: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# Document::transferPatientDocuments(@DocList);
# print "--- End transferPatientDocuments: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";

# print "Finished document list\n" if $verbose;

##########################################################################################
#
# Data Retrieval TESTRESULTS - get list of patients with test results updated since last update
#
##########################################################################################
# print "\n--- Start getTestResultsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# @TRList = TestResult::getTestResultsFromSourceDB($cronLogSer, \@patientList, $global_patientInfo_sql);
# print "--- End getTestResultsFromSourceDB: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Got test result list\n" if $verbose;

# #=========================================================================================
# # Loop over each test result. Various functions are done.
# #=========================================================================================
# print "-- Start Loop over each test result: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# foreach my $TestResult (@TRList) {

# 	# check if TR exists in our database
# 	my $TRExists = $TestResult->inOurDatabase();

# 	if ($TRExists) { # TR exists

# 		my $ExistingTR = dclone($TRExists); # reassign variable

# 		# compare our retrieve TR with existing TR
# 		# update is done on the original (existing) TR
# 		my ($UpdatedTR, $change) = $TestResult->compareWith($ExistingTR);

# 		# after updating our TR object, update the database
#         # if there was an actual change in comparison
#         if ($change) {
#     		$UpdatedTR->updateDatabase();
#         };

# 	} else { # TR DNE

# 		# insert TR into our database
# 		$TestResult->insertTestResultIntoOurDB();
# 	}

# }
# print "-- End Loop over each test result: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Finished test result list\n" if $verbose;

##########################################################################################
#
# Publishing ANNOUNCEMENTS
#
##########################################################################################
# print "\n--- Start publishAnnouncements: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# Announcement::publishAnnouncements($cronLogSer, @patientList);
# print "--- End publishAnnouncements: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Finished announcements\n" if $verbose;

##########################################################################################
#
# Publishing TREATMENT TEAM MESSAGES
#
##########################################################################################
# print "\n--- Start publishTxTeamMessages: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# TxTeamMessage::publishTxTeamMessages($cronLogSer, @patientList);
# print "--- End publishTxTeamMessages: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Finished treatment team messages\n" if $verbose;

##########################################################################################
#
# Publishing PATIENTS FOR PATIENTS
#
##########################################################################################
# print "\n--- Start publishPatientsForPatients: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# PatientsForPatients::publishPatientsForPatients($cronLogSer, @patientList);
# print "--- End publishTxTeamMessages: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Finished patients for patients\n";

##########################################################################################
#
# Publishing EDUCATIONAL MATERIALS
#
##########################################################################################
# print "\n--- Start publishEducationalMaterials: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# EducationalMaterial::publishEducationalMaterials($cronLogSer, @patientList);
# print "--- End publishTxTeamMessages: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Finished Educational materials\n" if $verbose;

##########################################################################################
#
# Publishing QUESTIONNAIRES
#
##########################################################################################
# Questionnaire::publishQuestionnaires(@patientList);

# print "Finished Questionnaires\n" if $verbose;

##########################################################################################
#
# Publishing LEGACY QUESTIONNAIRES
#
##########################################################################################
#print "\n--- Start publishLegacyQuestionnaires: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
#LegacyQuestionnaire::publishLegacyQuestionnaires($cronLogSer, @patientList);
# print "--- End publishTxTeamMessages: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
#print "Finished Legacy Questionnaires\n" if $verbose;

# Once everything is complete, we update the "last transferred" field for all controls
# Patient control
Patient::setPatientLastTransferredIntoOurDB($start_datetime);

# Alias control
# Alias::setAliasLastTransferIntoOurDB($start_datetime);

# Post control
# PostControl::setPostControlLastPublishedIntoOurDB($start_datetime);
# Educational material control
# EducationalMaterialControl::setEduMatControlLastPublishedIntoOurDB($start_datetime);

##########################################################################################
#
# Y.Mo 03-Apr-2020
# Updating the TestResultControl is no longer require because the system is fetching from Oacis
#
##########################################################################################
# # Test result control
# TestResultControl::setTestResultLastPublishedIntoOurDB($start_datetime);

# Get the current time
my $current_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));

# Log that the script is finished in the cronlog
Cron::setCronLog("Completed", $current_datetime);
print "--- Completed ---- ", $current_datetime, "\n\n";

print "Start Time: -->> $start_datetime\n";
print "End Time: -->> $current_datetime\n";

# success restart flag and counter for next run
$json_log->{'start'} = 0;
$json_log->{'run'}->{'count'} = 0;
$json_log->{'crash'}->{'count'} = 0;

writeToLogFile($monitor_log, encode_json($json_log), ">");
