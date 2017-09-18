#!/usr/bin/perl

#---------------------------------------------------------------------------------
# A.Joseph 22-Jul-2014 ++ File: FTP.pm
#---------------------------------------------------------------------------------
# Perl module that creates an FTP class. It connects to a host server.
# This module calls a constructor to create an FTP class and then calls a
# subroutine to connect to the host server with the parameters given.
#
# It is assumed that the host, username and password will remain static 
# through the whole process so we pre-define those variables in the constructor.
# However, when creating a new FTP object, we pass the remote and local directories
# as arguments incase we wish to quickly change these parameters when modifying
# this module. 
#
# Although all these object variables are set within this module, I provide setter and getter
# subroutines in case the user wishes to change these variables.

package FTP; # Declare package name

use Configs; # Custom Configurations

#====================================================================================
# Constructor for our FTP class 
#====================================================================================
sub new
{
	my $class = shift;

	my $ftp = {
		_localdir		=> shift,
		_clinicaldir	=> shift,
	};

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $ftp, $class; 
	return $ftp;
}

#====================================================================================
# CSubroutine to set the FTP connection 
#====================================================================================
sub getFTPCredentials
{
	my ($sourceDBSer) = @_; # source database serial

	my $ftpObject = undef;

	my $ftpCredentials = Configs::fetchFTPCredentials($sourceDBSer);

	# if clinical directory exists, set information
	if (-d $ftpCredentials->{_clinicaldir}) {

		$ftpObject = new FTP(
			$ftpCredentials->{_localdir},
			$ftpCredentials->{_clinicaldir}
		);
	}

	return $ftpObject;
}

#======================================================================================
# Subroutine to set the ftp local directory
#======================================================================================
sub setFTPLocalDir
{
	my ($ftp, $localdir) = @_; # ftp object with provided directory in arguments
	$ftp->{_localdir} = $localdir; # set the directory
	return $ftp->{_localdir};
}

#======================================================================================
# Subroutine to set the ftp clinical directory
#======================================================================================
sub setFTPClinicalDir
{
	my ($ftp, $clinicaldir) = @_; # ftp object with provided directory in arguments
	$ftp->{_clinicaldir} = $clinicaldir; # set the directory
	return $ftp->{_clinicaldir};
}

#====================================================================================
# Subroutine to get the ftp local directory
#====================================================================================
sub getFTPLocalDir
{
	my ($ftp) = @_; # our ftp object
	return $ftp->{_localdir};
}

#====================================================================================
# Subroutine to get the ftp clinical directory
#====================================================================================
sub getFTPClinicalDir
{
	my ($ftp) = @_; # our ftp object
	return $ftp->{_clinicaldir};
}

# To exit/return always true (for the module itself)
1;
