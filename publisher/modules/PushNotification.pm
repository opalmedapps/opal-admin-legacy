#!/usr/bin/perl
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
use Storable qw(dclone);
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
# my $thisURL = 'http://localhost:8080/publisher/php/sendPushNotification.php'
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
#====================================================================================
sub sendPushNotification
{
    my ($patientser, $reftablerowser, $notificationtype, %dynamicKeys) = @_; # args

    # retrieve notification parameters
    my $notification        = NotificationControl::getNotificationControlDetails($patientser, $notificationtype);
    my $controlser          = $notification->getNotificationControlSer();
    my $title               = $notification->getNotificationControlName();
    my $message             = $notification->getNotificationControlDescription();

    my ($sendstatus, $sendlog); # initialize

    # special case for replacing the $patientName wildcard
    if (index($message, '$patientName') != -1) {
        # query the patient's first name
        my $firstName;
        try {
            $firstName = Patient::getPatientFirstNameFromSer($patientser);
        } catch {
            $sendlog = "An error occurred while querying the patient's first name: $_";
            insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusFailure, $sendlog);
        };
        if (!defined $firstName) { return; }  # Return if catch block was used

        # add $patientName as a wildcard for replacement
        $dynamicKeys{'\$patientName'} = $firstName;
    }

    # loop through potential wildcard keys to execute a string replace
    for my $key (keys %dynamicKeys) {
        $message =~ s/$key/$dynamicKeys{$key}/g;
    }

    # get a list of the patient's device information
    my @PTDIDs  = getPatientDeviceIdentifiers($patientser);

    if (!@PTDIDs) { # not identifiers listed
        $sendlog        = "Patient has no device identifier! No push notification sent.";
        insertPushNotificationInDB('NULL', $patientser, $controlser, $reftablerowser, $statusWarning, $sendlog);
    }

    foreach my $PTDID (@PTDIDs) {

        # retrieve params
        my $ptdidser        = $PTDID->{ser};
        my $registrationid  = $PTDID->{registrationid};
        my $devicetype      = $PTDID->{devicetype};

        ($sendstatus, $sendlog) = postNotification($title, $message, $devicetype, $registrationid);

        insertPushNotificationInDB($ptdidser, $patientser, $controlser, $reftablerowser, $sendstatus, $sendlog);
    }

    # get a list of the patient caregivers' device information
    my apiResponse = Api::apiPatientCaregivers($patientser);
    apiResponse = decode_json(apiResponse);

    if (exists(apiResponse->{'caregivers'})) {
        my $caregivers = apiResponse->{'caregivers'};
        foreach $caregiver (@{ $caregivers }) {  # anonymous array traverse
            my $devices = $caregiver->{'devices'};
            foreach $device (@{ $devices }) {  # anonymous array traverse
                my $deviceType = $device->{'type'};
                my $push_token = $device->{'push_token'};
                if ($deviceType != 'WEB') {
                    $deviceType = $deviceType == 'IOS' ? 0 : 1;
                    postNotification($title, $message, $deviceType, $push_token);
                }
            }
        }
    }
}

#====================================================================================
# Subroutine to post notifications
#====================================================================================
sub postNotification
{
    my ($title, $message, $devicetype, $registrationid) = @_; # args

    my ($sendstatus, $sendlog); # initialize

    print "\n***** Start Push Notification *****\n";
    print "PatientSerNum: $patientser\n";
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
        $sendstatus = $statusFailure;
        $sendlog    = "Failed to send push notification! Message: 'Push Notification Timed Out'->{'error'}";
    };

    print "\n***** End Push Notification *****\n";

    if ($returnStatus->{'success'} eq 1) {
        $sendstatus = $statusSuccess;
        $sendlog    = "Push notification successfully sent! Message: $message";
    }
    if ($returnStatus->{'success'} eq 0) {
        $sendstatus = $statusFailure;
        $sendlog    = "Failed to send push notification! Message: $returnStatus->{'error'}";
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
    my ($patientser) = @_; # patient serial from args

    # initialize list
    my @PTDIDs = ();

    # DeviceType 0 is iOS
    # DeviceType 1 is Android
    my $select_sql = "
        SELECT DISTINCT
            ptdid.PatientDeviceIdentifierSerNum,
            ptdid.RegistrationId,
            ptdid.DeviceType
        FROM
            PatientDeviceIdentifier ptdid
        WHERE
            ptdid.PatientSerNum = '$patientser'
            AND ptdid.DeviceType in ('0', '1')
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

        my $ptdid = {
            'ser'               => $ser,
            'registrationid'    => $registrationid,
            'devicetype'        => $devicetype
        };

        push(@PTDIDs, $ptdid);
    }

    return @PTDIDs;
}

# Exit smoothly
1;
