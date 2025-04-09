#!/usr/bin/perl

# SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

#---------------------------------------------------------------------------------
# A.Joseph 13-May-2016 ++ File: PushNotification.pm
#---------------------------------------------------------------------------------
# Perl module that creates a push notification class. This module calls a constructor
# to create a push notification object that contains push notification information stored as
# object variables
#
# There exists various subroutines to set / get information and compare information
# between two push notification objects.

package PushNotification; # Declaring package name

use Database; # Our custom database module
use Configs; # Configs.pm
use NotificationControl; # NotificationControl.pm
use Api; # Api.pm

use Time::Piece; # perl module
use Array::Utils qw(:all);
use POSIX; # perl module
use LWP::UserAgent; # for post requests
use JSON;
use Net::Address::IP::Local;
use Cwd;
use Try::Tiny;

use Patient; # Our custom patient module

#---------------------------------------------------------------------------------
# Connect to the databases
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

# global vars
#my $ipaddress = Net::Address::IP::Local->public;
#my $thisURL = 'https://' . $ipaddress . $Configs::BACKEND_REL_URL . 'php/sendPushNotification.php';
# the docker environment is blocking the local net address currently. the direct url for push notifiction is add below
my $thisURL = Configs::fetchPushNotificationUrl();
my $statusSuccess = 'T';
my $statusWarning = 'W';
my $statusFailure = 'F';

#====================================================================================
# Constructor for our notification class
#====================================================================================
sub new
{
    my $class = shift;
    my $pushnotification = {
        _ser            => undef,
        _ptdidser       => undef,
        _patientser     => undef,
        _controlser     => undef,
        _reftablerowser => undef,
        _sendstatus     => undef,
        _sendlog        => undef,
    };

    # bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $pushnotification, $class;
    return $pushnotification;
}

#====================================================================================
# Subroutine to set the Push Notification Serial
#====================================================================================
sub setPushNotificationSer
{
    my ($pushnotification, $ser) = @_; # push notification object with provided serial in args
    $pushnotification->{_ser} = $ser; # set the push notification ser
    return $pushnotification->{_ser};
}

#====================================================================================
# Subroutine to set the Push Notification PT DID Serial
#====================================================================================
sub setPushNotificationPTDIDSer
{
    my ($pushnotification, $ptdidser) = @_; # push notification object with provided serial in args
    $pushnotification->{_ptdidser} = $ptdidser; # set the push notification ser
    return $pushnotification->{_ptdidser};
}

#====================================================================================
# Subroutine to set the Push Notification Patient Serial
#====================================================================================
sub setPushNotificationPatientSer
{
    my ($pushnotification, $patientser) = @_; # push notification object with provided serial in args
    $pushnotification->{_patientser} = $patientser; # set the push notification ser
    return $pushnotification->{_patientser};
}

#====================================================================================
# Subroutine to set the Push Notification Control Ser
#====================================================================================
sub setPushNotificationControlSer
{
    my ($pushnotification, $controlser) = @_; # push notification object with provided serial in args
    $pushnotification->{_controlser} = $controlser; # set the push notification ser
    return $pushnotification->{_controlser};
}

#====================================================================================
# Subroutine to set the Push Notification Ref Table Row Serial
#====================================================================================
sub setPushNotificationRefTableRowSer
{
    my ($pushnotification, $reftablerowser) = @_; # pushnotification object with provided serial in args
    $pushnotification->{_reftablerowser} = $reftablerowser; # set the push notification ser
    return $pushnotification->{_reftablerowser};
}

#====================================================================================
# Subroutine to set the Push Notification Send status
#====================================================================================
sub setPushNotificationSendStatus
{
    my ($pushnotification, $sendstatus) = @_; # push notification object with provided status in args
    $pushnotification->{_sendstatus} = $sendstatus; # set the notification status
    return $pushnotification->{_sendstatus};
}

#====================================================================================
# Subroutine to set the Push Notification Send Log
#====================================================================================
sub setPushNotificationSendLog
{
    my ($pushnotification, $sendlog) = @_; # push notification object with provided log in args
    $pushnotification->{_sendlog} = $sendlog; # set the notification log
    return $pushnotification->{_sendlog};
}

#====================================================================================
# Subroutine to get the Push Notification Serial
#====================================================================================
sub getPushNotificationSer
{
	my ($pushnotification) = @_; # our push notification object
	return $pushnotification->{_ser};
}

#====================================================================================
# Subroutine to get the Push Notification PT DID Serial
#====================================================================================
sub getPushNotificationPTDIDSer
{
	my ($pushnotification) = @_; # our push notification object
	return $pushnotification->{_ptdidser};
}

#====================================================================================
# Subroutine to get the Push Notification Patient Serial
#====================================================================================
sub getPushNotificationPatientSer
{
	my ($pushnotification) = @_; # our push notification object
	return $pushnotification->{_patientser};
}

