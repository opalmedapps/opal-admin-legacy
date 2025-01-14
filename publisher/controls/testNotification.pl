#!/usr/bin/perl

# SPDX-FileCopyrightText: Copyright (C) 2023 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

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

    ($usernamesStr, $institution_acronym_en, $institution_acronym_fr, $userLanguageList) = getPatientCaregivers($patientser, $controlser, $reftablerowser);

    if (!$usernamesStr) {
        print "Patient username array is empty\n";
        $sendlog        = "Patient has no related caregivers. the username array is empty\n";
        PushNotification::insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusWarning, $sendlog);
        return;
    }

    print "\n***** Get Patient Device Identifiers *****\n";

    # get a list of the patient's device information
    my @PTDIDs  = PushNotification::getPatientDeviceIdentifiers($usernamesStr);

    if (!@PTDIDs) { # not identifiers listed
        $sendlog        = "Patient has no device identifier for the usernames: $usernamesStr! No push notification sent.";
        PushNotification::insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusWarning, $sendlog);
        return;
    }

    print "\n***** Push notification to patient caregivers *****\n";

    $title = 'test notifications';
    $message = 'test notifications'

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
