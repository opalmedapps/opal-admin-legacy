#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 29-Sept-2017 ++ File: LegacyQuestionnaire.pm
#---------------------------------------------------------------------------------
# Perl module that creates a legacy questionnaire class. This module calls a 
# constructor to create a legacy questionnaire object that contains legacy questionnaire information stored
# as object variables.
#
# There exists various subroutines to set and get questionnaire information and compare
# legacy questionnaire information between two questionnaire objects.

package LegacyQuestionnaire; # Declaring package name

use Database; # Our custom database module
use Time::Piece; # perl module
use Array::Utils qw(:all);
use POSIX; # perl module
use Data::Dumper;

use Patient; # Our custom patient module 
use Filter; # Our custom filter module
use Appointment; # Our custom appointment module
use Alias; # Our custom alias module
use Diagnosis; # Our custom diagnosis module
use PatientDoctor; # Our custom patient doctor module 
use PushNotification; # Our custom push notification module

#---------------------------------------------------------------------------------
# Connect to the databases
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our questionnaire class 
#====================================================================================
sub new
{
    my $class = shift;
	my $questionnaire = {
		_ser 						=> undef,
		_patientser 				=> undef,
		_questionnairecontrolser 	=> undef,
		_filters 					=> undef,
        _cronlogser                 => undef,
	}; 

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $questionnaire, $class;
    return $questionnaire;
}

#====================================================================================
# Subroutine to set the Legacy Questionnaire Serial
#====================================================================================
sub setLegacyQuestionnaireSer
{
    my ($questionnaire, $ser) = @_; # questionnaire object with provided serial in args
    $questionnaire->{_ser} = $ser; # set the questionnaire ser
    return $questionnaire->{_ser};
}

#====================================================================================
# Subroutine to set the Legacy Questionnaire Patient Serial
#====================================================================================
sub setLegacyQuestionnairePatientSer
{
    my ($questionnaire, $patientser) = @_; # questionnaire object with provided serial in args
    $questionnaire->{_patientser} = $patientser; # set the ser
    return $questionnaire->{_patientser};
}

#====================================================================================
# Subroutine to set the Legacy Questionnaire Control Serial
#====================================================================================
sub setLegacyQuestionnaireControlSer
{
    my ($questionnaire, $questionnairecontrolser) = @_; # questionnaire object with provided serial in args
    $questionnaire->{_questionnairecontrolser} = $questionnairecontrolser; # set the ser
    return $questionnaire->{_questionnairecontrolser};
}

#====================================================================================
# Subroutine to set the Legacy Questionnaire Filters
#====================================================================================
sub setLegacyQuestionnaireFilters
{
    my ($questionnaire, $filters) = @_; # questionnaire object with provided filters in args
    $questionnaire->{_filters} = $filters; # set the filters
    return $questionnaire->{_filters};
}

#====================================================================================
# Subroutine to set the Legacy Questionnaire Cron Log Serial
#====================================================================================
sub setLegacyQuestionnaireCronLogSer
{
    my ($questionnaire, $cronlogser) = @_; # questionnaire object with provided serial in args
    $questionnaire->{_cronlogser} = $cronlogser; # set the questionnaire ser
    return $questionnaire->{_cronlogser};
}

#====================================================================================
# Subroutine to get the Legacy Questionnaire Serial
#====================================================================================
sub getLegacyQuestionnaireSer
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_ser};
}

#====================================================================================
# Subroutine to get the Legacy Questionnaire Patient Serial
#====================================================================================
sub getLegacyQuestionnairePatientSer
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_patientser};
}

#====================================================================================
# Subroutine to get the Legacy Questionnaire Serial
#====================================================================================
sub getLegacyQuestionnaireControlSer
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_questionnairecontrolser};
}

#====================================================================================
# Subroutine to get the Legacy Questionnaire Filters
#====================================================================================
sub getLegacyQuestionnaireFilters
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_filters};
}

#====================================================================================
# Subroutine to get the Legacy Questionnaire Cron Log Serial
#====================================================================================
sub getLegacyQuestionnaireCronLogSer
{
    my ($questionnaire) = @_; # our questionnaire object
    return $questionnaire->{_cronlogser};
}

