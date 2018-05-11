#!/usr/bin/perl

#---------------------------------------------------------------------------------
# A.Joseph 07-Aug-2015 ++ File: Patient.pm
#---------------------------------------------------------------------------------
# Perl module that creates a patient class. This module calls a constructor to 
# create a patient object that contains patient information stored as object 
# variables.
#
# There exists various subroutines to set patient information and compare patient
# information between two patient objects. 
# There exists various subroutines that use the Database.pm module to update the
# MySQL database.

package Patient; # Declare package name

use Exporter; # To export subroutines and variables
use Database; # Use our custom database module Database.pm
use Configs; # Use our custom configs module
use Email; # Use our custom email module
use Time::Piece; 
use POSIX;
use Storable qw(dclone); # for deep copies
use Data::Dumper;

# Get the current time
my $today = strftime("%Y-%m-%d %H:%M:%S", localtime(time));

#---------------------------------------------------------------------------------
# Connect to the database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Patient class 
#====================================================================================
sub new
{
	my $class = shift;
	my $patient = {
		_ser		    	=> undef,
		_sourceuid	    	=> undef,
        _id             	=> undef,
        _id2            	=> undef,
        _firstname      	=> undef,
        _lastname       	=> undef,
        _sex            	=> undef,
        _dob            	=> undef,
        _age            	=> undef,
        _picture        	=> undef,
        _ssn            	=> undef,
		_lasttransfer		=> undef,
        _accesslevel    	=> undef,
        _deathdate 			=> undef,
		_email 				=> undef,
		_firebaseuid		=> undef,
		_registrationdate	=> undef,
		_cronlogser			=> undef, 		
	};
	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $patient, $class; 
	return $patient;
}

#======================================================================================
# Subroutine to set the patient serial
#======================================================================================
sub setPatientSer
{
	my ($patient, $ser) = @_; # patient object with provided serial in arguments
	$patient->{_ser} = $ser; # set the serial
	return $patient->{_ser};
}

#======================================================================================
# Subroutine to set the patient source uid
#======================================================================================
sub setPatientSourceUID
{
	my ($patient, $sourceuid) = @_; # patient object with provided serial in arguments
	$patient->{_sourceuid} = $sourceuid; # set the serial
	return $patient->{_sourceuid};
}

#======================================================================================
# Subroutine to set the patient id
#======================================================================================
sub setPatientId
{
	my ($patient, $id) = @_; # patient object with provided id in arguments
	$patient->{_id} = $id; # set the id
	return $patient->{_id};
}

#======================================================================================
# Subroutine to set the patient id2
#======================================================================================
sub setPatientId2
{
	my ($patient, $id2) = @_; # patient object with provided id in arguments
	$patient->{_id2} = $id2; # set the id
	return $patient->{_id2};
}

#======================================================================================
# Subroutine to set the patient first name
#======================================================================================
sub setPatientFirstName
{
	my ($patient, $firstname) = @_; # patient object with provided name in arguments
	$patient->{_firstname} = $firstname; # set the name
	return $patient->{_firstname};
}

#======================================================================================
# Subroutine to set the patient last name
#======================================================================================
sub setPatientLastName
{
	my ($patient, $lastname) = @_; # patient object with provided name in arguments
	$patient->{_lastname} = $lastname; # set the name
	return $patient->{_lastname};
}

#======================================================================================
# Subroutine to set the patient date of birth
#======================================================================================
sub setPatientDOB
{
	my ($patient, $dob) = @_; # patient object with provided date in arguments
	$patient->{_dob} = $dob; # set the date
	return $patient->{_dob};
}

#======================================================================================
# Subroutine to set the patient age
#======================================================================================
sub setPatientAge
{
	my ($patient, $age) = @_; # patient object with provided age in arguments
	$patient->{_age} = $age; # set the age
	return $patient->{_age};
}

#======================================================================================
# Subroutine to set the patient sex
#======================================================================================
sub setPatientSex
{
	my ($patient, $sex) = @_; # patient object with provided sex in arguments
	$patient->{_sex} = $sex; # set the sex
	return $patient->{_sex};
}

