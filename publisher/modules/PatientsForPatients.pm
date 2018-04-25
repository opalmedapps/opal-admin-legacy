#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 25-Jan-2017 ++ File: PatientsForPatients.pm
#---------------------------------------------------------------------------------
# Perl module that creates a p4p class. This module calls a constructor to
# create a p4p object that contains p4p information stored as object
# variables.
#
# There exists various subroutines to set and get p4p information and compare
# p4p information between two p4p objects.

package PatientsForPatients; # Declaring package name

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
use PushNotification;

#---------------------------------------------------------------------------------
# Connect to the databases
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our PatsForPats class 
#====================================================================================
sub new
{
    my $class = shift;
    my $patsforpats = {
        _ser            => undef,
        _patientser     => undef,
        _postcontrolser => undef,
        _readstatus     => undef,
        _cronlogser     => undef,
    };

    # bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $patsforpats, $class;
    return $patsforpats;
}

#====================================================================================
# Subroutine to set the PatsForPats Serial
#====================================================================================
sub setPatsForPatsSer
{
    my ($patsforpats, $ser) = @_; # patsforpats object with provided serial in args
    $patsforpats->{_ser} = $ser; # set the patsforpats ser
    return $patsforpats->{_ser};
}

#====================================================================================
# Subroutine to set the PatsForPats Patient Serial
#====================================================================================
sub setPatsForPatsPatientSer
{
    my ($patsforpats, $patientser) = @_; # patsforpats object with provided serial in args
    $patsforpats->{_patientser} = $patientser; # set the patsforpats ser
    return $patsforpats->{_patientser};
}

#====================================================================================
# Subroutine to set the PatsForPats Post Control Serial
#====================================================================================
sub setPatsForPatsPostControlSer
{
    my ($patsforpats, $postcontrolser) = @_; # patsforpats object with provided serial in args
    $patsforpats->{_postcontrolser} = $postcontrolser; # set the patsforpats ser
    return $patsforpats->{_postcontrolser};
}

#====================================================================================
# Subroutine to set the PatsForPats Read Status
#====================================================================================
sub setPatsForPatsReadStatus
{
    my ($patsforpats, $readstatus) = @_; # patsforpats object with provided status in args
    $patsforpats->{_readstatus} = $readstatus; # set the patsforpats status
    return $patsforpats->{_readstatus};
}

#====================================================================================
# Subroutine to set the PatsForPats Cron Log Serial
#====================================================================================
sub setPatsForPatsCronLogSer
{
    my ($patsforpats, $cronlogser) = @_; # patsforpats object with provided serial in args
    $patsforpats->{_cronlogser} = $cronlogser; # set the patsforpats ser
    return $patsforpats->{_cronlogser};
}

#====================================================================================
# Subroutine to get the PatsForPats Serial
#====================================================================================
sub getPatsForPatsSer
{
	my ($patsforpats) = @_; # our patsforpats object
	return $patsforpats->{_ser};
}

#====================================================================================
# Subroutine to get the PatsForPats Patient Serial
#====================================================================================
sub getPatsForPatsPatientSer
{
	my ($patsforpats) = @_; # our patsforpats object
	return $patsforpats->{_patientser};
}

#====================================================================================
# Subroutine to get the PatsForPats Post Control Serial
#====================================================================================
sub getPatsForPatsPostControlSer
{
	my ($patsforpats) = @_; # our patsforpats object
	return $patsforpats->{_postcontrolser};
}

#====================================================================================
# Subroutine to get the PatsForPats Read Status
#====================================================================================
sub getPatsForPatsReadStatus
{
	my ($patsforpats) = @_; # our patsforpats object
	return $patsforpats->{_readstatus};
}

#====================================================================================
# Subroutine to get the PatsForPats Cron Log Serial
#====================================================================================
sub getPatsForPatsCronLogSer
{
    my ($patsforpats) = @_; # our patsforpats object
    return $patsforpats->{_cronlogser};
}

