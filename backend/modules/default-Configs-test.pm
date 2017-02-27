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

# DEFINE ARIA SERVER/DATABASE CREDENTIALS HERE
# NOTE: This works for a MicrosoftSQL (MSSQL) setup.
const our $ARIA_DB_HOST     => '172.16.220.56';
const our $ARIA_DB_PORT     => '1433';
const our $ARIA_DB_DSN      => 'DBI:Sybase:host=' . $ARIA_DB_HOST . ';port=' . $ARIA_DB_PORT;
const our $ARIA_DB_USERNAME => 'reports';
const our $ARIA_DB_PASSWORD => 'reports';
 
# DEFINE OPAL DATABASE CREDENTIALS HERE
# NOTE: This works for a MySQL setup. 
const our $OPAL_DB_NAME         => 'BBBB_Test'; 
const our $OPAL_DB_HOST         => '172.26.66.41';
const our $OPAL_DB_PORT         => '3306';
const our $OPAL_DB_DSN          => 'DBI:mysql:database=' . $OPAL_DB_NAME . ';host=' . $OPAL_DB_HOST . ';port=' . $OPAL_DB_PORT;
const our $OPAL_DB_USERNAME     => 'ackeem';
const our $OPAL_DB_PASSWORD     => 'tev_2032';

# DEFINE: WRM DATABASE CREDENTIALS HERE
# NOTE: This works for a MySQL setup.
const our $WRM_DB_HOST             => '172.26.66.41';
const our $WRM_DB_PORT             => '3306';
const our $WRM_DB_NAME             => 'WaitRoomManagement';
const our $WRM_DB_DSN              => 'DBI:mysql:database=' . $WRM_DB_NAME . ';host=' . $WRM_DB_HOST . ';port=' . $WRM_DB_PORT;
const our $WRM_DB_USERNAME         => 'readonly';
const our $WRM_DB_PASSWORD         => 'readonly';

# DEFINE MOSAIQ SERVER/DATABASE CREDENTIALS HERE
# NOTE: This works for a MicrosoftSQL (MSSQL) setup.
const our $MOSAIQ_DB_HOST     => 'MOSAIQ_DB_HOST_HERE';
const our $MOSAIQ_DB_PORT     => 'MOSAIQ_DB_PORT_HERE';
const our $MOSAIQ_DB_DSN      => 'DBI:Sybase:host=' . $MOSAIQ_DB_HOST . ';port=' . $MOSAIQ_DB_PORT;
const our $MOSAIQ_DB_USERNAME => 'MOSAIQ_DB_USERNAME_HERE';
const our $MOSAIQ_DB_PASSWORD => 'MOSAIQ_DB_PASSWORD_HERE';

# Environment-specific variables
const our $FRONTEND_ABS_PATH    => '/var/www/devDocuments/opalAdmin/';
const our $FRONTEND_REL_URL     => '/devDocuments/opalAdmin/';
const our $BACKEND_ABS_PATH     => $FRONTEND_ABS_PATH . 'backend/'; # absolute path of this project (include trailing slash)
const our $BACKEND_REL_URL      => $FRONTEND_REL_URL . 'backend/'; # relative path of this project from http host (include trailing slash)

# DEFINE FTP CREDENTIALS HERE
# NOTE: This is for sending clinical documents
const our $ARIA_FTP_DIR         => '/mnt/VarianFILEDATA/Documents/Files/'; # clinical aria document directory
const our $MOSAIQ_FTP_DIR       => 'MOSAIQ_FTP_DIR_HERE'; # clinical mosaiq document directory
const our $FTP_LOCAL_DIR        => $BACKEND_ABS_PATH . 'clinical/documents'; # local clinical directory

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

    # ARIA 
    if ($sourceDBser eq 1) { 

        $sourceCredentials->{_dsn}      = $ARIA_DB_DSN;
        $sourceCredentials->{_user}     = $ARIA_DB_USERNAME;
        $sourceCredentials->{_password} = $ARIA_DB_PASSWORD;

    }

    # WaitRoomManagement
    elsif ($sourceDBser eq 2) {

        $sourceCredentials->{_dsn}          = $WRM_DB_DSN;
        $sourceCredentials->{_user}         = $WRM_DB_USERNAME;
        $sourceCredentials->{_password}     = $WRM_DB_PASSWORD;

    }
    
    # Mosaiq
    elsif ($sourceDBser eq 3) {

        $sourceCredentials->{_dsn}          = $MOSAIQ_DB_DSN;
        $sourceCredentials->{_user}         = $MOSAIQ_DB_USERNAME;
        $sourceCredentials->{_password}     = $MOSAIQ_DB_PASSWORD;

    }
    # Others
    # ...


    return $sourceCredentials; 
}

1; # end module 