#======================================================================================
# Subroutine to set the patient picture
#======================================================================================
sub setPatientPicture
{
	my ($patient, $picture) = @_; # patient object with provided picture in arguments
	$patient->{_picture} = $picture; # set the picture
	return $patient->{_picture};
}

#======================================================================================
# Subroutine to set the patient ssn
#======================================================================================
sub setPatientSSN
{
	my ($patient, $ssn) = @_; # patient object with provided ssn in arguments
	$patient->{_ssn} = $ssn; # set the ssn
	return $patient->{_ssn};
}

#======================================================================================
# Subroutine to set the patient last transfer
#======================================================================================
sub setPatientLastTransfer
{
	my ($patient, $lasttransfer) = @_; # patient object with provided datetime in arguments
	$patient->{_lasttransfer} = $lasttransfer; # set the datetime
	return $patient->{_lasttransfer};
}

#======================================================================================
# Subroutine to set the patient access level
#======================================================================================
sub setPatientAccessLevel
{
    my ($patient, $accesslevel) = @_; # patient object with provided level in arguments
    $patient->{_accesslevel} = $accesslevel; # set the level
    return $patient->{_accesslevel};
}


#======================================================================================
# Subroutine to set the patient death date
#======================================================================================
sub setPatientDeathDate
{
    my ($patient, $deathdate) = @_; # patient object with provided date in arguments
    $patient->{_deathdate} = $deathdate; # set the date
    return $patient->{_deathdate};
}

#======================================================================================
# Subroutine to set the patient email
#======================================================================================
sub setPatientEmail
{
	my ($patient, $email) = @_; # patient object with provided email in arguments 
	$patient->{_email} = $email; # set the email
	return $patient->{_email};
}

#======================================================================================
# Subroutine to set the patient firebase uid
#======================================================================================
sub setPatientFirebaseUID
{
	my ($patient, $firebaseuid) = @_; # patient object with provided uid in arguments 
	$patient->{_firebaseuid} = $firebaseuid; # set the uid
	return $patient->{_firebaseuid};
}

#======================================================================================
# Subroutine to set the patient registration date
#======================================================================================
sub setPatientRegistrationDate
{
    my ($patient, $registrationdate) = @_; # patient object with provided date in arguments
    $patient->{_registrationdate} = $registrationdate; # set the date
    return $patient->{_registrationdate};
}

#======================================================================================
# Subroutine to set the patient cron log serial
#======================================================================================
sub setPatientCronLogSer
{
	my ($patient, $cronlogser) = @_; # patient object with provided serial in arguments
	$patient->{_cronlogser} = $cronlogser; # set the serial
	return $patient->{_cronlogser};
}

#======================================================================================
# Subroutine to get the patient serial
#======================================================================================
sub getPatientSer
{
	my ($patient) = @_; # our patient object
	return $patient->{_ser};
}

#======================================================================================
# Subroutine to get the patient source uid
#======================================================================================
sub getPatientSourceUID
{
	my ($patient) = @_; # our patient object
	return $patient->{_sourceuid};
}

#======================================================================================
# Subroutine to get the patient id
#======================================================================================
sub getPatientId
{
	my ($patient) = @_; # our patient object
	return $patient->{_id};
}

#======================================================================================
# Subroutine to get the patient id2
#======================================================================================
sub getPatientId2
{
	my ($patient) = @_; # our patient object
	return $patient->{_id2};
}

#======================================================================================
# Subroutine to get the patient first name
#======================================================================================
sub getPatientFirstName
{
	my ($patient) = @_; # our patient object
	return $patient->{_firstname};
}

#======================================================================================
# Subroutine to get the patient last name
#======================================================================================
sub getPatientLastName
{
	my ($patient) = @_; # our patient object
	return $patient->{_lastname};
}

#======================================================================================
# Subroutine to get the patient date of birth
#======================================================================================
sub getPatientDOB
{
	my ($patient) = @_; # our patient object
	return $patient->{_dob};
}

#======================================================================================
# Subroutine to get the patient age
#======================================================================================
sub getPatientAge
{
	my ($patient) = @_; # our patient object
	return $patient->{_age};
}