#====================================================================================
# Subroutine to get the Push Notification Control Serial
#====================================================================================
sub getPushNotificationControlSer
{
	my ($pushnotification) = @_; # our push notification object
	return $pushnotification->{_controlser};
}

#====================================================================================
# Subroutine to get the Push Notification Ref Table Row Serial
#====================================================================================
sub getPushNotificationRefTableRowSer
{
	my ($pushnotification) = @_; # our push notification object
	return $pushnotification->{_reftablerowser};
}

#====================================================================================
# Subroutine to get the Pust Notification Send Status
#====================================================================================
sub getPushNotificationSendStatus
{
	my ($pushnotification) = @_; # our push notification object
	return $pushnotification->{_sendstatus};
}

#====================================================================================
# Subroutine to get the Push Notification Send Log
#====================================================================================
sub getPushNotificationSendLog
{
	my ($pushnotification) = @_; # our push notification object
	return $pushnotification->{_sendlog};
}

#====================================================================================
# Subroutine to send/log push notification
#
# NOTE: The same functionality already exists in Perl (PushNotification.pm).
# Any change to the logic here needs to be applied there as well.
#====================================================================================
sub sendPushNotification
{
    my ($patientser, $reftablerowser, $notificationtype, %dynamicKeys) = @_; # args
    # retrieve notification parameters
    my $notification        = NotificationControl::getNotificationControlDetails($patientser, $notificationtype);
    my $controlser          = $notification->getNotificationControlSer();
    my $name                = $notification->getNotificationControlName();
    my $description         = $notification->getNotificationControlDescription();

    my ($sendstatus, $sendlog); # initialize

    # query the patient's first name
    my $firstName;
    try {
        $firstName = Patient::getPatientFirstNameFromSer($patientser);
    } catch {
        $sendlog = "An error occurred while querying the patient's first name: $_";
        insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusFailure, $sendlog);
    };
    if (!defined $firstName) { return; }  # Return if catch block was used

    ($usernamesStr, $institution_acronym_en, $institution_acronym_fr, $userLanguageList) = getPatientCaregivers($patientser, $controlser, $reftablerowser);

    if (!$usernamesStr) {
        print "\nPatient username array is empty\n";
        return;
    }

    print "\n***** Get Patient Device Identifiers *****\n";

    # get a list of the patient's device information
    my @PTDIDs  = getPatientDeviceIdentifiers($usernamesStr);

    if (!@PTDIDs) { # not identifiers listed
        $sendlog        = "Patient has no device identifier for the usernames: $usernamesStr! No push notification sent.";
        insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusWarning, $sendlog);
        return;
    }

    # NOTE! Currently push notifications are sent based on the target patient's language.
    # E.g., If Homer is a target patient for whom a new record and a push notification are being created,
    # and Homer's language is set to English, then push notification for Marge will be also in English
    # regardless of Marge's language setting.
    # TODO: Take into account caregiver's language when send push notifications. See QSCCD-2118.
    print "\n***** Push notification to patient caregivers *****\n";

    foreach my $PTDID (@PTDIDs) {

        # retrieve params
        my $ptdidser        = $PTDID->{ser};
        my $registrationid  = $PTDID->{registrationid};
        my $devicetype      = $PTDID->{devicetype};
        my $language        = $userLanguageList->{$PTDID->{username}}
        my $title           = $name->{$language};
        my $message         = $description->{$language};

        # special case for replacing the $patientName wildcard
        if (index($message, '$patientName') != -1) {
            # add $patientName as a wildcard for replacement
            $dynamicKeys{'\$patientName'} = $firstName;
        }

        # special case for replacing the $institution wildcard
        if (index($message, '$institution') != -1) {
            # TODO: update the code below once push notifications are built using caregiver's language setting.
            # See QSCCD-2118.

            # add $institution as a wildcard for replacement
            if ($language eq "en") { $dynamicKeys{'\$institution'} = $institution_acronym_en; }
            if ($language eq "fr") { $dynamicKeys{'\$institution'} = $institution_acronym_fr; }
        }

        # loop through potential wildcard keys to execute a string replace
        for my $key (keys %dynamicKeys) {
            $message =~ s/$key/$dynamicKeys{$key}/g;
        }

        ($sendstatus, $sendlog) = postNotification($title, $message, $devicetype, $registrationid);

        insertPushNotificationInDB($ptdidser, $patientser, $controlser, $reftablerowser, $sendstatus, $sendlog);
    }
}