#====================================================================================
# Subroutine to publish legacy questionnaires
#====================================================================================
sub publishLegacyQuestionnaires
{
	my ($cronLogSer, @patientList) = @_; # patient list and cron log serial from args

	# Retrieve all the legacy questionnaire controls
	my @legacyQuestionnaireControls = getLegacyQuestionnaireControlsMarkedForPublish(); 

	foreach my $Patient (@patientList) {

		my $patientSer 	= $Patient->getPatientSer();
		my $patientId  	= $Patient->getPatientId();

		foreach my $QuestionnaireControl (@legacyQuestionnaireControls) {

			my $questionnaireControlSer 	= $QuestionnaireControl->getLegacyQuestionnaireControlSer();
			my $questionnaireFilters 		= $QuestionnaireControl->getLegacyQuestionnaireFilters();

			# Fetch patient filters (if any)
			my @patientFilters = $questionnaireFilters->getPatientFilters();

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
            # We flag whether this legacy questionnaire is a recurring event for recurring filters 
            # (appointment, appointment status, checkin status, frequency events)
            # If there are recurring filters, then we flag = 1. This will allow publishing the same
            # legacy questionnaire more than once based on whether or not we published already today
            # or for all time. See inOurDatabase() function
            my $recurringFlag = 0;

			# Fetch sex filter (if any)
			my $sexFilter = $questionnaireFilters->getSexFilter();
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
                    # move on to the next questionnaire
                    else{next;}
                }
			}

			# Fetch age group filter (if any)
            my $ageFilter = $questionnaireFilters->getAgeFilter();
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
                    # move on to the next questionnaire
                    else{next;}
                }
            }

			# Retrieve all today's appointment(s)
            my @patientAppointments = Appointment::getTodaysPatientsAppointmentsFromOurDB($patientSer);

            my @aliasSerials = ();
            my @diagnosisNames = Diagnosis::getPatientsDiagnosesFromOurDB($patientSer);
            my @appointmentStatuses = ();
            my @checkins = ();

            my $frequencyFilter = $questionnaireFilters->getFrequencyFilter();

            # we build all possible appointment and diagnoses for each appointment found
            foreach my $appointment (@patientAppointments) {

                my $expressionSer = $appointment->getApptAliasExpressionSer();
                my $aliasSer = Alias::getAliasFromOurDB($expressionSer);
                my $status = $appointment->getApptStatus();
                my $checkinFlag = $appointment->getApptCheckin();
                push(@aliasSerials, $aliasSer) unless grep{$_ eq $aliasSer} @aliasSerials;
                push(@appointmentStatuses, $status) unless grep{$_ eq $status} @appointmentStatuses;
                push(@checkins, $checkinFlag) unless grep{$_ eq $checkinFlag} @checkins;

            }

            my @patientDoctors = PatientDoctor::getPatientsDoctorsFromOurDB($patientSer);

            # Fetch checkin filter (if any)
            my @checkinFilter = $questionnaireFilters->getCheckinFilters();
            if (@checkinFilter) {

                # toggle flag
                $isNonPatientSpecificFilterDefined = 1;
                $recurringFlag = 1;

                # Finding the existence of the patient checkin in the checkin filters
                # If there is an intersection, then patient is so far part of this publishing legacy questionnaire
                if (!intersect(@checkinFilter, @checkins)) {
                   if (@patientFilters) {
                        # if the patient failed to match the checkin filter but there are patient filters
                        # then we flag to check later if this patient matches with the patient filters
                        $isPatientSpecificFilterDefined = 1;
                    }
                    # else no patient filters were defined and failed to match the checkin filter
                    # move on to the next questionnaire
                    else{next;}
                } 
            }

            # Fetch appointment status filters (if any)
            my @appointmentStatusFilters =  $questionnaireFilters->getAppointmentStatusFilters();
            if (@appointmentStatusFilters) {

                # toggle flag
                $isNonPatientSpecificFilterDefined = 1;
                $recurringFlag = 1;

                # Finding the existence of the patient status in the status filters
                # If there is an intersection, then patient is so far part of this publishing legacy questionnaire
                if (!intersect(@appointmentStatusFilters, @appointmentStatuses)) {
                   if (@patientFilters) {
                        # if the patient failed to match the status filter but there are patient filters
                        # then we flag to check later if this patient matches with the patient filters
                        $isPatientSpecificFilterDefined = 1;
                    }
                    # else no patient filters were defined and failed to match the status filter
                    # move on to the next questionnaire
                    else{next;}
                } 
            }

			# Fetch appointment filters (if any)
            my @appointmentFilters =  $questionnaireFilters->getAppointmentFilters();
            if (@appointmentFilters) {

                # toggle flag
				$isNonPatientSpecificFilterDefined = 1;
                $recurringFlag = 1;

                # if all appointments were selected as triggers then patient passes
                # else do further checks 
                unless ('ALL' ~~ @appointmentFilters and @aliasSerials) {
                    # Finding the existence of the patient expressions in the appointment filters
                    # If there is an intersection, then patient is so far part of this publishing legacy questionnaire
                    if (!intersect(@appointmentFilters, @aliasSerials)) {
                       if (@patientFilters) {
                            # if the patient failed to match the appointment filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the appointment filter
                        # move on to the next questionnaire
                        else{next;}
                    } 
                }
            }

            # Fetch diagnosis filters (if any)
            my @diagnosisFilters = $questionnaireFilters->getDiagnosisFilters();
            if (@diagnosisFilters) {

                # toggle flag
				$isNonPatientSpecificFilterDefined = 1;

                # if all diagnoses were selected as triggers then patient passes
                # else do further checks 
                unless ('ALL' ~~ @diagnosisFilters and @diagnosisNames) {
                    # Finding the intersection of the patient's diagnosis and the diagnosis filters
                    # If there is an intersection, then patient is so far part of this publishing legacy questionnaire
                    if (!intersect(@diagnosisFilters, @diagnosisNames)) {
                        if (@patientFilters) {
                            # if the patient failed to match the diagnosis filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the diagnosis filter
                        # move on to the next questionnaire
                        else{next;}
                    }
                }
            }

            # Fetch doctor filters (if any)
            my @doctorFilters = $questionnaireFilters->getDoctorFilters();
            if (@doctorFilters) {

                # toggle flag
				$isNonPatientSpecificFilterDefined = 1;


                # if all doctors were selected as triggers then patient passes
                # else do further checks 
                unless ('ALL' ~~ @doctorFilters and @patientDoctors) {
                    # Finding the intersection of the patient's doctor(s) and the doctor filters
                    # If there is an intersection, then patient is so far part of this publishing legacy questionnaire
                    if (!intersect(@doctorFilters, @patientDoctors)) {
                        if (@patientFilters) {
                            # if the patient failed to match the doctor filter but there are patient filters
                            # then we flag to check later if this patient matches with the patient filters
                            $isPatientSpecificFilterDefined = 1;
                        }
                        # else no patient filters were defined and failed to match the doctor filter
                        # move on to the next questionnaire
                        else{next;}
                    } 
                }
            }

            # Fetch resource filters (if any)
            my @resourceFilters = $questionnaireFilters->getResourceFilters();
            if (@resourceFilters) {

                # toggle flag
                $isNonPatientSpecificFilterDefined = 1;

                # if all resources were selected as triggers then patient passes
                # else do further checks 
                unless ('ALL' ~~ @resourceFilters and @patientResources) {
                    # Finding the intersection of the patient resource(s) and the resource filters
                    # If there is an intersection, then patient is so far part of this publishing legacy questionnaire
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
                        if ($frequencyFilter) {
                            $recurringFlag = 1;
                        }
                        else {
                            $recurringFlag = 0;
                        }
                    }
                    else {next;}
                }
			}

            if ($isNonPatientSpecificFilterDefined eq 1 or $isPatientSpecificFilterDefined eq 1 or ($isNonPatientSpecificFilterDefined eq 0 and $patientPassed eq 1)) {

                # If we've reached this point, we've passed all catches (filter restrictions). We make
                # a questionnaire object, check if it exists already in the database. If it does 
                # this means the questionnaire has already been publish to the patient. If it doesn't
                # exist then we publish to the patient (insert into DB).
    			$questionnaire = new LegacyQuestionnaire();

    			# set the necessary values 
    			$questionnaire->setLegacyQuestionnaireControlSer($questionnaireControlSer);
                $questionnaire->setLegacyQuestionnairePatientSer($patientSer);
    			$questionnaire->setLegacyQuestionnaireCronLogSer($cronLogSer);

    			if (!$questionnaire->inOurDatabase($recurringFlag)) { 

    				$questionnaire = $questionnaire->insertLegacyQuestionnaireIntoOurDB();

    				# send push notification
    				my $questionnaireSer = $questionnaire->getLegacyQuestionnaireSer();
    				my $patientSer = $questionnaire->getLegacyQuestionnairePatientSer();
    				PushNotification::sendPushNotification($patientSer, $questionnaireSer, 'LegacyQuestionnaire');
    			}
            }

		}

	}

}

