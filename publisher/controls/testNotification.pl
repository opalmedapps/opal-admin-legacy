#!/usr/bin/perl

#---------------------------------------------------------------------------------
# K.Agnew 2021 Cron Modularity Refactor ++
#---------------------------------------------------------------------------------
# Control script for the sending of announcements to patients. This script has its
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
use Time::Piece;
use POSIX;
use Storable qw(dclone);
use File::Basename;
use File::Spec;
use JSON;
use MIME::Lite;
use Data::Dumper qw(Dumper);
use Net::Address::IP::Local;

use lib dirname($0) . "/../modules";
use Cwd 'abs_path';

use PushNotification;

use LWP::UserAgent; # for post requests

my $statusSuccess = 'T';
my $statusWarning = 'W';
my $statusFailure = 'F';

#---------------------------------------------------------------------------------
# Test push notifications
# run the command as: ./testNotification.pl 51 0 0
# 3 arguments, first arg 51 is patientSerNum
#---------------------------------------------------------------------------------

sub pushNotificationToPatient
{
my ($patientser, $controlser, $reftablerowser) = @_; # args

    $usernamesStr = PushNotification::getPatientCaregivers($patientser, $controlser, $reftablerowser);

     print "\n***** Get Patient Device Identifiers *****\n";

     # get a list of the patient's device information
     my @PTDIDs  = PushNotification::getPatientDeviceIdentifiers($usernamesStr);

     if (!@PTDIDs) { # not identifiers listed
         $sendlog        = "Patient has no device identifier for the usernames: $usernamesStr! No push notification sent.";
         PushNotification::insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusWarning, $sendlog);
         return;
     }

     print "\n***** Push notification to patient caregivers *****\n";

     $title = 'test notfications';

     foreach my $PTDID (@PTDIDs) {

         # retrieve params
         my $ptdidser        = $PTDID->{ser};
         my $registrationid  = $PTDID->{registrationid};
         my $devicetype      = $PTDID->{devicetype};

         ($sendstatus, $sendlog) = PushNotification::postNotification($title, $message, $devicetype, $registrationid);

         PushNotification::insertPushNotificationInDB($ptdidser, $patientser, $controlser, $reftablerowser, $sendstatus, $sendlog);
     }
}

print "$ARGV[0], $ARGV[1], $ARGV[2]\n";

pushNotificationToPatient($ARGV[0], $ARGV[1], $ARGV[2])
