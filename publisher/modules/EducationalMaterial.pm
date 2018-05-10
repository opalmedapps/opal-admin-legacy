#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 06-May-2016 ++ File: EducationalMaterial.pm
#---------------------------------------------------------------------------------
# Perl module that creates an educational material class. This module calls a 
# constructor to create an edumat object that contains edumat information stored
# as object variables.
#
# There exists various subroutines to set and get edumat information and compare
# edumat information between two edumat objects.

package EducationalMaterial; # Declaring package name 

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
use EducationalMaterialControl; # Our custom educational material control module
use PushNotification;

#---------------------------------------------------------------------------------
# Connect to the databases
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our edumat class 
#====================================================================================
sub new
{
    my $class = shift;
    my $edumat = {
        _ser                => undef,
        _patientser         => undef,
        _edumatcontrolser   => undef,
        _readstatus         => undef,
        _cronlogser         => undef,
    };

    # bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $edumat, $class;
    return $edumat;
}

#====================================================================================
# Subroutine to set the Educational Material Serial
#====================================================================================
sub setEduMatSer
{
    my ($edumat, $ser) = @_; # edumat object with provided serial in args
    $edumat->{_ser} = $ser; # set the edumat ser
    return $edumat->{_ser};
}

#====================================================================================
# Subroutine to set the Educational Material Patient Serial
#====================================================================================
sub setEduMatPatientSer
{
    my ($edumat, $patientser) = @_; # edumat object with provided serial in args
    $edumat->{_patientser} = $patientser; # set the edumat ser
    return $edumat->{_patientser};
}

#====================================================================================
# Subroutine to set the Educational Material control Serial
#====================================================================================
sub setEduMatControlSer
{
    my ($edumat, $edumatcontrolser) = @_; # edumat object with provided serial in args
    $edumat->{_edumatcontrolser} = $edumatcontrolser; # set the edumat ser
    return $edumat->{_edumatcontrolser};
}

#====================================================================================
# Subroutine to set the Educational Material Read Status
#====================================================================================
sub setEduMatReadStatus
{
    my ($edumat, $readstatus) = @_; # edumat object with provided status in args
    $edumat->{_readstatus} = $readstatus; # set the edumat status
    return $edumat->{_readstatus};
}

#====================================================================================
# Subroutine to set the Educational Material Cron Log Serial
#====================================================================================
sub setEduMatCronLogSer
{
    my ($edumat, $cronlogser) = @_; # edumat object with provided serial in args
    $edumat->{_cronlogser} = $cronlogser; # set the edumat ser
    return $edumat->{_cronlogser};
}

#====================================================================================
# Subroutine to get the Educational Material Serial
#====================================================================================
sub getEduMatSer
{
	my ($edumat) = @_; # our edumat object
	return $edumat->{_ser};
}

#====================================================================================
# Subroutine to get the Educational Material Patient Serial
#====================================================================================
sub getEduMatPatientSer
{
	my ($edumat) = @_; # our edumat object
	return $edumat->{_patientser};
}

#====================================================================================
# Subroutine to get the Educational Material Control Serial
#====================================================================================
sub getEduMatControlSer
{
	my ($edumat) = @_; # our edumat object
	return $edumat->{_edumatcontrolser};
}

#====================================================================================
# Subroutine to get the Educational Material Read Status
#====================================================================================
sub getEduMatReadStatus
{
	my ($edumat) = @_; # our edumat object
	return $edumat->{_readstatus};
}

#====================================================================================
# Subroutine to get the Educational Material Cron Log Serial
#====================================================================================
sub getEduMatCronLogSer
{
    my ($edumat) = @_; # our edumat object
    return $edumat->{_cronlogser};
}