#======================================================================================
# Subroutine to publish patsforpats
#======================================================================================
sub publishPatientsForPatients
{
    my ($cronLogSer, @patientList) = @_; # patient list and cron log serial from args

    my $today_date = strftime("%Y-%m-%d", localtime(time));
    my $now = Time::Piece->strptime(strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "%Y-%m-%d %H:%M:%S");

    # Date object of today at 8AM
    my $today_at_eightAM = Time::Piece->strptime($today_date . " 08:00:00", "%Y-%m-%d %H:%M:%S");
    # Date object of today at 8PM
    my $today_at_eightPM = Time::Piece->strptime($today_date . " 20:00:00", "%Y-%m-%d %H:%M:%S");

    # If we are not within the window to publish patsforpats then return
    if ( (($now - $today_at_eightAM) < 0) or (($now - $today_at_eightPM) > 0) ) {return;}

    my @patsForPatsControls = PostControl::getPostControlsMarkedForPublish('Patients For Patients');

    foreach my $Patient (@patientList) {

        my $patientSer          = $Patient->getPatientSer(); # get patient serial
		my $patientId 			= $Patient->getPatientId(); # get patient id 

        foreach my $PostControl (@patsForPatsControls) {

            my $postControlSer          = $PostControl->getPostControlSer();
            my $postFilters             = $PostControl->getPostControlFilters();

			# Fetch patient filters (if any)
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

            my @expressionNames = ();

            my @diagnosisNames = Diagnosis::getPatientsDiagnosesFromOurDB($patientSer);

            my @patientDoctors = PatientDoctor::getPatientsDoctorsFromOurDB($patientSer);
                
            # Fetch expression filters (if any)
            my @expressionFilters =  $postFilters->getAppointmentFilters();
            if (@expressionFilters) {
  
				# toggle flag
				$isNonPatientSpecificFilterDefined = 1;
               
                # Retrieve the patient appointment(s) if one (or more) lands within one day of today
                my @patientAppointments = Appointment::getPatientsAppointmentsFromDateInOurDB($patientSer, $postPublishDate, 0);

                # we build all possible expression names, and diagnoses for each appointment found
                foreach my $appointment (@patientAppointments) {

                    my $expressionSer = $appointment->getApptAliasExpressionSer();
                    my $expressionName = Alias::getExpressionNameFromOurDB($expressionSer);
                    push(@expressionNames, $expressionName) unless grep{$_ == $expressionName} @expressionNames;

                }

                 # if all appointments were selected as triggers then patient passes
                # else do further checks 
                unless ('ALL' ~~ @appointmentFilters and @expressionNames) {

                    # Finding the existence of the patient expressions in the expression filters
                    # If there is an intersection, then patient is so far part of this publishing P4P
                    if (!intersect(@expressionFilters, @expressionNames)) {
                       if (@patientFilters) {
                            # if the patient failed to match the expression filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the expression filter
                        # move on to the next P4P
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
                    # If there is an intersection, then patient is so far part of this publishing P4P
                    if (!intersect(@diagnosisFilters, @diagnosisNames)) {
                        if (@patientFilters) {
                            # if the patient failed to match the diagnosis filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the diagnosis filter
                        # move on to the next P4P
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
                    # If there is an intersection, then patient is so far part of this publishing P4P
                    if (!intersect(@doctorFilters, @patientDoctors)) {
                        if (@patientFilters) {
                            # if the patient failed to match the doctor filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the doctor filter
                        # move on to the next P4P
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
                    # Finding the intersection of the patient's resource(s) and the resource filters
                    # If there is an intersection, then patient is so far part of this publishing P4P
                    if (!intersect(@resourcesFilters, @patientResources)) {
                        if (@patientFilters) {
                            # if the patient failed to match the resource filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the resource filter
                        # move on to the next P4P
                        else{next;}
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
                    if ($patientId ~~ @patientFilters or 'ALL' ~~ @patientFilters) {
                        $patientPassed = 1;
                    }
                    else {next;}
                }
            }

            if ($isNonPatientSpecificFilterDefined eq 1 or $isPatientSpecificFilterDefined eq 1 or ($isNonPatientSpecificFilterDefined eq 0 and $patientPassed eq 1)) {
                # If we've reached this point, we've passed all catches (filter restrictions). We make
                # an PatientsForPatients object, check if it exists already in the database. If it does 
                # this means the PatientsForPatients has already been published to the patient. If it doesn't
                # exist then we publish to the patient (insert into DB).
                $patsforpats = new PatientsForPatients();

                # set the necessary values
                $patsforpats->setPatsForPatsPatientSer($patientSer);
                $patsforpats->setPatsForPatsPostControlSer($postControlSer);
                $patsforpats->setPatsForPatsCronLogSer($cronLogSer);

                if (!$patsforpats->inOurDatabase()) {

                    $patsforpats = $patsforpats->insertPatsForPatsIntoOurDB();

                    # send push notification
                    my $patsforpatsSer = $patsforpats->getPatsForPatsSer();
                    my $patientSer = $patsforpats->getPatsForPatsPatientSer();
                    PushNotification::sendPushNotification($patientSer, $patsforpatsSer, 'PatientsForPatients');

                }
            }

        } # End forEach PostControl   

    } # End forEach Patient

}

#======================================================================================
# Subroutine to check if our patsforpats exists in our MySQL db
#	@return: patsforpats object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
    my ($patsforpats) = @_; # our patsforpats object
    my $patientser = $patsforpats->getPatsForPatsPatientSer(); # get patient serial
    my $postcontrolser = $patsforpats->getPatsForPatsPostControlSer(); # get post control serial

    my $serInDB = 0; # false by default. Will be true if patsforpats exists
    my $ExistingPatsForPats = (); # data to be entered if patsforpats exists

    # Other variables, if patsforpats exists
    my ($readstatus, $cronlogser);

    my $inDB_sql = "
        SELECT
            pfp.PatientsForPatientsSerNum,
            pfp.ReadStatus,
            pfp.CronLogSerNum
        FROM
            PatientsForPatients pfp
        WHERE
            pfp.PatientSerNum = '$patientser'
        AND pfp.PostControlSerNum = '$postcontrolser'
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
        $cronlogser = $data[2];
    }

    if ($serInDB) {

        $ExistingPatsForPats = new PatientsForPatients(); # initialize object

        # set parameters
        $ExistingPatsForPats->setPatsForPatsSer($serInDB); 
        $ExistingPatsForPats->setPatsForPatsPatientSer($patientser);
        $ExistingPatsForPats->setPatsForPatsPostControlSer($postcontrolser); 
        $ExistingPatsForPats->setPatsForPatsReadStatus($readstatus);
        $ExistingPatsForPats->setPatsForPatsCronLogSer($cronLogSer);

        return $ExistingPatsForPats; # this is true (ie. patsforpats exists, return object)
    }

    else {return $ExistingPatsForPats;} # this is false (ie. patsforpats DNE, return empty)
}

#======================================================================================
# Subroutine to insert our patsforpats info in our database
#======================================================================================
sub insertPatsForPatsIntoOurDB
{
    my ($patsforpats) = @_; # our patsforpats object 

    my $patientser      = $patsforpats->getPatsForPatsPatientSer();
    my $postcontrolser  = $patsforpats->getPatsForPatsPostControlSer();
    my $cronlogser      = $patsforpats->getPatsForPatsCronLogSer();

    my $insert_sql = "
        INSERT INTO
            PatientsForPatients (
                PatientSerNum,
                CronLogSerNum,
                PostControlSerNum,
                DateAdded
            )
        VALUES (
            '$patientser',
            '$cronlogser',
            '$postcontrolser',
            NOW()
        )
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the serial
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the Serial in our object
	$patsforpats->setPatsForPatsSer($ser);
	
	return $patsforpats;
}


