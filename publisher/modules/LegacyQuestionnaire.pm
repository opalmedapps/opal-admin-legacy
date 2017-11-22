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
# Subroutine to publish legacy questionnaires
#====================================================================================
sub publishLegacyQuestionnaires
{
	my (@patientList) = @_; # patient list from args

	# Retrieve all the legacy questionnaire controls
	my @legacyQuestionnaireControls = getLegacyQuestionnaireControlsMarkedForPublish(); 

	foreach my $Patient (@patientList) {

		my $patientSer = $Patient->getPatientSer(); 

		foreach my $QuestionnaireControl (@legacyQuestionnaireControls) {

			my $questionnaireControlSer 	= $QuestionnaireControl->getLegacyQuestionnaireControlSer();
			my $questionnaireFilters 		= $QuestionnaireControl->getLegacyQuestionnaireFilters();

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
			$questionnaire = new LegacyQuestionnaire();

			# set the necessary values 
			$questionnaire->setLegacyQuestionnaireControlSer($questionnaireControlSer);
			$questionnaire->setLegacyQuestionnairePatientSer($patientSer);

			if (!$questionnaire->inOurDatabase()) {

				$questionnaire = $questionnaire->insertLegacyQuestionnaireIntoOurDB();

				# send push notification
				my $questionnaireSer = $questionnaire->getLegacyQuestionnaireSer();
				my $patientSer = $questionnaire->getLegacyQuestionnairePatientSer();
				#PushNotification::sendPushNotification($patientSer, $questionnaireSer, 'LegacyQuestionnaire');
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

	my $patientser 				= $questionnaire->getLegacyQuestionnairePatientSer();
	my $questionnaireControlSer	= $questionnaire->getLegacyQuestionnaireControlSer();

	my $serInDB = 0; # false by default. Will be true if questionnaire exists
	my $ExistingLegacyQuestionnaire = (); # data to be entered if questionnaire exists 

	my $inDB_sql = "
		SELECT DISTINCT
			Questionnaire.QuestionnaireSerNum
		FROM
			Questionnaire
		WHERE
			Questionnaire.PatientSerNum  				= '$patientser'
		AND Questionnaire.QuestionnaireControlSerNum 	= '$questionnaireControlSer'
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

		$ExistingLegacyQuestionnaire = new LegacyQuestionnaire(); # initialize

		# set params
		$ExistingLegacyQuestionnaire->setLegacyQuestionnaireSer($serInDB);
		$ExistingLegacyQuestionnaire->setLegacyQuestionnairePatientSer($patientser);
		$ExistingLegacyQuestionnaire->setLegacyQuestionnaireControlSer($questionnaireControlSer);

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

	my $patientser  			= $questionnaire->getLegacyQuestionnairePatientSer();
	my $questionnaireControlSer = $questionnaire->getLegacyQuestionnaireControlSer();

	my $insert_sql = "
		INSERT INTO 
			Questionnaire (
				PatientSerNum,
				QuestionnaireControlSerNum,
				DateAdded
			)
		VALUES (
			'$patientser',
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

    my $info_sql = "
        SELECT DISTINCT
           	QuestionnaireControl.QuestionnaireControlSerNum
		FROM
			QuestionnaireControl
		WHERE
			QuestionnaireControl.PublishFlag = 1
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

