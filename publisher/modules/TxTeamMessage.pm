# SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

#---------------------------------------------------------------------------------
# A.Joseph 06-May-2016 ++ File: TxTeamMessage.pm
#---------------------------------------------------------------------------------
# Perl module that creates a treatment team message (ttm) class. This module calls
# a constructor to create a ttm object that contains ttm information stored as
# object variables.
#
# There exists various subroutines to set and get ttm information and compare ttm
# information between two ttm objects.
#

package TxTeamMessage; # Declaring package name

use Database; # Our custom database module
use Time::Piece; # perl module
use Array::Utils qw(:all);
use POSIX; # perl module

use Patient; # Our custom patient module
use PatientDoctor; # PatientDoctor.pm
use Alias; # Alias.pm
use ResourceAppointment; # ResourceAppointment.pm
use Diagnosis; # Diagnosis.pm
use Appointment; # Our custom appointment module
use Filter; # Our custom filter module
use PostControl; # Our custom post control module

#---------------------------------------------------------------------------------
# Connect to the databases
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our ttm class
#====================================================================================
sub new
{
    my $class = shift;
    my $ttm = {
        _ser            => undef,
        _patientser     => undef,
        _postcontrolser => undef,
        _readstatus     => undef,
    };

    # bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $ttm, $class;
    return $ttm;
}

#====================================================================================
# Subroutine to set the Treatment Team Message Serial
#====================================================================================
sub setTTMSer
{
    my ($ttm, $ser) = @_; # ttm object with provided serial in args
    $ttm->{_ser} = $ser; # set the ttm ser
    return $ttm->{_ser};
}

#====================================================================================
# Subroutine to set the Treatment Team Message Patient Serial
#====================================================================================
sub setTTMPatientSer
{
    my ($ttm, $patientser) = @_; # ttm object with provided serial in args
    $ttm->{_patientser} = $patientser; # set the ttm ser
    return $ttm->{_patientser};
}

#====================================================================================
# Subroutine to set the Treatment Team Message Post Control Serial
#====================================================================================
sub setTTMPostControlSer
{
    my ($ttm, $postcontrolser) = @_; # ttm object with provided serial in args
    $ttm->{_postcontrolser} = $postcontrolser; # set the ttm ser
    return $ttm->{_postcontrolser};
}

#====================================================================================
# Subroutine to set the Treatment Team Message Read Status
#====================================================================================
sub setTTMReadStatus
{
    my ($ttm, $readstatus) = @_; # ttm object with provided status in args
    $ttm->{_readstatus} = $readstatus; # set the ttm status
    return $ttm->{_readstatus};
}

#====================================================================================
# Subroutine to get the Treatment Team Message Serial
#====================================================================================
sub getTTMSer
{
	my ($ttm) = @_; # our ttm object
	return $ttm->{_ser};
}

#====================================================================================
# Subroutine to get the Treatment Team Message Patient Serial
#====================================================================================
sub getTTMPatientSer
{
	my ($ttm) = @_; # our ttm object
	return $ttm->{_patientser};
}

#====================================================================================
# Subroutine to get the Treatment Team Message Post Control Serial
#====================================================================================
sub getTTMPostControlSer
{
	my ($ttm) = @_; # our ttm object
	return $ttm->{_postcontrolser};
}

#====================================================================================
# Subroutine to get the Treatment Team Message Read Status
#====================================================================================
sub getTTMReadStatus
{
	my ($ttm) = @_; # our ttm object
	return $ttm->{_readstatus};
}

