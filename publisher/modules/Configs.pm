# SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

#---------------------------------------------------------------------------------
# A.Joseph 30-Sept-2016 ++ File: Config.pm
#---------------------------------------------------------------------------------
# Perl module that sets various perl constants for use in this project.
#--------------------------------------------------------------------------------
package Configs; # define package name

use strict;
use warnings;
use Env;

use Const::Fast;
use Cwd 'abs_path';
use JSON;

# Get directory path of this file
my $path_name = abs_path($0);
# Strip child directories to get root path
# 2021-06-03 KA cron refactor:
# add these lines for all future modular cron controllers
$path_name =~ s/publisher\/dataControl.pl//g;
$path_name =~ s/publisher\/dataControl2.pl//g;
$path_name =~ s/publisher\/modules\/PushNotificationFromPHP.pm//g;
$path_name =~ s/publisher\/controls\/announcementControl.pl//g;
$path_name =~ s/publisher\/controls\/documentControl.pl//g;
$path_name =~ s/publisher\/controls\/txTeamMessagesControl.pl//g;
$path_name =~ s/publisher\/controls\/patientsForPatientsControl.pl//g;
$path_name =~ s/publisher\/controls\/legacyQuestionnaireControl.pl//g;
$path_name =~ s/publisher\/controls\/educationalMaterialControl.pl//g;
$path_name =~ s/publisher\/controls\/testNotification.pl//g;

# DEFINE OPAL DATABASE SETTINGS FROM ENV FILE
# NOTE: This works for a MySQL setup.
const our $OPAL_DB_NAME         => 'OpalDB';
const our $OPAL_DB_HOST         => $ENV{'OPAL_DB_HOST'};
const our $OPAL_DB_PORT         => $ENV{'OPAL_DB_PORT'};
const our $OPAL_DB_DSN          => 'DBI:MariaDB:database=' . $OPAL_DB_NAME . ';host=' . $OPAL_DB_HOST . ';port=' . $OPAL_DB_PORT;
const our $OPAL_DB_USERNAME     => $ENV{'OPAL_DB_USER'};
const our $OPAL_DB_PASSWORD     => $ENV{'OPAL_DB_PASSWORD'};
const our $USE_SSL              => $ENV{'DATABASE_USE_SSL'};

our $SSL_CA = '';

if (defined $USE_SSL && $USE_SSL eq '1') {
    $SSL_CA           = $ENV{'SSL_CA'};
}

our $OPAL_DB_SSL_DSN      = 'DBI:MariaDB:database=' . $OPAL_DB_NAME . ';host=' . $OPAL_DB_HOST . ';port=' . $OPAL_DB_PORT . ';mariadb_ssl=1;mariadb_ssl_verify_server_cert=1;mariadb_ssl_ca_file=' . $SSL_CA;

# Environment-specific variables
const our $FRONTEND_ABS_PATH    => '/var/www/html/';
const our $FRONTEND_REL_URL     => '/';
const our $BACKEND_ABS_PATH     => $FRONTEND_ABS_PATH . 'publisher/'; # absolute path of this project (include trailing slash)
const our $BACKEND_REL_URL      => $FRONTEND_REL_URL . 'publisher/'; # relative path of this project from http host (include trailing slash)

# YM 2019-01-07 : Production use shared folder
const our $BACKEND_SHARED_URL => $ENV{'CLINICAL_REPORTS_PATH'};

# DEFINE FTP CREDENTIALS HERE
# NOTE: This is for sending clinical documents
const our $ARIA_FTP_DIR         => $ENV{'ARIA_DOCUMENT_PATH'}; # clinical aria document directory
const our $MOSAIQ_FTP_DIR       => $ENV{'MOSAIQ_DOCUMENT_PATH'}; # clinical mosaiq document directory
# const our $FTP_LOCAL_DIR        =>  $BACKEND_ABS_PATH . 'clinical/documents'; # PDF directory
# YM 2019-01-07 : Production use shared folder
const our $FTP_LOCAL_DIR        =>  $BACKEND_SHARED_URL . 'clinical/documents'; # PDF directory
const our $OFFICE_PATH_DIR      => $ENV{'OFFICE_DOCUMENT_PATH'}; # Location where office is installed