#======================================================================================
# Subroutine to get the patient sex
#======================================================================================
sub getPatientSex
{
	my ($patient) = @_; # our patient object
	return $patient->{_sex};
}

#======================================================================================
# Subroutine to get the patient picture
#======================================================================================
sub getPatientPicture
{
	my ($patient) = @_; # our patient object
	return $patient->{_picture};
}

#======================================================================================
# Subroutine to get the patient ssn
#======================================================================================
sub getPatientSSN
{
	my ($patient) = @_; # our patient object
	return $patient->{_ssn};
}

#======================================================================================
# Subroutine to get the patient last transfer
#======================================================================================
sub getPatientLastTransfer
{
	my ($patient) = @_; # our patient object
	return $patient->{_lasttransfer};
}

#======================================================================================
# Subroutine to get the patient access level
#======================================================================================
sub getPatientAccessLevel
{
    my ($patient) = @_; # our patient object
    return $patient->{_accesslevel};
}

#======================================================================================
# Subroutine to get the patient death date
#======================================================================================
sub getPatientDeathDate
{
    my ($patient) = @_; # our patient object
    return $patient->{_deathdate};
}

#======================================================================================
# Subroutine to get the patient email
#======================================================================================
sub getPatientEmail
{
    my ($patient) = @_; # our patient object
    return $patient->{_email};
}

#======================================================================================
# Subroutine to get the patient firebase uid
#======================================================================================
sub getPatientFirebaseUID
{
    my ($patient) = @_; # our patient object
    return $patient->{_firebaseuid};
}

#======================================================================================
# Subroutine to get the patient registration date
#======================================================================================
sub getPatientRegistrationDate
{
    my ($patient) = @_; # our patient object
    return $patient->{_registrationdate};
}

#======================================================================================
# Subroutine to get the patient cron log serial
#======================================================================================
sub getPatientCronLogSer
{
	my ($patient) = @_; # our patient object
	return $patient->{_cronlogser};
}

#======================================================================================
# Subroutine to get all patient info from source dbs
#======================================================================================
sub getPatientInfoFromSourceDBs 
{
    my ($Patient) = @_; # our patient object

    my @patientList = (); # initialize a list 

    my $patientSSN      = $Patient->getPatientSSN(); # retrieve the ssn
    my $lastTransfer    = $Patient->getPatientLastTransfer();
    my $registrationDate 	= $Patient->getPatientRegistrationDate();

    ######################################
    # ARIA
    ######################################
    my $sourceDBSer = 1; # ARIA
    my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);
    if ($sourceDatabase) {

    	# mssql truncates texts to 4096 bytes so need to set textsize (for picture) to a high number
    	$sourceDatabase->do('set textsize 100000');

	    my $sourcePatient  = undef;

	    my $patientInfo_sql = "
	        SELECT DISTINCT 
	            pt.PatientSer,
	            pt.FirstName,
	            pt.LastName,
	            pt.PatientId,
	            pt.PatientId2,
	            CONVERT(VARCHAR, pt.DateOfBirth, 120),
	            ph.Picture,
	            RTRIM(pt.Sex),
	            CONVERT(VARCHAR, ppt.DeathDate, 120),
	            LEN(ph.Picture)
	        FROM 
	            variansystem.dbo.Patient pt
	        LEFT JOIN variansystem.dbo.Photo ph
	        ON pt.PatientSer       	= ph.PatientSer
	        LEFT JOIN variansystem.dbo.PatientParticular ppt 
	        ON ppt.PatientSer 		= pt.PatientSer
	        WHERE
	            LEFT(LTRIM(pt.SSN), 12)   = '$patientSSN'
	    ";

		# prepare query
		my $query = $sourceDatabase->prepare($patientInfo_sql)
		    or die "Could not prepare query: " . $sourceDatabase->errstr;

	    # execute query
	    $query->execute()
	        or die "Could not execute query: " . $query->errstr;

	    #print "$patientInfo_sql\n";

	    my $data = $query->fetchall_arrayref();
		foreach my $row (@$data) {
	   # while (my @data = $query->fetchrow_array()) {
	    
	        $sourcePatient  = new Patient();

	        my $sourceuid       = $row->[0];
	        my $firstname       = $row->[1];
	        my $lastname        = $row->[2];
	        my $id              = $row->[3];
	        my $id2             = $row->[4];
	        my $dob             = $row->[5];
	        my $age             = getAgeAtDate($dob, $today);
	        my $picture         = $row->[6];
	        my $sex             = $row->[7];
	        my $deathdate 		= $row->[8];

	        # set the information
	        $sourcePatient->setPatientSSN($patientSSN);
	        $sourcePatient->setPatientLastTransfer($lastTransfer);
	        $sourcePatient->setPatientRegistrationDate($registrationDate);

	        $sourcePatient->setPatientSourceUID($sourceuid);
	        $sourcePatient->setPatientFirstName($firstname);
	        $sourcePatient->setPatientLastName($lastname);
	        $sourcePatient->setPatientId($id);
	        $sourcePatient->setPatientId2($id2);
	        $sourcePatient->setPatientDOB($dob);
	        $sourcePatient->setPatientAge($age);
	        $sourcePatient->setPatientPicture($picture);
	        $sourcePatient->setPatientSex($sex);
	        $sourcePatient->setPatientDeathDate($deathdate);
	    }

	    if ($sourcePatient) {push(@patientList, $sourcePatient);}

	    # db disconnect
	    $sourceDatabase->disconnect();
	}

	# MediVisit section commented out as to only use one source for updating patient information
	# Hopefully in the future there will be one central source for patient info
