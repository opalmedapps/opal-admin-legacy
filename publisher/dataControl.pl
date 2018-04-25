#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 07-Aug-2015 ++ File: dataControl.pl
#---------------------------------------------------------------------------------
# Perl script that acts as a cronjob for populating the MySQL database with selected
# information. This script is called from the crontab.  
#
# We use our custom Perl Modules to help us with getting information and 
# setting them into the appropriate place. 
use File::Basename;
use lib dirname($0) . '/modules'; # specify where are modules are -- $0 = this script's location

#-----------------------------------------------------------------------
# Packages/Modules
#-----------------------------------------------------------------------
use Time::Piece;
use POSIX;
use Storable qw(dclone);

#-----------------------------------------------------------------------
# Custom Modules
#-----------------------------------------------------------------------
use Configs; 
use Database; 
use Patient; 
use Task; 
use Appointment; 
use ResourceAppointment; 
use Document; 
use Alias; 
use Doctor; 
use Diagnosis; 
use PatientDoctor; 
use TestResult; 
use TestResultControl;
use Cron; 
use PostControl; 
use Announcement; 
use TxTeamMessage; 
use PatientsForPatients; # custom PatientsForPatients.pm
use EducationalMaterialControl; 
use EducationalMaterial; 
use Priority; 
use PatientLocation; 
use Questionnaire; 
use LegacyQuestionnaire;

# Get the current time (for last-updates/logs)
my $start_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));

# Log that the script is initialized in the cronlog
my $cronLogSer = Cron::setCronLog("Started", $start_datetime);

#-----------------------------------------------------------------------
# Parameter initialization
#-----------------------------------------------------------------------
my @registeredPatients = (); 
my @patientList = (); 
my @PDList = (); 
my @TaskList = (); 
my @ApptList = (); 
my @DocList = ();
my @DiagnosisList = ();
my @PriorityList = ();
my @TRList = ();
my @RAList = ();
my @PLList = ();
my @PLMHList = ();

my $verbose = 1;

#=========================================================================================
# Retrieve all patients that are marked for update
#=========================================================================================
@registeredPatients = Patient::getPatientsMarkedForUpdate(); 

print "Got patients\n" if $verbose;
#=========================================================================================
# Loop over each patient. 
#=========================================================================================
foreach my $Patient (@registeredPatients) {

    # retrieve information from source databases
    my @sourcePatients = $Patient->getPatientInfoFromSourceDBs();

    foreach my $SourcePatient (@sourcePatients) {

        # check if patient exists in our database (it should by default)
        my $patientExists = $SourcePatient->inOurDatabase();

        if ($patientExists) { # patient exists

            my $ExistingPatient = dclone($patientExists); # reassign variable

            # compare our source patient with the existing patient
            # update is done on the existing patient
            my ($UpdatedPatient, $change) = $SourcePatient->compareWith($ExistingPatient);

			# if there was an actual change in comparison
			if ($change) {
				# update the database
				$UpdatedPatient->updateDatabase();
			}

            # push to patient list 
            push(@patientList, $UpdatedPatient);

        } else { # patient DNE 

    		# insert Patient into our database
	    	$SourcePatient = $SourcePatient->insertPatientIntoOurDB();

            # push to patient list 
            push(@patientList, $SourcePatient);

	    }
    }
}

##########################################################################################
# 
# Data Retrieval PATIENTDOCTORS - get list of patient-doctor info updated since last update
#
##########################################################################################
@PDList = PatientDoctor::getPatientDoctorsFromSourceDB(@patientList);
#=========================================================================================
# Loop over each PD. Various functions are done.
#=========================================================================================
foreach my $PatientDoctor (@PDList) {

	# check if patient exists in our database
	my $PDExists = $PatientDoctor->inOurDatabase();

	if ($PDExists) { # patientdoctor exists

		my $ExistingPD = dclone($PDExists); # reassign variable	

		# compare our retrieve PatientDoctor with existing PD
		# update is done on the original (existing) PD
		my $UpdatedPD = $PatientDoctor->compareWith($ExistingPD);

		# after updating our PatientDoctor object, update the database
		$UpdatedPD->updateDatabase();

	} else { # patient doctor DNE 

		# insert PatientDoctor into our database
		$PatientDoctor->insertPatientDoctorIntoOurDB();
	}
}

print "Got PDs\n" if $verbose;
##########################################################################################
# 
# Data Retrieval DIAGNOSES - get list of diagnosis info updated since last update
#
##########################################################################################
@DiagnosisList = Diagnosis::getDiagnosesFromSourceDB(@patientList);

#=========================================================================================
# Loop over each diagnosis. Various functions are done.
#=========================================================================================
foreach my $Diagnosis (@DiagnosisList) {

	# check if diagnosis exists in our database
	my $DiagnosisExists = $Diagnosis->inOurDatabase();

	if ($DiagnosisExists) { # diagnosis exists

		my $ExistingDiagnosis = dclone($DiagnosisExists); # reassign variable	

		# compare our retrieve Diagnosis with existing Diagnosis
		# update is done on the original (existing) Diagnosis
		my $UpdatedDiagnosis = $Diagnosis->compareWith($ExistingDiagnosis);

		# after updating our Diagnosis object, update the database
		$UpdatedDiagnosis->updateDatabase();

	} else { # diagnosis DNE 
				
		# insert Diagnosis into our database
		$Diagnosis->insertDiagnosisIntoOurDB();
	}
}