#======================================================================================
# Subroutine to publish tx team message
#======================================================================================
sub publishTxTeamMessages
{
    my (@patientList) = @_; # patient list and cron log serial from args

    my $today_date = strftime("%Y-%m-%d", localtime(time));
    my $now = Time::Piece->strptime(strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "%Y-%m-%d %H:%M:%S");

    # Check for any new updates from the main cron control
	PostControl::CheckPostControlsMarkedForPublishModularCron('Treatment Team Message');

    my @txTeamMessageControls = PostControl::getPostControlsMarkedForPublishModularCron('Treatment Team Message');

    foreach my $Patient (@patientList) {

        my $patientSer          = $Patient->getPatientSer(); # get patient serial

        foreach my $PostControl (@txTeamMessageControls) {

            my $postControlSer          = $PostControl->getPostControlSer();
            my $postFilters             = $PostControl->getPostControlFilters();

			my @patientFilters = $postFilters->getPatientFilters();

			# We will flag whether there are patient filters or other (non-patient) filters
			# The reason is that the patient filter will combine as an OR with the non-patient filters
			# If any of the non-patient filters exist, all non-patient filters combine in an AND (i.e. intersection)
            # However, we don't want to lose the exception that a patient filter has been defined
			# If there is a patient filter defined, then we only send the content to the patients
			# selected in the filter UNLESS other non-patient filters have been defined. In that case,
			# we send to the patients defined in the patient filters AND to the patients that pass
			# in the non-patient filters
			my $isNonPatientSpecificFilterDefined = 0;
            my $isPatientSpecificFilterDefined = 0;
            my $patientPassed = 0;

            my @aliasSerials = ();

            my @diagnosisNames = Diagnosis::getPatientsDiagnosesFromOurDB($patientSer);

            my @patientDoctors = PatientDoctor::getPatientsDoctorsFromOurDB($patientSer);

            # Fetch appointment filters (if any)
            my @appointmentFilters =  $postFilters->getAppointmentFilters();
            if (@appointmentFilters) {

				# toggle flag
				$isNonPatientSpecificFilterDefined = 1;

                # Retrieve the patient appointment(s) if one (or more) lands within one day of today
                my @patientAppointments = Appointment::getPatientsAppointmentsFromDateInOurDB($patientSer, $today_date, 1);

                # we build all possible appointment and diagnoses for each appointment found
                foreach my $appointment (@patientAppointments) {

                    my $expressionSer = $appointment->getApptAliasExpressionSer();
                    my $aliasSer = Alias::getAliasFromOurDB($expressionSer);
                    push(@aliasSerials, $aliasSer) unless grep{$_ == $aliasSer} @aliasSerials;

                }
                # if all appointments were selected as triggers then patient passes
                # else do further checks
                unless ('ALL' ~~ @appointmentFilters and @aliasSerials) {

    				# Finding the existence of the patient appointments in the appointment filters
                    # If there is an intersection, then patient is so far part of this publishing tx team message
                    if (!intersect(@appointmentFilters, @aliasSerials)) {
                       if (@patientFilters) {
                            # if the patient failed to match the appointment filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the appointment filter
                        # move on to the next treatment team message
                        else{next;}
                    }
                }
            }

            # Fetch diagnosis filters (if any)
            my @diagnosisFilters = $postFilters->getDiagnosisFilters();
            if (@diagnosisFilters) {

                # toggle flag
				$isNonPatientSpecificFilterDefined = 1;

                # if all diagnoses were selected as triggers then patient passes
                # else do further checks
                unless ('ALL' ~~ @diagnosisFilters and @diagnosisNames) {
                    # Finding the intersection of the patient's diagnosis and the diagnosis filters
                    # If there is an intersection, then patient is so far part of this publishing tx team message
                    if (!intersect(@diagnosisFilters, @diagnosisNames)) {
                        if (@patientFilters) {
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the diagnosis filter
                        # move on to the next treatment team message
                        else{next;}
                    }
                }
            }

            # Fetch doctor filters (if any)
            my @doctorFilters = $postFilters->getDoctorFilters();
            if (@doctorFilters) {

                # toggle flag
				$isNonPatientSpecificFilterDefined = 1;

                # if all doctors were selected as triggers then patient passes
                # else do further checks
                unless ('ALL' ~~ @doctorFilters and @patientDoctors) {
                    # Finding the intersection of the patient's doctor(s) and the doctor filters
                    # If there is an intersection, then patient is so far part of this publishing tx team message
                    if (!intersect(@doctorFilters, @patientDoctors)) {
                        if (@patientFilters) {
                            # if the patient failed to match the doctor filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the doctor filter
                        # move on to the next treatment team message
                        else{next;}
                    }
                }
            }

            # Fetch resource filters (if any)
            my @resourceFilters = $postFilters->getResourceFilters();
            if (@resourceFilters) {

                # toggle flag
                $isNonPatientSpecificFilterDefined = 1;

                # if all resources were selected as triggers then patient passes
                # else do further checks
                unless ('ALL' ~~ @resourceFilters and @patientResources) {
                    # Finding the intersection of the patient resource(s) and the resource filters
                    # If there is an intersection, then patient is so far part of this publishing tx team message
                    if (!intersect(@resourceFilters, @patientResources)) {
                        if (@patientFilters) {
                            # if the patient failed to match the resource filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the resource filter
                        # move on to the next tx team message
                        else{
                            next;
                        }
                    }
                }
            }

			# We look into whether any patient-specific filters have been defined
			# If we enter this if statement, then we check if that patient is in that list
            if (@patientFilters) {

                # if the patient-specific flag was enabled then it means this patient failed
                # one of the filters above
                # OR if the non patient specific flag was disabled then there were no filters defined above
                # and this is the last test to see if this patient passes
                if ($isPatientSpecificFilterDefined eq 1 or $isNonPatientSpecificFilterDefined eq 0) {
    				# Finding the existence of the patient in the patient-specific filters
    				# If the patient exists, or all patients were selected as triggers,
                    # then patient passes else move on to next patient
                    if ($patientSer ~~ @patientFilters or 'ALL' ~~ @patientFilters) {
                        $patientPassed = 1;
                    }
                    else {next;}
                }
			}

            if ($isNonPatientSpecificFilterDefined eq 1 or $isPatientSpecificFilterDefined eq 1 or ($isNonPatientSpecificFilterDefined eq 0 and $patientPassed eq 1)) {

                # If we've reached this point, we've passed all catches (filter restrictions). We make
                # a tx team message object, check if it exists already in the database. If it does
                # this means the message has already been publish to the patient. If it doesn't
                # exist then we publish to the patient (insert into DB).
                $txTeamMessage = new TxTeamMessage();

                # set the necessary values
                $txTeamMessage->setTTMPatientSer($patientSer);
                $txTeamMessage->setTTMPostControlSer($postControlSer);

                if (!$txTeamMessage->inOurDatabase()) {

                    $txTeamMessage = $txTeamMessage->insertTxTeamMessageIntoOurDB();

                    # send push notification
                    my $txTeamMessageSer = $txTeamMessage->getTTMSer();
                    my $patientSer = $txTeamMessage->getTTMPatientSer();
                    PushNotification::sendPushNotification($patientSer, $txTeamMessageSer, 'TxTeamMessage');

                }
            }

        } # End forEach PostControl

    } # End forEach Patient

}

#======================================================================================
# Subroutine to check if our ttm exists in our MySQL db
#	@return: ttm object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
    my ($txTeamMessage) = @_; # our ttm object in args

    my $patientser = $txTeamMessage->getTTMPatientSer(); # get patient serial
    my $postcontrolser = $txTeamMessage->getTTMPostControlSer(); # get post control serial

    my $serInDB = 0; # false by default. Will be true if message exists
    my $ExistingTTM = (); # data to be entered if ttm exists

    # Other variables, if ttm exists
    my ($readstatus);

    my $inDB_sql = "
        SELECT
            ttm.TxTeamMessageSerNum,
            ttm.ReadStatus
        FROM
            TxTeamMessage ttm
        WHERE
            ttm.PatientSerNum       = '$patientser'
        AND ttm.PostControlSerNum   = '$postcontrolser'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        $serInDB    = $data[0];
        $readstatus = $data[1];
    }

    if ($serInDB) {

        $ExistingTTM = new TxTeamMessage(); # initialize object

        # set params
        $ExistingTTM->setTTMSer($serInDB);
        $ExistingTTM->setTTMPatientSer($patientser);
        $ExistingTTM->setTTMPostControlSer($postcontrolser);
        $ExistingTTM->setTTMReadStatus($readstatus);

        return $ExistingTTM; # this is true (ie. ttm exists. return object)

    }

    else {return $ExistingTTM;} # this is false (ie. ttm does not exist)

}

#======================================================================================
# Subroutine to insert our treatment team message info in our database
#======================================================================================
sub insertTxTeamMessageIntoOurDB
{
    my ($txTeamMessage) = @_; # our ttm object

    my $patientser      = $txTeamMessage->getTTMPatientSer();
    my $postcontrolser  = $txTeamMessage->getTTMPostControlSer();

    my $insert_sql = "
        INSERT INTO
            TxTeamMessage (
                PatientSerNum,
                PostControlSerNum,
                DateAdded,
                ReadStatus
            )
        VALUES (
            '$patientser',
            '$postcontrolser',
            NOW(),
            0
        )
    ";

    #print "$insert_sql\n";
    # prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the serial
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the Serial in our object
	$txTeamMessage->setTTMSer($ser);

	return $txTeamMessage;
}


# Exit smoothly
1;