#======================================================================================
# Subroutine to check if our questionnaire exists in our MySQL db
#	@return: questionnaire object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
    # our questionnaire object in args
	my ($questionnaire, $recurringflag) = @_; 

	my $patientser 				= $questionnaire->getLegacyQuestionnairePatientSer();
	my $questionnaireControlSer	= $questionnaire->getLegacyQuestionnaireControlSer();

	my $serInDB = 0; # false by default. Will be true if questionnaire exists
	my $ExistingLegacyQuestionnaire = (); # data to be entered if questionnaire exists 

    my ($cronlogser);

	my $inDB_sql = "
		SELECT DISTINCT
			Questionnaire.QuestionnaireSerNum,
            Questionnaire.CronLogSerNum
		FROM
			Questionnaire
		WHERE
			Questionnaire.PatientSerNum  				= '$patientser'
		AND Questionnaire.QuestionnaireControlSerNum 	= '$questionnaireControlSer'
    ";
    if ($recurringflag) {
        $inDB_sql .= " 
            AND DATE(Questionnaire.DateAdded)= DATE(NOW())
        ";
    }

    # prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

		$serInDB = $data[0];
        $cronlogser = $data[1];
	}

	if ($serInDB) {

		$ExistingLegacyQuestionnaire = new LegacyQuestionnaire(); # initialize

		# set params
		$ExistingLegacyQuestionnaire->setLegacyQuestionnaireSer($serInDB);
		$ExistingLegacyQuestionnaire->setLegacyQuestionnairePatientSer($patientser);
        $ExistingLegacyQuestionnaire->setLegacyQuestionnaireControlSer($questionnaireControlSer);
		$ExistingLegacyQuestionnaire->setLegacyQuestionnaireCronLogSer($cronlogser);

		return $ExistingLegacyQuestionnaire; # this is true (i.e. questionnaire exists. return object)
	}

	else {return $ExistingLegacyQuestionnaire}; # this is false (i.e. questionnaire DNE)
}