=pod
    ######################################
    # MediVisit
    ######################################

    my $sourceDBSer = 2; # WaitRoomManagement
    my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);
    if ($sourceDatabase) {

	    my $sourcePatient  = undef;

	    my $patientInfo_sql = "
	        SELECT DISTINCT 
	            pt.PatientSerNum,
	            pt.FirstName,
	            pt.LastName,
	            pt.PatientId
	        FROM
	            Patient pt
	        WHERE
	            pt.SSN      LIKE '$patientSSN%'
	    ";  

		# prepare query
		my $query = $sourceDatabase->prepare($patientInfo_sql)
		    or die "Could not prepare query: " . $sourceDatabase->errstr;

	    # execute query
	    $query->execute()
	        or die "Could not execute query: " . $query->errstr;

	    while (my @data = $query->fetchrow_array()) {

	        $sourcePatient  = new Patient();

	        my $sourceuid      = $data[0];
	        my $firstname      = $data[1];
	        my $lastname       = $data[2];
	        my $id             = $data[3];

	        $sourcePatient->setPatientSSN($patientSSN);
	        $sourcePatient->setPatientLastTransfer($lastTransfer);
	        $sourcePatient->setPatientUserSer($patientUserSer);

	        $sourcePatient->setPatientSourceUID($sourceuid);
	        $sourcePatient->setPatientSourceDatabaseSer($sourceDBSer);
	        $sourcePatient->setPatientFirstName($firstname);
	        $sourcePatient->setPatientLastName($lastname);
	        $sourcePatient->setPatientId($id);
	    }

	    if ($sourcePatient) {push(@patientList, $sourcePatient);}

	    # db disconnect
	    $sourceDatabase->disconnect();
	}
=cut

	######################################
    # MOSAIQ
    ######################################
    my $sourceDBSer = 3; # MOSAIQ
    my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);
    if ($sourceDatabase) {

	    my $sourcePatient  = undef;

	    my $patientInfo_sql = "SELECT 'QUERY_HERE'";

		# prepare query
		my $query = $sourceDatabase->prepare($patientInfo_sql)
		    or die "Could not prepare query: " . $sourceDatabase->errstr;

	    # execute query
	    $query->execute()
	        or die "Could not execute query: " . $query->errstr;

	    while (my @data = $query->fetchrow_array()) {
	    
	        #$sourcePatient  = new Patient(); # uncomment for use

	        # use setters to set appropriate information from query
	       	
	    }

	    if ($sourcePatient) {push(@patientList, $sourcePatient);}

	    # db disconnect
	    $sourceDatabase->disconnect();
	}

    return @patientList;

}

