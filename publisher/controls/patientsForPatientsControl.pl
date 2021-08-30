#!/usr/bin/perl

#---------------------------------------------------------------------------------
# K.Agnew 2021 Cron Modularity Refactor ++
#---------------------------------------------------------------------------------
# Control script for the sending of patients for patients materials. This script has its
# own dedicated cron job.
# We use our custom Perl Modules to help us with getting information and
# setting them into the appropriate place.

#---------------------------------------------------------------------------------
=Log
This is the first phase for now in separating the dataContorl.pl

Second phase will be modifying the OpalAdmin to use the new tables for
the publishing control. This will allow a slow transition so that 
it is easy to troubleshoot and validate the changes.

YM 2021-06-28
=cut
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

use lib dirname($0) . '/../modules'; # specify where our modules are -- $0 = this script's location
use Configs;
use Database;
use Patient;
use PostControl;
use Alias;
use EducationalMaterialControl;
use PatientsForPatients;
use Cron;

#-----------------------------------------------------------------------
# Monitor this script's execution
# - What this section does is
# 	1. Checks for hanging jobs
# 	2. Checks for crash reports
# Both recorded in monitor_log.json
#-----------------------------------------------------------------------

our $wsSlash = File::Spec->catfile('', '');

$execution_log = dirname($0) . '/../logs/executions_patientsForPatients.log';
$monitor_log = dirname($0) . '/../logs/monitor_log_patientsForPatients.json';

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
				'To'		=> "yickmo\@gmail.com",
				# 'Cc'			=> "yickmo\@gmail.com",
				'Subject'	=> "Potential hanging script - Opal patientsForPatientsControl.pl",
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
					'To'		=> "yickmo\@gmail.com",
					# 'Cc'			=> "yickmo\@gmail.com",
					'Subject'	=> "Script crash - Opal patientsForPatientsControl.pl",
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


# Get the current time (for last-updates/logs)
my $start_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));
print "--- Start patientsForPatientsControl --- ", $start_datetime, "\n";

# Log that the script is initialized in the cronlog
my $cronLogSer = Cron::setCronLog("Started patsPatsControl", $start_datetime);


#=========================================================================================
# Retrieve all patients that are marked for update
#=========================================================================================
print "\n--- Start getPatientsMarkedForUpdate: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# @registeredPatients = Patient::getPatientsMarkedForUpdateModularCron($cronLogSer, 'Patients for Patients');
@patientList = Patient::getPatientsMarkedForUpdateModularCron($cronLogSer, 'Patients for Patients');
print "--- End getPatientsMarkedForUpdate: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got patient list\n" if $verbose;

# print "--- Start Loop over each patient: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# #=========================================================================================
# # Loop over each patient.
# #=========================================================================================
# foreach my $Patient (@registeredPatients) {

#     # retrieve information from source databases
#     my @sourcePatients = $Patient->getPatientInfoFromSourceDBs();

#     foreach my $SourcePatient (@sourcePatients) {

#         # check if patient exists in our database (it should by default)
#         my $patientExists = $SourcePatient->inOurDatabase();

#         if ($patientExists) { # patient exists

#             my $ExistingPatient = dclone($patientExists); # reassign variable

#             # compare our source patient with the existing patient
#             # update is done on the existing patient
#             my ($UpdatedPatient, $change) = $SourcePatient->compareWith($ExistingPatient);

# 			# # 2021-08-26 YM: Remove this for now
# 			# # if there was an actual change in comparison
# 			# if ($change) {
# 			# 	# update the database
# 			# 	$UpdatedPatient->updateDatabase();
# 			# }

#             # push to patient list
#             push(@patientList, $UpdatedPatient);

# 		# # 2021-08-26 YM: Remove this for now
#         # } else { # patient DNE

#     	# 	# insert Patient into our database
# 	    # 	$SourcePatient = $SourcePatient->insertPatientIntoOurDB();

#         #     # push to patient list
#         #     push(@patientList, $SourcePatient);

# 	    }
#     }
# }

# print "-- End Loop over each patient: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
# print "Finished patient list\n" if $verbose;

##########################################################################################
#
# Publishing PATIENTS FOR PATIENTS
#
##########################################################################################
print "\n--- Start publishPatientsForPatients: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
PatientsForPatients::publishPatientsForPatients($cronLogSer, @patientList);
print "--- End publishPatientsForPatients: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Finished patients for patients\n";

# Once everything is complete, we update the "last transferred" field for all controls
# Patient control
Patient::setPatientLastTransferredModularCron($start_datetime, 'Patients for Patients');

# Post control
PostControl::setPostControlLastPublishedModularControllers($start_datetime, 'Patients for Patients');

my $current_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));
# Log that the script is finished in the cronlog
Cron::setCronLog("Completed patsPatsControl", $current_datetime);
print "--- Completed ---- ", $current_datetime, "\n\n";

print "Start Time [patientsForPatientsControl]: -->> $start_datetime\n";
print "End Time [patientsForPatientsControl]: -->> $current_datetime\n";

# success restart flag and counter for next run
$json_log->{'start'} = 0;
$json_log->{'run'}->{'count'} = 0;
$json_log->{'crash'}->{'count'} = 0;

writeToLogFile($monitor_log, encode_json($json_log), ">");


