#!/usr/bin/perl

# SPDX-FileCopyrightText: Copyright (C) 2015 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

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
				'To'		=> "gregormitchell\@gmail.com",
				#'Cc'			=> "someone\@gmail.com",
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
					'To'		=> "gregormitchell\@gmail.com",
					#'Cc'			=> "someone\@gmail.com",
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

# Get the current time (for last-updates/logs)
my $start_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));
print "--- Start --- ", $start_datetime, "\n";

#-----------------------------------------------------------------------
# Parameter initialization
#-----------------------------------------------------------------------
my @registeredPatients = ();

my $verbose = 1;

#=========================================================================================
# Retrieve all patients that are marked for update
# Most of the functionality in this script has been removed/moved elsewhere, but we keep getPatientsMarkedForUpdateLegacy because
# it updates the PatientControl.TransferFlag which might still be used somewhere else in the cron.
#=========================================================================================
print "\n--- Start getPatientsMarkedForUpdate: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
@registeredPatients = Patient::getPatientsMarkedForUpdateLegacy();
print "--- End getPatientsMarkedForUpdate: ", strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "\n";
print "Got patient list\n" if $verbose;

# Once everything is complete, we update the "last transferred" field for all controls
# Patient control
Patient::setPatientLastTransferredIntoOurDB($start_datetime);

# Get the current time
my $current_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));

# Log that the script is finished in the cronlog
print "--- Completed ---- ", $current_datetime, "\n\n";

print "Start Time: -->> $start_datetime\n";
print "End Time: -->> $current_datetime\n";

# success restart flag and counter for next run
$json_log->{'start'} = 0;
$json_log->{'run'}->{'count'} = 0;
$json_log->{'crash'}->{'count'} = 0;

writeToLogFile($monitor_log, encode_json($json_log), ">");
