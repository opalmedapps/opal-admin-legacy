#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 30-Sept-2016 ++ File: Config.pm
#---------------------------------------------------------------------------------
# Perl module that sets various perl constants for use in this project. 
#--------------------------------------------------------------------------------
package Configs; # define package name

use strict;
use warnings;

use Const::Fast;

# DEFINE CLINICAL SERVER/DATABASE CREDENTIALS HERE
# NOTE: This works for a MicrosoftSQL (MSSQL) setup.
const our $CLINICAL_DB_HOST     => 'CLINICAL_DB_HOST_HERE';
const our $CLINICAL_DB_NAME     => 'DBI:Sybase:server=' . $CLINICAL_DB_HOST;
const our $CLINICAL_DB_USER     => 'CLINICAL_DB_USER_HERE';
const our $CLINICAL_DB_PASS     => 'CLINICAL_DB_PASS_HERE';
 
# DEFINE OPAL DATABASE CREDENTIALS HERE
# NOTE: This works for a MySQL setup. 
const our $OPAL_DB_NAME         => 'OPAL_DB_NAME_HERE'; 
const our $OPAL_DB_HOST         => 'OPAL_DB_HOST_HERE';
const our $OPAL_DB_DSN          => 'DBI:mysql:database=' . $OPAL_DB_NAME . ';host=' . $OPAL_DB_HOST;
const our $OPAL_DB_USER         => 'OPAL_DB_USER_HERE';
const our $OPAL_DB_PASS         => 'OPAL_DB_PASS_HERE';

# DEFINE: WRM DATABASE CREDENTIALS HERE
# NOTE: This works for a MySQL setup.
const our $WRM_HOST             => 'WRM_HOST_HERE';
const our $WRM_NAME             => 'WRM_NAME_HERE';
const our $WRM_DSN              => 'DBI:mysql:database=' . $WRM_NAME . ';host=' . $WRM_HOST;
const our $WRM_USERNAME         => 'WRM_USERNAME_HERE';
const our $WRM_PASSWORD         => 'WRM_PASSWORD_HERE';

# Environment-specific variables
const our $FRONTEND_ABS_PATH    => 'FRONTEND_ABS_PATH_HERE';
const our $FRONTEND_REL_URL     => 'FRONTEND_REL_URL_HERE';
const our $BACKEND_ABS_PATH     => $FRONTEND_ABS_PATH . 'backend/'; # absolute path of this project (include trailing slash)
const our $BACKEND_REL_URL      => $FRONTEND_REL_URL . 'backend/'; # relative path of this project from http host (include trailing slash)

# DEFINE FTP CREDENTIALS HERE
# NOTE: This is for sending clinical documents
const our $FTP_CLINICAL_DIR         => 'FTP_CLINICAL_DIR_HERE'; # clinical document directory
const our $FTP_LOCAL_DIR            => $BACKEND_ABS_PATH . 'clinical/documents'; # local clinical directory

#======================================================================================
# Subroutine to return source database credentials
#======================================================================================
sub fetchSourceCredentials
{
    my ($sourceDBser) = @_; # source serial number 

    # initialize object
    my $sourceCredentials = {
        _dsn       => undef,
        _user       => undef,
        _password   => undef,
    };

    if (!$sourceDBser) {return $sourceCredentials;} # return null object

    if ($sourceDBser eq 1) { 

        $sourceCredentials->{_dsn}      = $CLINICAL_DB_NAME;
        $sourceCredentials->{_user}     = $CLINICAL_DB_USER;
        $sourceCredentials->{_password} = $CLINICAL_DB_PASS;

    }

    # WaitRoomManagement
    elsif ($sourceDBser eq 2) {

        $sourceCredentials->{_dsn}          = $WRM_DSN;
        $sourceCredentials->{_user}         = $WRM_USERNAME;
        $sourceCredentials->{_password}     = $WRM_PASSWORD;

    }
    
    # Others
    # ...


    return $sourceCredentials; 
}

1; # end module 
