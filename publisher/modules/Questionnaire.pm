#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 15-Aug-2017 ++ File: Questionnaire.pm
#---------------------------------------------------------------------------------
# Perl module that creates a questionnaire class. This module calls a 
# constructor to create a questionnaire object that contains questionnaire information stored
# as object variables.
#
# There exists various subroutines to set and get questionnaire information and compare
# questionnaire information between two questionnaire objects.

package Questionnaire; # Declaring package name

use Database; # Our custom database module
use Time::Piece; # perl module
use Array::Utils qw(:all);
use POSIX; # perl module

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
		_userser 					=> undef,
		_filters 					=> undef,
	}; 

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $questionnaire, $class;
    return $questionnaire;
}

#====================================================================================
# Subroutine to set the Questionnaire Serial
#====================================================================================
sub setQuestionnaireSer
{
    my ($questionnaire, $ser) = @_; # questionnaire object with provided serial in args
    $questionnaire->{_ser} = $ser; # set the questionnaire ser
    return $questionnaire->{_ser};
}

#====================================================================================
# Subroutine to set the Questionnaire Patient Serial
#====================================================================================
sub setQuestionnairePatientSer
{
    my ($questionnaire, $patientser) = @_; # questionnaire object with provided serial in args
    $questionnaire->{_patientser} = $patientser; # set the ser
    return $questionnaire->{_patientser};
}

#====================================================================================
# Subroutine to set the Questionnaire Control Serial
#====================================================================================
sub setQuestionnaireControlSer
{
    my ($questionnaire, $questionnairecontrolser) = @_; # questionnaire object with provided serial in args
    $questionnaire->{_questionnairecontrolser} = $questionnairecontrolser; # set the ser
    return $questionnaire->{_questionnairecontrolser};
}

#====================================================================================
# Subroutine to set the Questionnaire User Serial
#====================================================================================
sub setQuestionnaireUserSer
{
    my ($questionnaire, $userser) = @_; # questionnaire object with provided serial in args
    $questionnaire->{_userser} = $userser; # set the ser
    return $questionnaire->{_userser};
}

#====================================================================================
# Subroutine to set the Questionnaire Filters
#====================================================================================
sub setQuestionnaireFilters
{
    my ($questionnaire, $filters) = @_; # questionnaire object with provided filters in args
    $questionnaire->{_filters} = $filters; # set the filters
    return $questionnaire->{_filters};
}

#====================================================================================
# Subroutine to get the Questionnaire Serial
#====================================================================================
sub getQuestionnaireSer
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_ser};
}

#====================================================================================
# Subroutine to get the Questionnaire Patient Serial
#====================================================================================
sub getQuestionnairePatientSer
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_patientser};
}

#====================================================================================
# Subroutine to get the Questionnaire Serial
#====================================================================================
sub getQuestionnaireControlSer
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_questionnairecontrolser};
}

#====================================================================================
# Subroutine to get the Questionnaire User Serial
#====================================================================================
sub getQuestionnaireUserSer
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_userser};
}

#====================================================================================
# Subroutine to get the Questionnaire Filters
#====================================================================================
sub getQuestionnaireFilters
{
	my ($questionnaire) = @_; # our questionnaire object
	return $questionnaire->{_filters};
}