#======================================================================================
# Subroutine to insert our questionnaire in our database
#======================================================================================
sub insertLegacyQuestionnaireIntoOurDB
{
	my ($questionnaire) = @_; # our questionnaire object

	my $patientser  			    = $questionnaire->getLegacyQuestionnairePatientSer();
    my $questionnaireControlSer     = $questionnaire->getLegacyQuestionnaireControlSer();
	my $cronlogser                  = $questionnaire->getLegacyQuestionnaireCronLogSer();

	my $insert_sql = "
		INSERT INTO 
			Questionnaire (
				PatientSerNum,
                CronLogSerNum,
				QuestionnaireControlSerNum,
				DateAdded
			)
		VALUES (
			'$patientser',
            '$cronlogser',
			'$questionnaireControlSer',
			NOW()
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
	$questionnaire->setLegacyQuestionnaireSer($ser);
	
	return $questionnaire;
}

#======================================================================================
# Subroutine to get a list of legacy questionnaire controls marked for publish
#======================================================================================
sub getLegacyQuestionnaireControlsMarkedForPublish
{
    my @questionnaireControlList = (); # initialize a list

    # this query combines the retrieval of all legacy questionnaires with a publish flag turned on 
    # and checks whether a questionnaire lands on the scheduled frequency time that is defined, if it is defined
    # (See the "Publish Frequency" section on opalAdmin for legacy questionnaires)
    # complex query inspiration from 
    # https://stackoverflow.com/questions/5183630/calendar-recurring-repeating-events-best-storage-method
    my $info_sql = "
        SELECT DISTINCT
           	QuestionnaireControl.QuestionnaireControlSerNum
		FROM
			QuestionnaireControl
        LEFT JOIN FrequencyEvents fe1 ON QuestionnaireControl.QuestionnaireControlSerNum = fe1.ControlTableSerNum
            AND fe1.ControlTable = 'LegacyQuestionnaireControl'
            AND fe1.MetaKey = 'repeat_start'
        LEFT JOIN FrequencyEvents fe2 ON fe2.MetaKey = CONCAT( 'repeat_day|lqc_', fe1.ControlTableSerNum )
        LEFT JOIN FrequencyEvents fe3 ON fe3.MetaKey = CONCAT( 'repeat_week|lqc_', fe1.ControlTableSerNum )
        LEFT JOIN FrequencyEvents fe4 ON fe4.MetaKey = CONCAT( 'repeat_month|lqc_', fe1.ControlTableSerNum )
        LEFT JOIN FrequencyEvents fe5 ON fe5.MetaKey = CONCAT( 'repeat_year|lqc_', fe1.ControlTableSerNum )
        LEFT JOIN FrequencyEvents fe6 ON fe6.MetaKey = CONCAT( 'repeat_day_iw|lqc_', fe1.ControlTableSerNum )
        LEFT JOIN FrequencyEvents fe7 ON fe7.MetaKey = CONCAT( 'repeat_week_im|lqc_', fe1.ControlTableSerNum )
        LEFT JOIN FrequencyEvents fe8 ON fe8.MetaKey = CONCAT( 'repeat_date_im|lqc_', fe1.ControlTableSerNum )
        LEFT JOIN FrequencyEvents fe9 ON fe9.MetaKey = CONCAT( 'repeat_month_iy|lqc_', fe1.ControlTableSerNum )
        LEFT JOIN FrequencyEvents fe10 ON QuestionnaireControl.QuestionnaireControlSerNum = fe10.ControlTableSerNum
            AND fe10.ControlTable = 'LegacyQuestionnaireControl'
            AND fe10.MetaKey = 'repeat_end'
		WHERE
        -- Flag
			QuestionnaireControl.PublishFlag = 1
        AND ( 
            -- Compare day interval
            (
                -- Number of days passed since start is divisible by repeat interval
                MOD(TIMESTAMPDIFF(DAY, FROM_UNIXTIME(fe1.MetaValue), NOW()), fe2.MetaValue) = 0
                -- or day repeat not defined
                OR fe2.MetaValue IS NULL
            )
            -- Compare week interval
            AND (
                -- If only week interval is defined
                (
                    -- Number of weeks (in days) passed since start is divisible by repeat interval
                    MOD(TIMESTAMPDIFF(DAY, FROM_UNIXTIME(fe1.MetaValue), NOW()), fe3.MetaValue*7) = 0
                    -- No repeat_day_iw defined
                    AND fe6.MetaValue IS NULL
                    -- or week repeat not even defined at all
                    OR fe3.MetaValue IS NULL
                )
                -- If repeat_day_iw define, we override basic repeat week logic 
                OR (
                    -- Number of weeks passed since start is divisible by repeat interval
                    -- BUT shift both start date and now to Sunday to compare weeks passed
                    -- Because if triggers are on Mon, Tues, but today is Thurs then technically Mon, Tues next week
                    -- should trigger even though a true week hasn't passed
                    MOD(
                        TIMESTAMPDIFF(
                            WEEK,
                            DATE_SUB(FROM_UNIXTIME(fe1.MetaValue), INTERVAL DAYOFWEEK(FROM_UNIXTIME(fe1.MetaValue))-1 DAY),
                            DATE_SUB(NOW(), INTERVAL DAYOFWEEK(NOW())-1 DAY)
                        ),
                        fe3.MetaValue
                    ) = 0
                    -- today is in list of days in week 
                    AND find_in_set(DAYOFWEEK(NOW()), fe6.MetaValue) > 0
                )
            )
            -- Compare month interval
            AND (
                -- If only the month interval is defined 
                (
                    -- Number of months passed since start is divisible by repeat interval
                    -- https://stackoverflow.com/questions/288984/the-difference-in-months-between-dates-in-mysql
                    MOD(
                        TIMESTAMPDIFF(MONTH,FROM_UNIXTIME(fe1.MetaValue), NOW()) 
                        + DATEDIFF(
                            NOW(), 
                            FROM_UNIXTIME(fe1.MetaValue) + INTERVAL TIMESTAMPDIFF(MONTH, FROM_UNIXTIME(fe1.MetaValue), NOW()) MONTH
                        ) /
                        DATEDIFF(
                            FROM_UNIXTIME(fe1.MetaValue) + INTERVAL TIMESTAMPDIFF(MONTH, FROM_UNIXTIME(fe1.MetaValue), NOW()) + 1 MONTH,
                            FROM_UNIXTIME(fe1.MetaValue) + INTERVAL TIMESTAMPDIFF(MONTH, FROM_UNIXTIME(fe1.MetaValue), NOW()) MONTH 
                        ), 
                        fe4.MetaValue
                    ) = 0 
                    -- No repeat_day_iw defined
                    AND fe6.MetaValue IS NULL
                    -- No repeat_week_im defined
                    AND fe7.MetaValue IS NULL
                    -- No repeat_date_im defined
                    AND fe8.MetaValue IS NULL
                    -- or month repeat interval not defined at all
                    OR fe4.MetaValue IS NULL
                )
                -- If other repeats are defined in conjuntion to month repeat
                OR (
                    -- Number of months passed since start is divisible by repeat interval 
                    -- BUT shift both start date and today's date to the 1st to compare months passed 
                    -- Because if triggers are on 2nd and 3rd of the month and today is the 15th, then
                    -- we shouldn't trigger on the next 2nd and 3rd if we check every 2 months even though
                    -- a true month hasn't passed
                    MOD(
                        TIMESTAMPDIFF(
                            MONTH,
                            DATE_SUB(FROM_UNIXTIME(fe1.MetaValue), INTERVAL DAY(FROM_UNIXTIME(fe1.MetaValue))-1 DAY),
                            DATE_SUB(NOW(), INTERVAL DAY(NOW())-1 DAY)
                        ),
                        fe4.MetaValue
                    ) = 0
                    AND (
                        -- logic for day and week in month
                        (
                            -- today lands on the defined day in week 
                            MOD(DAYOFWEEK(NOW()), fe6.MetaValue) = 0
                            -- today lands on the week number in month 
                            AND (
                                -- logic for week number other than last day in month
                                (
                                    MOD(
                                        WEEK(NOW(),3) - WEEK(NOW() - INTERVAL DAY(NOW()) - 1 DAY,3),
                                        fe7.MetaValue
                                    ) = 0
                                    -- if not looking for last day in month 
                                    AND fe7.MetaValue != 6
                                )
                                -- logic for last day in month
                                OR (
                                    DAY(NOW()) = DAY(LAST_DAY(NOW()) - ((7 + DAYOFWEEK(LAST_DAY(NOW())) - fe6.MetaValue) % 7))
                                    AND fe7.MetaValue = 6
                                )
                            )
                        )
                        -- logic for date in month
                        OR (
                            -- today's date in list of dates
                            find_in_set(DAY(NOW()), fe8.MetaValue) > 0
                        )
                    )
                )
            )
            -- Compare year interval
            AND (
                -- If only the year interval is defined
                (

                    -- Number of years passed (in days) since start is divisible by repeat interval
                    MOD(TIMESTAMPDIFF(DAY, FROM_UNIXTIME(fe1.MetaValue), NOW())/365, fe5.MetaValue) = 0
                    -- No day in week defined 
                    AND fe6.MetaValue IS NULL
                    -- No repeat_week_im defined 
                    AND fe7.MetaValue IS NULL
                    -- No repeat_month_iy defined 
                    AND fe9.MetaValue IS NULL
                    -- or year repeat interval not defined at all
                    OR fe5.MetaValue IS NULL
                )
                -- If other repeats are defined in conjunction with the year
                OR (
                    -- Subtract year number since start is divisible by repeat interval
                    MOD (YEAR(NOW()) - YEAR(FROM_UNIXTIME(fe1.MetaValue)), fe5.MetaValue) = 0
                    -- 
                    AND (
                        -- logic for day and week in month 
                        (
                            MOD(DAYOFWEEK(NOW()), fe6.MetaValue) = 0
                            -- today lands on the week number in month 
                            AND (
                                -- logic for week number other than last day in month
                                (
                                    MOD(
                                        WEEK(NOW(),3) - WEEK(NOW() - INTERVAL DAY(NOW()) - 1 DAY,3),
                                        fe7.MetaValue
                                    ) = 0
                                    -- if not looking for last day in month 
                                    AND fe7.MetaValue != 6
                                )
                                -- logic for last day in month
                                OR (
                                    DAY(NOW()) = DAY(LAST_DAY(NOW()) - ((7 + DAYOFWEEK(LAST_DAY(NOW())) - fe6.MetaValue) % 7))
                                    AND fe7.MetaValue = 6
                                )
                            )
                            -- and repeat_month_iy not defined 
                            AND fe9.MetaValue IS NULL
                        )
                        -- logic for months in year 
                        OR (
                            -- today's month in list of months 
                            find_in_set(MONTH(NOW()), fe9.MetaValue) > 0
                            -- and repeat_day_iw is null
                            AND fe6.MetaValue IS NULL 
                            -- and repeat_week_im is null 
                            AND fe7.MetaValue IS NULL
                        )
                        -- logic for both day and week and months in year 
                        OR (
                            MOD(DAYOFWEEK(NOW()), fe6.MetaValue) = 0
                            -- today lands on the week number in month 
                            AND (
                                -- logic for week number other than last day in month
                                (
                                    MOD(
                                        WEEK(NOW(),3) - WEEK(NOW() - INTERVAL DAY(NOW()) - 1 DAY,3),
                                        fe7.MetaValue
                                    ) = 0
                                    -- if not looking for last day in month 
                                    AND fe7.MetaValue != 6
                                )
                                -- logic for last day in month
                                OR (
                                    DAY(NOW()) = DAY(LAST_DAY(NOW()) - ((7 + DAYOFWEEK(LAST_DAY(NOW())) - fe6.MetaValue) % 7))
                                    AND fe7.MetaValue = 6
                                )
                            )
                            -- today's month in list of months 
                            AND find_in_set(MONTH(NOW()), fe9.MetaValue) > 0
                        )
                    )
                )
            )
            -- today must be greater than start date
            AND FROM_UNIXTIME(fe1.MetaValue) <= NOW()
            -- end date logic 
            AND (
                -- today must be less than end date 
                DATE(NOW()) <= FROM_UNIXTIME(fe10.MetaValue)
                -- or no end date is defined 
                OR fe10.MetaValue IS NULL
            )
            -- or no start date is set at all 
            OR fe1.MetaValue IS NULL
        ) 
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($info_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        my $questionnaireControl = new LegacyQuestionnaire(); # new object

        my $ser             = $data[0];

        # set information
        $questionnaireControl->setLegacyQuestionnaireControlSer($ser);

        # get all the filters
        my $filters = Filter::getAllFiltersFromOurDB($ser, 'LegacyQuestionnaireControl');

        $questionnaireControl->setLegacyQuestionnaireFilters($filters);

        push(@questionnaireControlList, $questionnaireControl);
    }

    return @questionnaireControlList;
}


# Exit smoothly 
1;