#======================================================================================
# Subroutine to get patients marked for update
#======================================================================================
sub getPatientsMarkedForUpdate
{
    my ($cronLogSer) = @_; # cron log serial in args
	
	my @patientList = (); # initialize list of patient objects
	my ($lasttransfer, $ssn, $registrationdate);
	
	# Query
	my $patients_sql = "
		SELECT DISTINCT
			PatientControl.LastTransferred,
            Patient.SSN,
            Patient.RegistrationDate
		FROM
			PatientControl,
            Patient
		WHERE
            PatientControl.PatientUpdate        = 1
        AND Patient.PatientSerNum               = PatientControl.PatientSerNum
	";

	# prepare query
	my $query = $SQLDatabase->prepare($patients_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

		my $Patient = new Patient(); # patient object

		$lasttransfer		= $data[0];
        $ssn            	= $data[1];
        $registrationdate 	= $data[2];

		# set patient information
		$Patient->setPatientLastTransfer($lasttransfer);
        $Patient->setPatientSSN($ssn);
        $Patient->setPatientRegistrationDate($registrationdate);
		$Patient->setPatientCronLogSer($cronLogSer);

		push(@patientList, $Patient);
	}

	return @patientList;
}

#======================================================================================
# Subroutine to block a patient
#======================================================================================
sub blockPatient
{
	my ($patient, $reason) = @_; # patient object in args

	# get patient serial
	my $patientSer = $patient->getPatientSer();
	# get uid
	my $firebaseUID = $patient->getPatientFirebaseUID();

	my $update_sql = "
		UPDATE
			Patient
		SET
			BlockedStatus 	= 1,
		 	StatusReasonTxt = \"$reason\"
		WHERE
			PatientSerNum = $patientSer
	";

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# call our nodejs script to block user on Firebase
	my $command = "/usr/bin/node " . $Configs::FRONTEND_ABS_PATH . 'js/firebaseSetBlock.js --blocked=1 --uid=' . $firebaseUID;

	my $response = system($command);

	# response = 0 (success); otherwise failed


}

#======================================================================================
# Subroutine to unset patient control
#======================================================================================
sub unsetPatientControl
{
	my ($patient) = @_; # patient object in args

	# get patient serial
	my $patientSer = $patient->getPatientSer();

	my $update_sql = "
		UPDATE
			PatientControl
		SET
			PatientUpdate = 0
		WHERE	
			PatientSerNum = $patientSer
	";

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

}

#======================================================================================
# Subroutine to set/update the "last transferred" field to current time 
#======================================================================================
sub setPatientLastTransferredIntoOurDB
{
	my ($current_datetime) = @_; # current datetime in args

	my $update_sql = "

		UPDATE 
			PatientControl
		SET
			LastTransferred	= '$current_datetime',
            LastUpdated     = LastUpdated
		WHERE
			PatientUpdate 	= 1
	";

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
}

#======================================================================================
# Subroutine to get patient access level from patient serial
#======================================================================================
sub getPatientAccessLevelFromSer
{
    my ($patientSer) = @_; # get serial from args

    my $accesslevel = 1;
    my $sql = "
        SELECT 
            pt.Accesslevel
        FROM
            Patient pt 
        WHERE
            pt.PatientSerNum = '$patientSer'
    ";

    # prepare query
    my $query = $SQLDatabase->prepare($sql)
        or die "Could not prepare query: " . $SQLDatabase->errstr;

    # execute query
    $query->execute()
        or die "Could not execute query: " . $query->errstr;

    while (my @data = $query->fetchrow_array()) {
        $accesslevel = $data[0];
    }

    return $accesslevel

}