print "Got diagnosis\n" if $verbose;

##########################################################################################
# 
# Data Retrieval PRIORITIES - get list of priority info updated since last update
#
##########################################################################################
@PriorityList = Priority::getPrioritiesFromSourceDB(@patientList);

#=========================================================================================
# Loop over each priority. Various functions are done.
#=========================================================================================
foreach my $Priority (@PriorityList) {

	# check if priority exists in our database
	my $PriorityExists = $Priority->inOurDatabase();

	if ($PriorityExists) { # priority exists

		my $ExistingPriority = dclone($PriorityExists); # reassign variable	

		# compare our retrieve Priority with existing Priority
		# update is done on the original (existing) Priority
		my $UpdatedPriority = $Priority->compareWith($ExistingPriority);

		# after updating our Priority object, update the database
		$UpdatedPriority->updateDatabase();

	} else { # priority DNE 
				
		# insert Priority into our database
		$Priority->insertPriorityIntoOurDB();
	}
}

print "Got priority\n" if $verbose;


##########################################################################################
# 
# Data Retrieval TASKS - get list of patients with tasks updated since last update
#
##########################################################################################
@TaskList = Task::getTasksFromSourceDB(@patientList, $cronLogSer);

#=========================================================================================
# Loop over each task. Various functions are done.
#=========================================================================================
foreach my $Task (@TaskList) {

	# check if task exists in our database
	my $TaskExists = $Task->inOurDatabase();

	if ($TaskExists) { # task exists

		my $ExistingTask = dclone($TaskExists); # reassign variable	

		# compare our retrieve Task with existing Task
		# update is done on the original (existing) Task
		my $UpdatedTask = $Task->compareWith($ExistingTask);

		# after updating our Task object, update the database
		$UpdatedTask->updateDatabase();

	} else { # task DNE 
				
		# insert Task into our database
		$Task = $Task->insertTaskIntoOurDB();
	}	

}

print "Got tasks\n" if $verbose;
##########################################################################################
# 
# Data Retrieval APPOINTMENTS - get list of patients with appointments updated since last update
#
##########################################################################################
@ApptList = Appointment::getApptsFromSourceDB(@patientList, $cronLogSer);

#=========================================================================================
# Loop over each patient. Various functions are done.
#=========================================================================================
foreach my $Appointment (@ApptList) {

	# check if appointment exists in our database
	my $AppointmentExists = $Appointment->inOurDatabase();

	if ($AppointmentExists) { # appointment exists

		my $ExistingAppointment = dclone($AppointmentExists); # reassign variable	

		# compare our retrieve Appointment with existing Appointment
		# update is done on the original (existing) Appointment
		my $UpdatedAppointment = $Appointment->compareWith($ExistingAppointment);

		# after updating our Appointment object, update the database
		$UpdatedAppointment->updateDatabase();

	} else { # appointment DNE 
				
		# insert Appointment into our database
		$Appointment = $Appointment->insertApptIntoOurDB();
	}	
}

print "Got appointments\n" if $verbose;
##########################################################################################
# 
# Data Retrieval RESOURCEAPPOINTMENT - get list of resourceappt info updated since last update
#
##########################################################################################
@RAList = ResourceAppointment::getResourceAppointmentsFromSourceDB(@patientList);

print "RA List\n" if $verbose;
#=========================================================================================
# Loop over each RA. Various functions are done.
#=========================================================================================
foreach my $ResourceAppointment (@RAList) {

	# check if RA exists in our database
	my $RAExists = $ResourceAppointment->inOurDatabase();

	if ($RAExists) { # RA exists

		my $ExistingRA = dclone($RAExists); # reassign variable	

		# compare our retrieve RA with existing RA
		# update is done on the original (existing) RA
		my $UpdatedRA = $ResourceAppointment->compareWith($ExistingRA);

		# after updating our RA object, update the database
		$UpdatedRA->updateDatabase();

	} else { # RA DNE 
				
		# insert RA into our database
		$ResourceAppointment->insertResourceAppointmentIntoOurDB();
	}
}


print "Got RAs\n" if $verbose;

##########################################################################################
# 
# Data Retrieval PATIENTLOCATION - get list of PL info updated since last update
#
##########################################################################################
@PLList = PatientLocation::getPatientLocationsFromSourceDB(@patientList);