#====================================================================================
# Subroutine to publish questionnaires
#====================================================================================
sub publishQuestionnaires
{
	my (@patientList) = @_; # patient list from args

	# Retrieve all the questionnaire controls
	my @questionnaireControls = getQuestionnaireControlsMarkedForPublish(); 

	foreach my $Patient (@patientList) {

		my $patientSer = $Patient->getPatientSer(); 

		foreach my $QuestionnaireControl (@questionnaireControls) {

			my $questionnaireControlSer 	= $QuestionnaireControl->getQuestionnaireControlSer();
			my $questionnaireFilters 		= $QuestionnaireControl->getQuestionnaireFilters();

			# Fetch sex filter (if any)
			my $sexFilter = $questionnaireFilters->getSexFilter();
			if ($sexFilter) {

				my $patientSex = $Patient->getPatientSex();

				# Determine if the filter matches the current patient's sex
				# If not, continue to the next questionnaire
				if ($sexFilter ne $patientSex){next;}
			}

			# Fetch age group filter (if any)
            my $ageFilter = $questionnaireFilters->getAgeFilter();
            if ($ageFilter) {

                my $patientAge = $Patient->getPatientAge();
                # Determine if the patient's age falls within the age group
                # If not, continue to the next questionnaire
                if ($patientAge < $ageFilter->{_min} or $patientAge > $ageFilter->{_max}) {next;}

            }

			# Retrieve all patient's appointment(s) up until tomorrow
            my @patientAppointments = Appointment::getAllPatientsAppointmentsFromOurDB($patientSer);

            my @expressionNames = ();
            my @diagnosisNames = ();

            # we build all possible expression names, and diagnoses for each appointment found
            foreach my $appointment (@patientAppointments) {

                my $expressionSer = $appointment->getApptAliasExpressionSer();
                my $expressionName = Alias::getExpressionNameFromOurDB($expressionSer);
                push(@expressionNames, $expressionName) unless grep{$_ eq $expressionName} @expressionNames;

                my $diagnosisSer = $appointment->getApptDiagnosisSer();
                my $diagnosisName = Diagnosis::getDiagnosisNameFromOurDB($diagnosisSer);
                push(@diagnosisNames, $diagnosisName) unless grep{$_ eq $diagnosisName} @diagnosisNames;

            }

            my @patientDoctors = PatientDoctor::getPatientsDoctorsFromOurDB($patientSer);

			# Fetch expression filters (if any)
            my @expressionFilters =  $questionnaireFilters->getExpressionFilters();
            if (@expressionFilters) {

                # Finding the existence of the patient expressions in the expression filters
                # If there is an intersection, then patient is part of this publishing announcement
                # If not, then continue to next announcement
                if (!intersect(@expressionFilters, @expressionNames)) {next;} 
            }

            # Fetch diagnosis filters (if any)
            my @diagnosisFilters = $questionnaireFilters->getDiagnosisFilters();
            if (@diagnosisFilters) {

                # Finding the intersection of the patient's diagnosis and the diagnosis filters
                # If there is an intersection, then patient is part of this publishing announcement
                # If not, then continue to next announcement
                if (!intersect(@diagnosisFilters, @diagnosisNames)) {next;} 
            }

            # Fetch doctor filters (if any)
            my @doctorFilters = $questionnaireFilters->getDoctorFilters();
            if (@doctorFilters) {

                # Finding the intersection of the patient's doctor(s) and the doctor filters
                # If there is an intersection, then patient is part of this publishing announcement
                # If not, then continue to next announcement
                if (!intersect(@doctorFilters, @patientDoctors)) {next;} 
            }

            # If we've reached this point, we've passed all catches (filter restrictions). We make
            # a questionnaire object, check if it exists already in the database. If it does 
            # this means the questionnaire has already been publish to the patient. If it doesn't
            # exist then we publish to the patient (insert into DB).
			$questionnaire = new Questionnaire();

			# set the necessary values 
			$questionnaire->setQuestionnaireControlSer($questionnaireControlSer);
			$questionnaire->setQuestionnairePatientSer($patientSer);

			if (!$questionnaire->inOurDatabase()) {

				$questionnaire = $questionnaire->insertQuestionnaireIntoOurDB();

				# send push notification
				my $questionnaireSer = $questionnaire->getQuestionnaireSer();
				my $patientSer = $questionnaire->getQuestionnairePatientSer();
				#PushNotification::sendPushNotification($patientSer, $questionnaireSer, 'Questionnaire');
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
	my ($questionnaire) = @_; # our questionnaire object in args

	my $patientser 				= $questionnaire->getQuestionnairePatientSer();
	my $questionnairecontrolser	= $questionnaire->getQuestionnaireControlSer();

	my $serInDB = 0; # false by default. Will be true if questionnaire exists
	my $ExistingQuestionnaire = (); # data to be entered if questionnaire exists 

	my $inDB_sql = "
		SELECT DISTINCT
			qp.serNum
		FROM
			Questionnaire_patient qp
		WHERE
			qp.patient_serNum  		= '$patientser'
		AND qp.questionnaire_serNum	= '$questionnairecontrolser'
	";

	  # prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

		$serInDB = $data[0];
	}

	if ($serInDB) {

		$ExistingQuestionnaire = new Questionnaire(); # initialize

		# set params
		$ExistingQuestionnaire->setQuestionnaireSer($serInDB);
		$ExistingQuestionnaire->setQuestionnairePatientSer($patientser);
		$ExistingQuestionnaire->setQuestionnaireControlSer($questionnairecontrolser);

		return $ExistingQuestionnaire; # this is true (i.e. questionnaire exists. return object)
	}

	else {return $ExistingQuestionnaire}; # this is false (i.e. questionnaire DNE)
}

#======================================================================================
# Subroutine to insert our questionnaire in our database
#======================================================================================
sub insertQuestionnaireIntoOurDB
{
	my ($questionnaire) = @_; # our questionnaire object

	my $patientser  			= $questionnaire->getQuestionnairePatientSer();
	my $questionnairecontrolser = $questionnaire->getQuestionnaireControlSer();

	my $insert_sql = "
		INSERT INTO 
			Questionnaire_patient (
				patient_serNum,
				questionnaire_serNum
			)
		VALUES (
			'$patientser',
			'$questionnairecontrolser'
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
	$questionnaire->setQuestionnaireSer($ser);
	
	return $questionnaire;
}

#======================================================================================
# Subroutine to get a list of questionnaire controls marked for publish
#======================================================================================
sub getQuestionnaireControlsMarkedForPublish
{
    my @questionnaireControlList = (); # initialize a list

    my $info_sql = "
        SELECT DISTINCT
           Questionnaire.serNum
		FROM
			Questionnaire
		WHERE
			Questionnaire.publish = 1
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($info_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        my $questionnaireControl = new Questionnaire(); # new object

        my $ser             = $data[0];

        # set information
        $questionnaireControl->setQuestionnaireControlSer($ser);

        # get all the filters
        my $filters = Filter::getAllFiltersFromOurDB($ser, 'Questionnaire');

        $questionnaireControl->setQuestionnaireFilters($filters);

        push(@questionnaireControlList, $questionnaireControl);
    }

    return @questionnaireControlList;
}


# Exit smoothly 
1;

