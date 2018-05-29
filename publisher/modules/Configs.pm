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
use Cwd 'abs_path';
use JSON;

# Get directory path of this file
my $path_name = abs_path($0);
# Strip child directories to get root path
$path_name =~ s/publisher\/dataControl.pl//g;
my $config_file = $path_name . 'config.json';

# Get contents of config file
my $json;
{
    local $/; # Enable 'slurp' mode
    open my $file_handler, "<", $config_file
        or die "Could not open config file: $!";
    $json = <$file_handler>;
    close $file_handler;
}
# configurations in hash form
my $config = decode_json($json);

# DEFINE ARIA SERVER/DATABASE CREDENTIALS HERE
# NOTE: This works for a MicrosoftSQL (MSSQL) setup.
const our $ARIA_DB_HOST     => $config->{'databaseConfig'}{'aria'}{'host'};
const our $ARIA_DB_PORT     => $config->{'databaseConfig'}{'aria'}{'port'};
const our $ARIA_DB_DSN      => 'DBI:Sybase:server=' . $ARIA_DB_HOST . ';port=' . $ARIA_DB_PORT;
const our $ARIA_DB_USERNAME => $config->{'databaseConfig'}{'aria'}{'username'};
const our $ARIA_DB_PASSWORD => $config->{'databaseConfig'}{'aria'}{'password'};
 
# DEFINE OPAL DATABASE CREDENTIALS HERE
# NOTE: This works for a MySQL setup. 
const our $OPAL_DB_NAME         => $config->{'databaseConfig'}{'opal'}{'name'}; 
const our $OPAL_DB_HOST         => $config->{'databaseConfig'}{'opal'}{'host'};
const our $OPAL_DB_PORT         => $config->{'databaseConfig'}{'opal'}{'port'};
const our $OPAL_DB_DSN          => 'DBI:mysql:database=' . $OPAL_DB_NAME . ';host=' . $OPAL_DB_HOST . ';port=' . $OPAL_DB_PORT;
const our $OPAL_DB_USERNAME     => $config->{'databaseConfig'}{'opal'}{'username'};
const our $OPAL_DB_PASSWORD     => $config->{'databaseConfig'}{'opal'}{'password'};

# DEFINE: WRM DATABASE CREDENTIALS HERE
# NOTE: This works for a MySQL setup.
const our $WRM_DB_HOST             => $config->{'databaseConfig'}{'wrm'}{'host'};
const our $WRM_DB_PORT             => $config->{'databaseConfig'}{'wrm'}{'port'};
const our $WRM_DB_NAME             => $config->{'databaseConfig'}{'wrm'}{'name'};
const our $WRM_DB_DSN              => 'DBI:mysql:database=' . $WRM_DB_NAME . ';host=' . $WRM_DB_HOST . ';port=' . $WRM_DB_PORT;
const our $WRM_DB_USERNAME         => $config->{'databaseConfig'}{'wrm'}{'username'};
const our $WRM_DB_PASSWORD         => $config->{'databaseConfig'}{'wrm'}{'password'};

# DEFINE MOSAIQ SERVER/DATABASE CREDENTIALS HERE
# NOTE: This works for a MicrosoftSQL (MSSQL) setup.
const our $MOSAIQ_DB_HOST     => $config->{'databaseConfig'}{'mosaiq'}{'host'};
const our $MOSAIQ_DB_PORT     => $config->{'databaseConfig'}{'mosaiq'}{'port'};
const our $MOSAIQ_DB_DSN      => 'DBI:Sybase:host=' . $MOSAIQ_DB_HOST . ';port=' . $MOSAIQ_DB_PORT;
const our $MOSAIQ_DB_USERNAME => $config->{'databaseConfig'}{'mosaiq'}{'username'};
const our $MOSAIQ_DB_PASSWORD => $config->{'databaseConfig'}{'mosaiq'}{'password'};

# Environment-specific variables
const our $FRONTEND_ABS_PATH    => $config->{'pathConfig'}{'abs_path'};
const our $FRONTEND_REL_URL     => $config->{'pathConfig'}{'relative_url'};
const our $BACKEND_ABS_PATH     => $FRONTEND_ABS_PATH . 'publisher/'; # absolute path of this project (include trailing slash)
const our $BACKEND_REL_URL      => $FRONTEND_REL_URL . 'publisher/'; # relative path of this project from http host (include trailing slash)

# DEFINE FTP CREDENTIALS HERE
# NOTE: This is for sending clinical documents
const our $ARIA_FTP_DIR         => $config->{'clinicalDocumentPathConfig'}{'aria'}; # clinical aria document directory
const our $MOSAIQ_FTP_DIR       => $config->{'clinicalDocumentPathConfig'}{'mosaiq'}; # clinical mosaiq document directory
const our $FTP_LOCAL_DIR        =>  $BACKEND_ABS_PATH . 'clinical/documents'; # PDF directory 

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

        $sourceCredentials->{_dsn}          = $ARIA_DB_DSN;
        $sourceCredentials->{_user}         = $ARIA_DB_USERNAME;
        $sourceCredentials->{_password}     = $ARIA_DB_PASSWORD;

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

1; # end module 