print "PL List\n" if $verbose;
#=========================================================================================
# Loop over each PL. Various functions are done.
#=========================================================================================
foreach my $PatientLocation (@PLList) {

	# check if PL exists in our database 
	my $PLExists = $PatientLocation->inOurDatabase();

	if ($PLExists) { # PL exists
	
		my $ExistingPL = dclone($PLExists); # reassign variable

		# compare our retrieved PL with the existing PL
		# update is done on the original (existing) PL
		my $UpdatedPL = $PatientLocation->compareWith($ExistingPL);

		# after updating our PL object, update the database
		$UpdatedPL->updateDatabase();

	} else { #PL DNE
	
		# insert PL into our database 
		$PatientLocation->insertPatientLocationIntoOurDB();
	}
}

print "Got PLs\n" if $verbose;

##########################################################################################
# 
# Data Retrieval PATIENTLOCATIONMH - get list of PL MH info updated since last update
#
##########################################################################################
@PLMHList = PatientLocation::getPatientLocationsMHFromSourceDB(@patientList);

print "PL List\n" if $verbose;
#=========================================================================================
# Loop over each PL MH. Various functions are done.
#=========================================================================================
foreach my $PatientLocation (@PLMHList) {

	# check if PL exists in our database 
	my $PLExists = $PatientLocation->inOurDatabaseMH();

	if ($PLExists) { # PL exists
	
		my $ExistingPL = dclone($PLExists); # reassign variable

		# compare our retrieved PL with the existing PL
		# update is done on the original (existing) PL
		my $UpdatedPL = $PatientLocation->compareWith($ExistingPL);

		# after updating our PL object, update the database
		$UpdatedPL->updateDatabaseMH();

	} else { #PL DNE
	
		# insert PL into our database 
		$PatientLocation->insertPatientLocationMHIntoOurDB();
	}
}

print "Got PL MHs\n" if $verbose;

##########################################################################################
# 
# Data Retrieval DOCUMENTS - get list of patients with documents updated since last update
#
##########################################################################################
@DocList = Document::getDocsFromSourceDB(@patientList, $cronLogSer);

# Transfer and log patient documents
Document::transferPatientDocuments(@DocList);


print "Got documents\n" if $verbose;


##########################################################################################
# 
# Data Retrieval TESTRESULTS - get list of patients with test results updated since last update
#
##########################################################################################
@TRList = TestResult::getTestResultsFromSourceDB(@patientList, $cronLogSer);

#=========================================================================================
# Loop over each test result. Various functions are done.
#=========================================================================================
foreach my $TestResult (@TRList) {

	# check if TR exists in our database
	my $TRExists = $TestResult->inOurDatabase();

	if ($TRExists) { # TR exists

		my $ExistingTR = dclone($TRExists); # reassign variable	

		# compare our retrieve TR with existing TR
		# update is done on the original (existing) TR
		my $UpdatedTR = $TestResult->compareWith($ExistingTR);

		# after updating our TR object, update the database
		$UpdatedTR->updateDatabase();

	} else { # TR DNE 
				
		# insert TR into our database
		$TestResult->insertTestResultIntoOurDB();
	}	
		
}

print "Got test results\n" if $verbose;
##########################################################################################
# 
# Publishing ANNOUNCEMENTS 
#
##########################################################################################
Announcement::publishAnnouncements(@patientList, $cronLogSer);

print "Got announcements\n" if $verbose;

##########################################################################################
# 
# Publishing TREATMENT TEAM MESSAGES
#
##########################################################################################
TxTeamMessage::publishTxTeamMessages(@patientList, $cronLogSer);

print "Got TTM\n" if $verbose;

##########################################################################################
# 
# Publishing PATIENTS FOR PATIENTS
#
##########################################################################################
PatientsForPatients::publishPatientsForPatients(@patientList, $cronLogSer);

print "Got P4P\n";

##########################################################################################
# 
# Publishing EDUCATIONAL MATERIALS
#
##########################################################################################
EducationalMaterial::publishEducationalMaterials(@patientList, $cronLogSer);

print "Got Educational materials\n" if $verbose;

##########################################################################################
# 
# Publishing QUESTIONNAIRES
#
##########################################################################################
# Questionnaire::publishQuestionnaires(@patientList);

# print "Got Questionnaires\n" if $verbose;

##########################################################################################
# 
# Publishing LEGACY QUESTIONNAIRES
#
##########################################################################################
LegacyQuestionnaire::publishLegacyQuestionnaires(@patientList, $cronLogSer);

print "Got Legacy Questionnaires\n" if $verbose;

# Once everything is complete, we update the "last transferred" field for all controls
# Patient control
Patient::setPatientLastTransferredIntoOurDB($start_datetime);
# Alias control
Alias::setAliasLastTransferIntoOurDB($start_datetime);
# Post control
PostControl::setPostControlLastPublishedIntoOurDB($start_datetime);
# Educational material control
EducationalMaterialControl::setEduMatControlLastPublishedIntoOurDB($start_datetime);
# Test result control
TestResultControl::setTestResultLastPublishedIntoOurDB($start_datetime);


# Get the current time
my $current_datetime = strftime("%Y-%m-%d %H:%M:%S", localtime(time));

# Log that the script is finished in the cronlog
Cron::setCronLog("Completed", $current_datetime);

# Update the "Next Cron"
Cron::setNextCron();
