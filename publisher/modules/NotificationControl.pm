# SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

#---------------------------------------------------------------------------------
# A.Joseph 21-Nov-2016 ++ File: NotificationControl.pm
#---------------------------------------------------------------------------------
# Perl module that creates a notification control class. This module calls a constructor
# to create a notification object that contains notification control information stored as
# object variables
#
# There exists various subroutines to set / get information and compare information
# between two notification objects.

package NotificationControl; # Declaring package name

use Database;
use Data::Dumper;

#---------------------------------------------------------------------------------
# Connect to the databases
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our notification class
#====================================================================================
sub new
{
    my $class = shift;
    my $notification = {
        _ser            => undef,
        _name           => undef,
        _description    => undef,
        _type           => undef,
        _language       => undef,
    };

    # bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $notification, $class;
    return $notification;
}

#====================================================================================
# Subroutine to set the Notification Control Serial
#====================================================================================
sub setNotificationControlSer
{
    my ($notification, $ser) = @_; # notification object with provided serial in args
    $notification->{_ser} = $ser; # set the notification ser
    return $notification->{_ser};
}

#====================================================================================
# Subroutine to set the Notification Control Name
#====================================================================================
sub setNotificationControlName
{
    my ($notification, $name) = @_; # notification object with provided name in args
    $notification->{_name} = $name; # set the notification name
    return $notification->{_name};
}

#====================================================================================
# Subroutine to set the Notification Control Description
#====================================================================================
sub setNotificationControlDescription
{
    my ($notification, $description) = @_; # notification object with provided description in args
    $notification->{_description} = $description; # set the notification description
    return $notification->{_description};
}

#====================================================================================
# Subroutine to set the Notification Control Type
#====================================================================================
sub setNotificationControlType
{
    my ($notification, $type) = @_; # notification object with provided type in args
    $notification->{type} = $type; # set the notification type
    return $notification->{_type};
}

#====================================================================================
# Subroutine to set the Notification Control Language
#====================================================================================
sub setNotificationControlLanguage
{
    my ($notification, $language) = @_; # notification object with provided language in args
    $notification->{_language} = $language; # set the notification name
    return $notification->{_language};
}

#====================================================================================
# Subroutine to get the Notification Control Serial
#====================================================================================
sub getNotificationControlSer
{
	my ($notification) = @_; # our notification object
	return $notification->{_ser};
}

#====================================================================================
# Subroutine to get the Notification Control Name
#====================================================================================
sub getNotificationControlName
{
	my ($notification) = @_; # our notification object
	return $notification->{_name};
}

#====================================================================================
# Subroutine to get the Notification Control Description
#====================================================================================
sub getNotificationControlDescription
{
	my ($notification) = @_; # our notification object
	return $notification->{_description};
}

#====================================================================================
# Subroutine to get the Notification Control Type
#====================================================================================
sub getNotificationControlType
{
	my ($notification) = @_; # our notification object
	return $notification->{_type};
}

#====================================================================================
# Subroutine to get the Notification Control Language
#====================================================================================
sub getNotificationLanguage
{
    my ($notification) = @_; # our notification object
	return $notification->{_language};
}

#====================================================================================
# Subroutine to get notification details according to patient and type
#====================================================================================
sub getNotificationControlDetails
{
    my ($patientser, $notificationtype) = @_; # args

    my $notification = new NotificationControl(); # initialize

    my $select_sql = "
        SELECT DISTINCT
            NotificationControl.NotificationControlSerNum,
            NotificationControl.Description_EN,
            NotificationControl.Description_FR,
            NotificationControl.Name_EN,
            NotificationControl.Name_FR,
            Patient.Language AS Language
        FROM
            Patient,
            NotificationControl,
            NotificationTypes
        WHERE
            Patient.PatientSerNum                       = '$patientser'
        AND NotificationControl.NotificationTypeSerNum  = NotificationTypes.NotificationTypeSerNum
        AND NotificationTypes.NotificationTypeId        = '$notificationtype'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {
        $ser        = $data[0];
        $message    = {
            en => $data[1],
            fr => $data[2],
        };
        $title      = {
            en => $data[3],
            fr => $data[4],
        };
        $language   = $data[5];

        $notification->setNotificationControlSer($ser);
        $notification->setNotificationControlDescription($message);
        $notification->setNotificationControlName($title);
        $notification->setNotificationControlLanguage($language);
    }

    return $notification;
}



# exit smoothly
1;