#======================================================================================
# Subroutine to check if our patient exists in our MySQL db
#	@return: patient object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
    my ($patient) = @_; # our patient object

    my $ssn             	= $patient->getPatientSSN();
    my $lastTransfer    	= $patient->getPatientLastTransfer();
    my $registrationDate 	= $patient->getPatientRegistrationDate();


    my $PatientSSNInDB = 0; # false by default. Will be true if patient exists
	my $ExistingPatient = (); # data to be entered if patient exists

	# for query results
    my ($ser, $sourceuid, $id, $id2, $firstname, $lastname, $sex, $dob, $age, $picture, $deathdate, $email, $firebaseuid);
 
    my $inDB_sql = "
        SELECT DISTINCT
            Patient.PatientSerNum,
            Patient.PatientAriaSer,
            Patient.PatientId,
            Patient.PatientId2,
            Patient.FirstName,
            Patient.LastName,
            Patient.Sex,
            Patient.DateOfBirth,
			Patient.Age,
            Patient.ProfileImage,
            Patient.SSN,
            Patient.DeathDate,
			Patient.Email,
			Users.Username
        FROM
            Patient,
			Users
        WHERE
            Patient.SSN     		= '$ssn'
		AND Patient.PatientSerNum 	= Users.UserTypeSerNum
		AND Users.UserType 			= 'Patient'
    ";
	# prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        $ser                    = $data[0];
        $sourceuid              = $data[1];
        $id                     = $data[2];
        $id2                    = $data[3];
        $firstname              = $data[4];
        $lastname               = $data[5];
        $sex                    = $data[6];
        $dob                    = $data[7];
        $age                    = $data[8];
        $picture                = $data[9];
        $PatientSSNInDB         = $data[10];
        $deathdate 				= $data[11];
		$email 					= $data[12];
		$firebaseuid 			= $data[13];
    }

    if ($PatientSSNInDB) {

        $ExistingPatient = new Patient(); # initialze patient object

        $ExistingPatient->setPatientSer($ser);
        $ExistingPatient->setPatientSourceUID($sourceuid);
        $ExistingPatient->setPatientId($id);
        $ExistingPatient->setPatientId2($id2);
        $ExistingPatient->setPatientFirstName($firstname);
        $ExistingPatient->setPatientLastName($lastname);
        $ExistingPatient->setPatientSex($sex);
        $ExistingPatient->setPatientDOB($dob);
        $ExistingPatient->setPatientAge($age);
        $ExistingPatient->setPatientPicture($picture);
        $ExistingPatient->setPatientLastTransfer($lastTransfer);
        $ExistingPatient->setPatientRegistrationDate($registrationDate);
        $ExistingPatient->setPatientSSN($PatientSSNInDB);
        $ExistingPatient->setPatientDeathDate($deathdate);
		$ExistingPatient->setPatientEmail($email);
		$ExistingPatient->setPatientFirebaseUID($firebaseuid);

        return $ExistingPatient; # this is true (ie. patient exists, return object)
	}

	else {return $ExistingPatient;} # this is false (ie. patient DNE, return empty)
}
    
#======================================================================================
# Subroutine to insert our patient info in our database
#======================================================================================
sub insertPatientIntoOurDB
{
	my ($patient) = @_; # our patient object to insert

	# Retrieve all the necessary details from this object
    my $sourceuid           = $patient->getPatientSourceUID();
    my $id                  = $patient->getPatientId();
    my $id2                 = $patient->getPatientId2();
    my $firstname           = $patient->getPatientFirstName();
    my $lastname            = $patient->getPatientLastName();
    my $sex                 = $patient->getPatientSex();
    my $dob                 = $patient->getPatientDOB();
	my $age 				= $patient->getPatientAge();
    my $picture             = $patient->getPatientPicture();
    my $deathdate 			= $patient->getPatientDeathDate();

    my $insert_sql = "
        INSERT INTO
            Patient (
                PatientAriaSer,
                PatientId,
                PatientId2,
                FirstName,
                LastName,
                Sex,
                DateOfBirth,
				Age,
                ProfileImage,
                DeathDate
            )
        VALUES (
            '$sourceuid',
            '$id',
            '$id2',
            \"$firstname\",
            \"$lastname\",
            '$sex',
            '$dob',
			'$age',
            '$picture',
            '$deathdate'
        )
    ";

	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the PatientSer
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the Serial in our patient object
	$patient->setPatientSer($ser);

	return $patient;
}
    