#DEFINE PUSH NOTIFICATION URL HERE
const our $PUSH_NOTIFICATION_URL     => $ENV{'PUSH_NOTIFICATION_URL'};
#NEW BACKEND API URL AND TOKEN
const our $NEW_BACKEND_HOST     => $ENV{'NEW_OPALADMIN_HOST_INTERNAL'};
const our $NEW_BACKEND_TOKEN     => $ENV{'NEW_OPALADMIN_TOKEN'};

#======================================================================================
# Subroutine to return source database credentials
#======================================================================================
sub fetchSourceCredentials
{
    my ($sourceDBser) = @_; # source serial number

    # initialize object
    my $sourceCredentials = {
        _dsn        => undef,
        _user       => undef,
        _password   => undef,
    };

    if (!$sourceDBser) {return $sourceCredentials;} # return null object

    # These variables are not used anymore, they are removed from the environment file.
    # we are commenting the following lines for now, to cleanup this code later
    # TODO: check this function and remove it if it is not used
    # ARIA
    #if ($sourceDBser eq 1) {

    #    $sourceCredentials->{_dsn}          = $ARIA_DB_DSN;
    #    $sourceCredentials->{_user}         = $ARIA_DB_USERNAME;
    #    $sourceCredentials->{_password}     = $ARIA_DB_PASSWORD;

    #}

    # WaitRoomManagement
    #elsif ($sourceDBser eq 2) {

    #    $sourceCredentials->{_dsn}          = $WRM_DB_DSN;
    #    $sourceCredentials->{_user}         = $WRM_DB_USERNAME;
    #    $sourceCredentials->{_password}     = $WRM_DB_PASSWORD;

    #}

    # Mosaiq
    #elsif ($sourceDBser eq 3) {

    #    $sourceCredentials->{_dsn}          = $MOSAIQ_DB_DSN;
    #    $sourceCredentials->{_user}         = $MOSAIQ_DB_USERNAME;
    #    $sourceCredentials->{_password}     = $MOSAIQ_DB_PASSWORD;

    #}
    # Others
    # ...


    return $sourceCredentials;
}

#======================================================================================
# Subroutine to return FTP credentials
#======================================================================================
sub fetchFTPCredentials
{
    my ($sourceDBser) = @_; # source serial number

    # initialize object
    my $ftpCredentials = {
        _localdir       => undef,
        _clinicaldir    => undef,
    };

    if (!$sourceDBser) {return $ftpCredentials;} # return null object

     # ARIA
    if ($sourceDBser eq 1) {

        $ftpCredentials->{_localdir}            = $FTP_LOCAL_DIR;
        $ftpCredentials->{_clinicaldir}         = $ARIA_FTP_DIR;

    }

    # WaitRoomManagement
    elsif ($sourceDBser eq 2) {

        # None yet
    }

    # Mosaiq
    elsif ($sourceDBser eq 3) {

        $ftpCredentials->{_localdir}            = $FTP_LOCAL_DIR;
        $ftpCredentials->{_clinicaldir}         = $MOSAIQ_FTP_DIR;


    }

    # Others
    # ...


    return $ftpCredentials;

}

#======================================================================================
# Subroutine to return PUSH NOTIFICATION URL HERE
#======================================================================================
sub fetchPushNotificationUrl
{
    return $PUSH_NOTIFICATION_URL;
}

#======================================================================================
# Subroutine to return NEW BACKEND URL
#======================================================================================
sub fetchNewBackendHost
{
    return $NEW_BACKEND_HOST;
}

#======================================================================================
# Subroutine to return NEW BACKEND TOKEN
#======================================================================================
sub fetchNewBackendToken
{
    return $NEW_BACKEND_TOKEN;
}

1; # end module