#====================================================================================
# Subroutine to get patient caregivers
#====================================================================================
sub getPatientCaregivers
{
    my ($patientser, $controlser, $reftablerowser) = @_; # args
    # get a list of the patient caregivers' device information
    my $apiResponseStr = Api::apiPatientCaregiverDevices($patientser);
    $apiResponse = decode_json($apiResponseStr);

    print "api response: $apiResponseStr\n";

    if ($apiResponse->{'data_access'} != 'ALL') {
        $sendlog        = "Patient has no data access.";
        insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusWarning, $sendlog);
        return;
    }

    # get caregiver's username array
    my @usernames = ();
    my @userLanguageList = {};
    if (exists($apiResponse->{'caregivers'})) {
        my $caregivers = $apiResponse->{'caregivers'};
        foreach $caregiver (@{ $caregivers }) {  # anonymous array traverse
            push @usernames, $caregiver->{'username'};
            $userLanguageList->{$caregiver->{'username'}} = $caregiver->{'language'};
        }
    }

    print "username list: @usernames\n";

    if (!@usernames) {
        $sendlog        = "Patient has no related caregivers.";
        insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusWarning, $sendlog);
        return '';
    }
    # convert username array to string for the query
    my $usernamesStr = join("','", @usernames);
    $usernamesStr = "'".$usernamesStr."'";

    my $acronym_en = $apiResponse->{'institution'}->{'acronym_en'};
    my $acronym_fr = $apiResponse->{'institution'}->{'acronym_fr'};

    return ($usernamesStr, $acronym_en, $acronym_fr, $userLanguageList);
}

#====================================================================================
# Subroutine to post notifications
#====================================================================================
sub postNotification
{
    my ($title, $message, $devicetype, $registrationid) = @_; # args

    my ($sendstatus, $sendlog); # initialize

    print "\n***** Start Push Notification *****\n";
    print "DeviceType: $devicetype\n";
    print "Title: $title\n";

    # system command to call PHP push notification script
    my $browser = LWP::UserAgent->new;
    # Uncomment the line below to skip the ssl verification
    # $browser->ssl_opts(verify_hostname => 0, SSL_verify_mode => 0x00);
    my $response = $browser->post($thisURL,
        [
            'message_title'     => $title,
            'message_text'      => $message,
            'device_type'       => $devicetype,
            'registration_id'   => $registrationid
        ]
    );

    # json decode
    try {
        $returnStatus = decode_json($response->content);
    } catch {
        $sendstatus = $statusWarning;
        $sendlog    = "Unknown status of push notification! Message: 'Failed to decode response: $_";
    };

    print "\n***** End Push Notification *****\n";

    if ($returnStatus->{'success'} eq 1) {
        $sendstatus = $statusSuccess;
        $sendlog    = "Push notification successfully sent! Message: $message";
    }
    elsif ($returnStatus->{'success'} eq 0) {
        $sendstatus = $statusFailure;
        $sendlog    = "Failed to send push notification! Message: $returnStatus->{'error'}";
    }
    else {
        $sendstatus = $statusWarning;
        $sendlog    = "Unknown status of push notification! Unexpected return status: $returnStatus";
    }

    return ($sendstatus, $sendlog);
}


#====================================================================================
# Subroutine to insert a record into the PushNotification table
#====================================================================================
sub insertPushNotificationInDB
{
    my ($ptdidser, $patientser, $controlser, $reftablerowser, $sendstatus, $sendlog) = @_; # args

    my $insert_sql = "
        INSERT INTO
            PushNotification (
                PatientDeviceIdentifierSerNum,
                PatientSerNum,
                NotificationControlSerNum,
                RefTableRowSerNum,
                DateAdded,
                SendStatus,
                SendLog
            )
        VALUES (
            $ptdidser,
            '$patientser',
            '$controlser',
            '$reftablerowser',
            NOW(),
            \"$sendstatus\",
            \"$sendlog\"
        );
    ";

    # prepare query
    my $query = $SQLDatabase->prepare($insert_sql)
        or die "Could not prepare query: " . $SQLDatabase->errstr;

    # execute query
    $query->execute()
        or die "Could not execute query: " . $query->errstr;
}

#====================================================================================
# Subroutine to get all patient device identifiers
#====================================================================================
sub getPatientDeviceIdentifiers
{
    my ($usernamesStr) = @_; # patient serial from args

    # initialize list
    my @PTDIDs = ();

    if (!$usernamesStr) {
        $usernamesStr = "''";
    }

    # DeviceType 0 is iOS
    # DeviceType 1 is Android
    my $select_sql = "
        SELECT DISTINCT
            ptdid.PatientDeviceIdentifierSerNum,
            ptdid.RegistrationId,
            ptdid.DeviceType,
            ptdid.Username
        FROM
            PatientDeviceIdentifier ptdid
        WHERE ptdid.DeviceType in ('0', '1')
        AND Username in ($usernamesStr)
        AND IfNull(RegistrationId, '') <> ''
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        my $ser             = $data[0];
        my $registrationid  = $data[1];
        my $devicetype      = $data[2];
        my $username        = $data[3];

        my $ptdid = {
            'ser'               => $ser,
            'registrationid'    => $registrationid,
            'devicetype'        => $devicetype,
            'username'          => $username,
        };

        push(@PTDIDs, $ptdid);
    }

    return @PTDIDs;
}

# Exit smoothly
1;