#======================================================================================
# Subroutine to update our database with the patient's updated info
#======================================================================================
sub updateDatabase
{
    my ($patient) = @_; # our patient object to update

    my $patientSourceUID    = $patient->getPatientSourceUID();
    my $patientId           = $patient->getPatientId();
    my $patientId2          = $patient->getPatientId2();
    my $patientFirstName    = $patient->getPatientFirstName();
    my $patientLastName     = $patient->getPatientLastName();
    my $patientDOB          = $patient->getPatientDOB();
	my $patientAge 			= $patient->getPatientAge();
    my $patientPicture      = $patient->getPatientPicture();
    my $patientSex          = $patient->getPatientSex();
    my $patientSSN          = $patient->getPatientSSN();
    my $patientDeathDate 	= $patient->getPatientDeathDate();

    my $update_sql = "
        UPDATE
            Patient
        SET
            PatientAriaSer          = '$patientSourceUID',
            PatientId               = '$patientId',
            PatientId2              = '$patientId2',
            FirstName               = \"$patientFirstName\",
            LastName                = \"$patientLastName\",
            Sex                     = '$patientSex',
            DateOfBirth             = '$patientDOB',
			Age 					= '$patientAge',
            ProfileImage            = '$patientPicture',
            DeathDate 				= '$patientDeathDate'
        WHERE
            SSN                     = '$patientSSN'
    ";

    #print "$update_sql\n";
 	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

}

