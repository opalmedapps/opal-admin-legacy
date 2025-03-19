#!/usr/bin/perl
#---------------------------------------------------------------------------------
# Limin Liu 2023-04-03 ++ File: Api.pm
#---------------------------------------------------------------------------------
# Perl module that creates an api class. This module calls a constructor to
# create an api object that provides api functions


package Api; # Declare package name

use LWP::UserAgent; # for requests
use JSON;

#---------------------------------------------------------------------------------
# env config
#---------------------------------------------------------------------------------
use Configs; # Configs.pm

my $newBackendHost = Configs::fetchNewBackendHost();
my $newBackendToken = Configs::fetchNewBackendToken();
#====================================================================================
# function calls the new backend api patients/legacy/<int:legacy_id>/
#====================================================================================
sub apiPatientCaregiverDevices
{
    my ($patientSerNum) = @_; # patient ser number

	$url = $newBackendHost . "/api/patients/legacy/$patientSerNum/caregiver-devices/";

    my $ua = LWP::UserAgent->new;
    my $res = $ua->get(
        $url,
        "Authorization" => "Token ".$newBackendToken,
    );

    # return JSON string of the response
    if ($res->is_success) {
        return $res->content;
    } else {
        return '{"error": "apiPatientCaregiverDevices request failed"}';
    }
}

# To exit/return always true (for the module itself)
1;