#======================================================================================
# Subroutine to publish educational materials
#======================================================================================
sub publishEducationalMaterials
{
    my ($cronLogSer, @patientList) = @_; # patient list and cron log serial from args

    my $today_date = strftime("%Y-%m-%d", localtime(time));
    my $now = Time::Piece->strptime(strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "%Y-%m-%d %H:%M:%S");

    # Date object of today at 8AM
    my $today_at_eightAM = Time::Piece->strptime($today_date . " 08:00:00", "%Y-%m-%d %H:%M:%S");
    # Date object of today at 8PM
    my $today_at_eightPM = Time::Piece->strptime($today_date . " 20:00:00", "%Y-%m-%d %H:%M:%S");

    # If we are not within the window to publish the messages then return
    if ( (($now - $today_at_eightAM) < 0) or (($now - $today_at_eightPM) > 0) ) {return;}

    # Retrieve all the controls
    my @eduMatControls = EducationalMaterialControl::getEduMatControlsMarkedForPublish();

    foreach my $Patient (@patientList) {

        my $patientSer          = $Patient->getPatientSer(); # get patient serial
        my $patientId           = $Patient->getPatientId(); # get patient id

        foreach my $EduMatControl (@eduMatControls) {

            my $eduMatControlSer    = $EduMatControl->getEduMatControlSer();
            my $eduMatFilters       = $EduMatControl->getEduMatControlFilters();

			# Fetch patient filters (if any)
            my @patientFilters = $eduMatFilters->getPatientFilters();

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

            # Fetch sex filter (if any) 
            my $sexFilter =  $eduMatFilters->getSexFilter();
            if ($sexFilter) {

				# toggle flag
				$isNonPatientSpecificFilterDefined = 1;

                my $patientSex = $Patient->getPatientSex();

                # Determine if the filter matches the current patient's sex
                if ($sexFilter ne $patientSex){
                    if (@patientFilters) {
                        # if the patient failed to match the sex filter but there are patient filters
                        # then we flag to check later if this patient matches with the patient filters
                        $isPatientSpecificFilterDefined = 1;
                    }
                    # else no patient filters were defined and failed to match the sex filter
                    # move on to the next educational material
                    else{next;}
                }
            }

            # Next fetch age group filter (if any)
            my $ageFilter = $eduMatFilters->getAgeFilter();
            if ($ageFilter) {

				# toggle flag
				$isNonPatientSpecificFilterDefined = 1;

                my $patientAge = $Patient->getPatientAge();
                # Determine if the patient's age falls within the age group
                if (($patientAge < $ageFilter->{_min} or $patientAge > $ageFilter->{_max})) {
                    if (@patientFilters) {
                        # if the patient failed to match the age filter but there are patient filters
                        # then we flag to check later if this patient matches with the patient filters
                        $isPatientSpecificFilterDefined = 1;
                    }
                    # else no patient filters were defined and failed to match the age filter
                    # move on to the next educational material
                    else{next;}
                }
            }

            my @aliasSerials = ();

            my @diagnosisNames = Diagnosis::getPatientsDiagnosesFromOurDB($patientSer);

            my @patientDoctors = PatientDoctor::getPatientsDoctorsFromOurDB($patientSer);
                
            # Fetch appointment filters (if any)
            my @appointmentFilters =  $eduMatFilters->getAppointmentFilters();
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
                    # Finding the existence of the patient appointment in the appointment filters
                    # If there is an intersection, then patient is so far part of this publishing educational material
                    if (!intersect(@appointmentFilters, @aliasSerials)) {
                       if (@patientFilters) {
                            # if the patient failed to match the appointment filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the appointment filter
                        # move on to the next educational material
                        else{next;}
                    } 
                }
            }

            # Fetch diagnosis filters (if any)
            my @diagnosisFilters = $eduMatFilters->getDiagnosisFilters();
            if (@diagnosisFilters) {

				# toggle flag
				$isNonPatientSpecificFilterDefined = 1;

                # if all diagnoses were selected as triggers then patient passes
                # else do further checks 
                unless ('ALL' ~~ @diagnosisFilters and @diagnosisNames) {
                    # Finding the intersection of the patient's diagnosis and the diagnosis filters
                    # If there is an intersection, then patient is so far part of this publishing educational material
                    if (!intersect(@diagnosisFilters, @diagnosisNames)) {
                        if (@patientFilters) {
                            # if the patient failed to match the diagnosis filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the diagnosis filter
                        # move on to the next educational material
                        else{next;}
                    }
                }
            }

            # Fetch doctor filters (if any)
            my @doctorFilters = $eduMatFilters->getDoctorFilters();
            if (@doctorFilters) {

				# toggle flag
				$isNonPatientSpecificFilterDefined = 1;

                # if all doctors were selected as triggers then patient passes
                # else do further checks 
                unless ('ALL' ~~ @doctorFilters and @patientDoctors) {
                    # Finding the intersection of the patient's doctor(s) and the doctor filters
                    # If there is an intersection, then patient is so far part of this publishing educational material
                    if (!intersect(@doctorFilters, @patientDoctors)) {
                        if (@patientFilters) {
                            # if the patient failed to match the doctor filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the doctor filter
                        # move on to the next educational material
                        else{next;}
                    } 
                }
            }

            # Fetch resource filters (if any)
            my @resourceFilters = $eduMatFilters->getResourceFilters();
            if (@resourceFilters) {

                # toggle flag
                $isNonPatientSpecificFilterDefined = 1;

                # if all resources were selected as triggers then patient passes
                # else do further checks 
                unless ('ALL' ~~ @resourceFilters and @patientResources) {
                    # Finding the intersection of the patient resource(s) and the resource filters
                    # If there is an intersection, then patient is so far part of this publishing educational material
                    if (!intersect(@resourceFilters, @patientResources)) {
                        if (@patientFilters) {
                            # if the patient failed to match the resource filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the resource filter
                        # move on to the next announcement
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
                    if ($patientId ~~ @patientFilters or 'ALL' ~~ @patientFilters) {
                        $patientPassed = 1;
                    }
                    else {next;}
                }
			}

            if ($isNonPatientSpecificFilterDefined eq 1 or $isPatientSpecificFilterDefined eq 1 or ($isNonPatientSpecificFilterDefined eq 0 and $patientPassed eq 1)) {
                # If we've reached this point, we've passed all catches (filter restrictions). We make
                # an educational material object, check if it exists already in the database. If it does 
                # this means the edumat has already been publish to the patient. If it doesn't
                # exist then we publish to the patient (insert into DB).
                $eduMat = new EducationalMaterial();

                # set the necessary values
                $eduMat->setEduMatPatientSer($patientSer);
                $eduMat->setEduMatControlSer($eduMatControlSer);
                $eduMat->setEduMatCronLogSer($cronLogSer);

                if (!$eduMat->inOurDatabase()) {
        
                    $eduMat = $eduMat->insertEducationalMaterialIntoOurDB();
        
                    # send push notification
                    my $eduMatSer = $eduMat->getEduMatSer();
                    my $patientSer = $eduMat->getEduMatPatientSer();
                    PushNotification::sendPushNotification($patientSer, $eduMatSer, 'EducationalMaterial');

                }
            }
        } # End forEach Educational Material Control   

    } # End forEach Patient

}

#======================================================================================
# Subroutine to check if our edumat exists in our MySQL db
#	@return: edumat object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
    my ($edumat) = @_; # our edumat object in args

    my $patientser          = $edumat->getEduMatPatientSer();
    my $edumatcontrolser    = $edumat->getEduMatControlSer();

    my $serInDB = 0; # false by default. Will be true if edumat exists
    my $ExistingEduMat = (); # data to be entered if edumat exists

    # Other variables, if edumat exists
    my ($readstatus, $cronlogser);

    my $inDB_sql = "
        SELECT
            em.EducationalMaterialSerNum,
            em.ReadStatus,
            em.CronLogSerNum
        FROM
            EducationalMaterial em
        WHERE
            em.PatientSerNum                    = '$patientser'
        AND em.EducationalMaterialControlSerNum = '$edumatcontrolser'
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

        $ExistingEduMat = new EducationalMaterial(); # initialize

        # set params
        $ExistingEduMat->setEduMatSer($serInDB);
        $ExistingEduMat->setEduMatPatientSer($patientser);
        $ExistingEduMat->setEduMatControlSer($edumatcontrolser);
        $ExistingEduMat->setEduMatReadStatus($readstatus);
        $ExistingEduMat->setEduMatCronLogSer($cronlogser);

        return $ExistingEduMat; # this is true (ie. edumat exists. return object)
    }

    else {return $ExistingEduMat}; # this is false (ie. edumat DNE)
}

#======================================================================================
# Subroutine to insert our educational material in our database
#======================================================================================
sub insertEducationalMaterialIntoOurDB
{
    my ($edumat) = @_; # our edumat object

    my $patientser          = $edumat->getEduMatPatientSer();
    my $edumatcontrolser    = $edumat->getEduMatControlSer();
    my $cronlogser          = $edumat->getEduMatCronLogSer();

    my $insert_sql = "
        INSERT INTO 
            EducationalMaterial (
                PatientSerNum,
                CronLogSerNum,
                EducationalMaterialControlSerNum,
                DateAdded
            )
        VALUES (
            '$patientser',
            '$cronlogser',
            '$edumatcontrolser',
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
	$edumat->setEduMatSer($ser);
	
	return $edumat;
}


# Exit smoothly 
1;