#======================================================================================
# Subroutine to compare two patient objects. If different, use setter functions
# to update patient object.
#======================================================================================
sub compareWith
{
	my ($SuspectPatient, $OriginalPatient) = @_; # our two patient objects from arguments
	my $UpdatedPatient = dclone($OriginalPatient); 

	my $change = 0; # boolean to recognize an actual difference between objects

	# retrieve parameters
	# Suspect Patient...
    my $SPatientSourceUID   = $SuspectPatient->getPatientSourceUID();
	my $SPatientId			= $SuspectPatient->getPatientId();
	my $SPatientId2			= $SuspectPatient->getPatientId2();
	my $SPatientDOB			= $SuspectPatient->getPatientDOB();
	my $SPatientAge 		= $SuspectPatient->getPatientAge();
	my $SPatientSex			= $SuspectPatient->getPatientSex();
    my $SPatientFirstName   = $SuspectPatient->getPatientFirstName();
    my $SPatientLastName    = $SuspectPatient->getPatientLastName();
    my $SPatientPicture     = $SuspectPatient->getPatientPicture();
    my $SPatientDeathDate 	= $SuspectPatient->getPatientDeathDate();
	
	# Original Patient...
    my $OPatientSourceUID   = $OriginalPatient->getPatientSourceUID();
	my $OPatientId			= $OriginalPatient->getPatientId();
	my $OPatientId2			= $OriginalPatient->getPatientId2();
	my $OPatientDOB			= $OriginalPatient->getPatientDOB();
	my $OPatientAge 		= $OriginalPatient->getPatientAge();
	my $OPatientSex			= $OriginalPatient->getPatientSex();
    my $OPatientFirstName   = $OriginalPatient->getPatientFirstName();
    my $OPatientLastName    = $OriginalPatient->getPatientLastName();
    my $OPatientPicture     = $OriginalPatient->getPatientPicture();
    my $OPatientDeathDate 	= $OriginalPatient->getPatientDeathDate();

	# go through each parameter
    if ($SPatientSourceUID ne $OPatientSourceUID) {

		$change = 1; # change occurred
		print "Patient Source UID has changed from $OPatientSourceUID to $SPatientSourceUID!\n";
		my $updatedUID = $UpdatedPatient->setPatientSourceUID($SPatientSourceUID); # update patient id
		print "Will update database entry to '$updatedUID'.\n";
	}
	if ($SPatientId ne $OPatientId) {

		$change = 1; # change occurred
		print "Patient ID has changed from $OPatientId to $SPatientId!\n";
		my $updatedId = $UpdatedPatient->setPatientId($SPatientId); # update patient id
		print "Will update database entry to '$updatedId'.\n";
	}
	if ($SPatientId2 ne $OPatientId2) {

		$change = 1; # change occurred
		print "Patient ID2 has changed from $OPatientId2 to $SPatientId2!\n";
		my $updatedId2 = $UpdatedPatient->setPatientId2($SPatientId2); # update patient id2
		print "Will update database entry to \"$updatedId2\".\n";
	}	
	if ($SPatientDOB ne $OPatientDOB and (isValidDate($SPatientDOB) or isValidDate($OPatientDOB))) {

		$change = 1; # change occurred
		print "Patient Date of Birth has changed from $OPatientDOB to $SPatientDOB!\n";
		my $updatedDOB = $UpdatedPatient->setPatientDOB($SPatientDOB); # update patient date of birth
		print "Will update database entry to \"$updatedDOB\".\n";

	}
	if ($SPatientAge ne $OPatientAge) {

		$change = 1; # change occurred
		print "Patient Age has changed from $OPatientAge to $SPatientAge!\n";
		my $updatedAge = $UpdatedPatient->setPatientAge($SPatientAge); # update patient age
		print "Will update database entry to \"$updatedAge\".\n";

		# block patient if patient passed 13 years of age and send email
		if ($OPatientAge < 14 && $SPatientAge >= 14 && $OPatientAge > 0) {
			blockPatient($UpdatedPatient, "Patient passed 13 years of age");
			my $patientser = $UpdatedPatient->getPatientSer();
			my $patientemail = $UpdatedPatient->getPatientEmail();
			my $cronlogser = $UpdatedPatient->getPatientCronLogSer();
			my $email = Email::getEmailControlDetails($patientser, "PaedPatientBlock");
			$email->setEmailToAddress($patientemail);
			$email->setEmailCronLogSer($cronlogser);
			$email->sendEmail($patientser);
		}
	}
	if ($SPatientSex ne $OPatientSex) {

		$change = 1; # change occurred
		print "Patient Sex has changed from $OPatientSex to $SPatientSex!\n";
		my $updatedSex = $UpdatedPatient->setPatientSex($SPatientSex); # update patient sex
		print "Will update database entry to \"$updatedSex\".\n";
	}
	if ($SPatientFirstName ne $OPatientFirstName) {

		$change = 1; # change occurred
		print "Patient First Name has changed from $OPatientFirstName to $SPatientFirstName!\n";
		my $updatedFirstName = $UpdatedPatient->setPatientFirstName($SPatientFirstName); # update patient first name
		print "Will update database entry to \"$updatedFirstName\".\n";
	}
	if ($SPatientLastName ne $OPatientLastName) {

		$change = 1; # change occurred
		print "Patient Last Name has changed from $OPatientLastName to $SPatientLastName!\n";
		my $updatedLastName = $UpdatedPatient->setPatientLastName($SPatientLastName); # update patient last name
		print "Will update database entry to \"$updatedLastName\".\n";
	}
	if ($SPatientPicture ne $OPatientPicture) {

		$change = 1; # change occurred
		print "Patient Picture has changed from $OPatientPicture to $SPatientPicture!\n";
		my $updatedPicture = $UpdatedPatient->setPatientPicture($SPatientPicture); # update patient picture
		print "Will update database entry to \"$updatedPicture\".\n";
	}
	if ($SPatientDeathDate ne $OPatientDeathDate and (isValidDate($SPatientDeathDate) or isValidDate($OPatientDeathDate))) {

		$change = 1; # change occurred
		print "Patient Death Date has changed from $OPatientDeathDate to $SPatientDeathDate!\n";
		my $updatedDeathDate = $UpdatedPatient->setPatientDeathDate($SPatientDeathDate); # update patient death date 
		print "Will update database entry to \"$updatedDeathDate\" and block patient.\n";

		# block deceased patient
		blockPatient($UpdatedPatient, "Deceased patient"); 
		# turn off patient control 
		unsetPatientControl($UpdatedPatient);
	}
	
	return ($UpdatedPatient, $change);
}

#======================================================================================
# Subroutine to get age from a date
#======================================================================================
sub getAgeAtDate
{
    my ($dob, $date) = @_;

    if (!isValidDate($dob)) {return -1;} # dob undef

    my $diff = $date - $dob;

    return int($diff);
}

#======================================================================================
# Subroutine to determine if date is invalid
#======================================================================================
sub isValidDate
{
	my ($date) = @_;

	if (!$date or $date eq '1970-01-01 00:00:00' or $date eq '0000-00-00 00:00:00') {return undef;}
	else {return 1;}
}

#exit module 
1;
