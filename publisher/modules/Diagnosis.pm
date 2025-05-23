# SPDX-FileCopyrightText: Copyright (C) 2015 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

#---------------------------------------------------------------------------------
# A.Joseph 10-Aug-2015 ++ File: Diagnosis.pm
#---------------------------------------------------------------------------------
# Perl module that creates a diagnosis class. This module calls a constructor to
# create a diagnosis object that contains diagnosis information stored as object
# variables.
#
# There exists various subroutines to set diagnosis information and compare diagnosis
# information between two diagnosis objects.
# There exists various subroutines that use the Database.pm module to update the
# MySQL database and check if a diagnosis exists already in this database.

package Diagnosis; # Declare package name


use Exporter; # To export subroutines and variables
use Database; # Use our custom database module Database.pm
use Time::Piece;
use Storable qw(dclone); # for deep copies

use Patient; # our custom patient module

#---------------------------------------------------------------------------------
# Connect to the databases
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Diagnosis class
#====================================================================================
sub new
{
	my $class = shift;
	my $diagnosis = {
		_ser		    => undef,
		_sourceuid	    => undef,
        _sourcedbser    => undef,
		_patientser	    => undef,
		_datestamp	    => undef,
		_description	=> undef,
        _code           => undef,
        _stage 			=> undef,
        _stagecriteria 	=> undef,
	};

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $diagnosis, $class;
	return $diagnosis;
}

#====================================================================================
# Subroutine to set the diagnosis serial
#====================================================================================
sub setDiagnosisSer
{
	my ($diagnosis, $ser) = @_; # diagnosis object with provided serial in arguments
	$diagnosis->{_ser} = $ser; # set the serial
	return $diagnosis->{_ser};
}

#====================================================================================
# Subroutine to set the diagnosis source database serial
#====================================================================================
sub setDiagnosisSourceDatabaseSer
{
	my ($diagnosis, $sourcedbser) = @_; # diagnosis object with provided serial in arguments
	$diagnosis->{_sourcedbser} = $sourcedbser; # set the serial
	return $diagnosis->{_sourcedbser};
}

#====================================================================================
# Subroutine to set the diagnosis source uid
#====================================================================================
sub setDiagnosisSourceUID
{
	my ($diagnosis, $sourceuid) = @_; # diagnosis object with provided uid in arguments
	$diagnosis->{_sourceuid} = $sourceuid; # set the uid
	return $diagnosis->{_sourceuid};
}

#====================================================================================
# Subroutine to set the diagnosis patient serial
#====================================================================================
sub setDiagnosisPatientSer
{
	my ($diagnosis, $patientser) = @_; # diagnosis object with provided serial in arguments
	$diagnosis->{_patientser} = $patientser; # set the serial
	return $diagnosis->{_patientser};
}

#====================================================================================
# Subroutine to set diagnosis DateStamp
#====================================================================================
sub setDiagnosisDateStamp
{
	my ($diagnosis, $datestamp) = @_; # diagnosis object with provided datestamp in arguments
	$diagnosis->{_datestamp} = $datestamp; # set the datestamp
	return $diagnosis->{_datestamp};
}

#====================================================================================
# Subroutine to set diagnosis description
#====================================================================================
sub setDiagnosisDescription
{
	my ($diagnosis, $description) = @_; # diagnosis object with provided description in arguments
	$diagnosis->{_description} = $description; # set the description
	return $diagnosis->{_description};
}

#====================================================================================
# Subroutine to set diagnosis code
#====================================================================================
sub setDiagnosisCode
{
	my ($diagnosis, $code) = @_; # diagnosis object with provided code in arguments
	$diagnosis->{_code} = $code; # set the code
	return $diagnosis->{_code};
}

#====================================================================================
# Subroutine to set diagnosis stage
#====================================================================================
sub setDiagnosisStage
{
	my ($diagnosis, $stage) = @_; # diagnosis object with provided stage in arguments
	$diagnosis->{_stage} = $stage; # set the stage
	return $diagnosis->{_stage};
}

#====================================================================================
# Subroutine to set diagnosis stage criteria
#====================================================================================
sub setDiagnosisStageCriteria
{
	my ($diagnosis, $stagecriteria) = @_; # diagnosis object with provided stage criteria in arguments
	$diagnosis->{_stagecriteria} = $stagecriteria; # set the stage criteria
	return $diagnosis->{_stagecriteria};
}

#======================================================================================
# Subroutine to get the diagnosis serial
#======================================================================================
sub getDiagnosisSer
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_ser};
}

#======================================================================================
# Subroutine to get the diagnosis source database serial
#======================================================================================
sub getDiagnosisSourceDatabaseSer
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_sourcedbser};
}

#======================================================================================
# Subroutine to get the diagnosis source uid
#======================================================================================
sub getDiagnosisSourceUID
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_sourceuid};
}

#======================================================================================
# Subroutine to get the diagnosis patient serial
#======================================================================================
sub getDiagnosisPatientSer
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_patientser};
}

#======================================================================================
# Subroutine to get the diagnosis DateStamp
#======================================================================================
sub getDiagnosisDateStamp
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_datestamp};
}

#======================================================================================
# Subroutine to get the diagnosis description
#======================================================================================
sub getDiagnosisDescription
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_description};
}

#======================================================================================
# Subroutine to get the diagnosis code
#======================================================================================
sub getDiagnosisCode
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_code};
}

#======================================================================================
# Subroutine to get the diagnosis stage
#======================================================================================
sub getDiagnosisStage
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_stage};
}

#======================================================================================
# Subroutine to get the diagnosis stage criteria
#======================================================================================
sub getDiagnosisStageCriteria
{
	my ($diagnosis) = @_; # our diagnosis object
	return $diagnosis->{_stagecriteria};
}

#======================================================================================
# Subroutine to get all diagnoses from the ARIA db since last cron
#======================================================================================
sub getDiagnosesFromSourceDB
{
	my @patientList = @_[0];
    my $global_patientInfo_sql = @_[1];

	my @diagnosisList = (); # initialize a list for diagnosis objects

	# for query results
	my ($sourceuid, $datestamp, $description, $code);

	######################################
    # ARIA
    ######################################
    my $sourceDBSer = 1;
	{
		my $sourceDatabase	= Database::connectToSourceDatabase($sourceDBSer);

        if ($sourceDatabase) {

			my $patientInfo_sql = "
				use VARIAN;

                IF OBJECT_ID('tempdb.dbo.#tempDiag', 'U') IS NOT NULL
                	DROP TABLE #tempDiag;

				IF OBJECT_ID('tempdb.dbo.#tempPatient', 'U') IS NOT NULL
					DROP TABLE #tempPatient;

				WITH PatientInfo (PatientAriaSer, LastTransfer, PatientSerNum) AS (
			";
			$patientInfo_sql .= $global_patientInfo_sql; #use pre-loaded patientInfo from dataControl
			$patientInfo_sql .= ")
			Select c.* into #tempDiag
			from PatientInfo c;
			Create Index temporaryindexDiag1 on #tempDiag (PatientAriaSer);
			Create Index temporaryindexDiag2 on #tempDiag (PatientSerNum);

			Select p.PatientSer, p.PatientId into #tempPatient
			from VARIAN.dbo.Patient p;
			Create Index temporaryindexPatient2 on #tempPatient (PatientSer);
			";

			my $diagInfo_sql = $patientInfo_sql . "
		    	SELECT DISTINCT
			    	dx.DiagnosisSer,
				    CONVERT(VARCHAR, dx.DateStamp, 120),
    				RTRIM(REPLACE(REPLACE(dx.Description,'Malignant neoplasm','malignant neoplasm'),'malignant neoplasm','Ca')),
                    dx.DiagnosisId,
                    pmdx.SummaryStage,
                    RTRIM(pmdx.StageCriteria),
					PatientInfo.PatientSerNum
		    	FROM
			    	VARIAN.dbo.Diagnosis dx with(nolock),
				    VARIAN.dbo.Patient pt with(nolock),
				    VARIAN.dbo.PrmryDiagnosis pmdx with(nolock),
					#tempDiag as PatientInfo
    			WHERE
	    		 	dx.DiagnosisSer 		= pmdx.DiagnosisSer
			    AND	dx.Description 			NOT LIKE '%ERROR%'
    			AND	dx.HstryDateTime    	> PatientInfo.LastTransfer
	    		AND dx.DateStamp			> '1970-01-01 00:00:00'
				AND dx.PatientSer 			= PatientInfo.PatientAriaSer
		    ";

    		# prepare query
	    	my $query = $sourceDatabase->prepare($diagInfo_sql)
		    	or die "Could not prepare query: " . $sourceDatabase->errstr;

		    # execute query
	    	$query->execute()
			    or die "Could not execute query: " . $query->errstr;

            my $data = $query->fetchall_arrayref();
            foreach my $row (@$data) {

    			my $diagnosis = new Diagnosis(); # new diagnosis object

	    		$sourceuid		= $row->[0];
		    	$datestamp		= $row->[1];
			    $description	= $row->[2];
                $code           = $row->[3];
                $stage 			= $row->[4];
                $stagecriteria 	= $row->[5];
				$patientSer 	= $row->[6];

	    		# set diagnostic information
		    	$diagnosis->setDiagnosisSourceUID($sourceuid);
			    $diagnosis->setDiagnosisDateStamp($datestamp);
    			$diagnosis->setDiagnosisDescription($description);
	    		$diagnosis->setDiagnosisPatientSer($patientSer);
                $diagnosis->setDiagnosisCode($code);
                $diagnosis->setDiagnosisStage($stage);
                $diagnosis->setDiagnosisStageCriteria($stagecriteria);
                $diagnosis->setDiagnosisSourceDatabaseSer($sourceDBSer);

	    		push(@diagnosisList, $diagnosis);
		    }

            $sourceDatabase->disconnect();
        }
	}

	######################################
	# MediVisit
	######################################
	my $sourceDBSer = 2; # MediVisit
	{
		my $sourceDatabase	= Database::connectToSourceDatabase($sourceDBSer);

        if ($sourceDatabase) {

        	my $diagInfo_sql = "SELECT 'QUERY_HERE'";

        	# prepare query
	    	# my $query = $sourceDatabase->prepare($diagInfo_sql)
		    # 	or die "Could not prepare query: " . $sourceDatabase->errstr;

		    # # execute query
	    	# $query->execute()
			#     or die "Could not execute query: " . $query->errstr;

            # my $data = $query->fetchall_arrayref();
            # foreach my $row (@$data) {

            # 	#my $diagnosis = new Diagnosis(); # uncomment for use

            # 	# use setters to set appropriate diagnosis information from query

            # 	#push(@diagnosisList, $diagnosis);
            # }

            # $sourceDatabase->disconnect();
        }
	}

	######################################
	# MOSAIQ
	######################################
	my $sourceDBSer = 3; # MOSAIQ
	{
	    my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);
        if ($sourceDatabase) {

        	my $diagInfo_sql = "SELECT 'QUERY_HERE'";

        	# prepare query
	    	# my $query = $sourceDatabase->prepare($diagInfo_sql)
		    # 	or die "Could not prepare query: " . $sourceDatabase->errstr;

		    # # execute query
	    	# $query->execute()
			#     or die "Could not execute query: " . $query->errstr;

            # my $data = $query->fetchall_arrayref();
            # foreach my $row (@$data) {

            # 	#my $diagnosis = new Diagnosis(); # uncomment for use

            # 	# use setters to set appropriate diagnosis information from query

            # 	#push(@diagnosisList, $diagnosis);
            # }

            # $sourceDatabase->disconnect();
        }
	}

	return @diagnosisList;
}

#======================================================================================
# Subroutine to get the closest diagnosis in time given the patient serial and a date
# 	@return: diagnosis serial
#======================================================================================
sub getClosestDiagnosis
{
	my ($patientSer, $referencedate) = @_; # get the patient serial and a ref date

	my ($closestdate, $diagnosisSer);

	# Since the diagnosis creation date will be ascending,
	# if the first diagnosis date is already passed the ref date in time,
 	# then we'll just take the first diagnosis, and break.
	my $first = 1;

	my $date_sql = "
		SELECT DISTINCT
			Diagnosis.CreationDate,
			Diagnosis.DiagnosisSerNum
		FROM
			Diagnosis
		WHERE
			Diagnosis.PatientSerNum	= '$patientSer'
		ORDER BY
			Diagnosis.CreationDate ASC
	";

	# prepare query
	my $query = $SQLDatabase->prepare($date_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# how this will work is that we loop each creation date until
	# said date passes the reference date. In other words, the
	# delay gets smaller and may turn negative. The smallest,
	# non-negative delay is the closest date.
	while (my @data = $query->fetchrow_array()) {

		$closestdate = $data[0];

		my $delay_sql = "
			SELECT TIMESTAMPDIFF(DAY, '$closestdate', '$referencedate')
		";

		# prepare query
		my $delayquery = $SQLDatabase->prepare($delay_sql)
			or die "Could not prepare query: " . $SQLDatabase->errstr;

		# execute query
		$delayquery->execute()
			or die "Could not execute query: " . $delayquery->errstr;

		my @delaydata = $delayquery->fetchrow_array();

		my $delay = int($delaydata[0]);

		# reached negative delay, break out of loop
		if ($delay < 0) {
			# however, if the first diagnosis is passed the ref date
			# by less than 100 days, then count it as the closest diagnosis
			if ($first and $delay > -100) {
				$diagnosisSer = $data[1];
				last;
			}
			last;
		}

		# assign diagnosis serial
		$diagnosisSer = $data[1];

		$first = undef;	 # passed the first diagnosis

	}

	return $diagnosisSer;
}

#======================================================================================
# Subroutine to get diagnosis name from our db given a diagnosis serial
#======================================================================================
sub getDiagnosisNameFromOurDB
{
    my ($diagnosisSer) = @_; # args

    my $diagnosisName; # initialize

    my $select_sql = "
        SELECT DISTINCT
            dc.DiagnosisTranslationSerNum
        FROM
            Diagnosis dx,
            DiagnosisCode dc
        WHERE
            dx.DiagnosisSerNum          = '$diagnosisSer'
        AND dx.DiagnosisCode            = dc.DiagnosisCode
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        $diagnosisName = $data[0];

    }

    return $diagnosisName;

}

#======================================================================================
# Subroutine to get diagnosis names from our db given a patient serial
#======================================================================================
sub getPatientsDiagnosesFromOurDB
{
    my ($patientSer) = @_; # args

    my @diagnoses = (); # initialize list

    my $select_sql = "
        SELECT DISTINCT
            dc.DiagnosisTranslationSerNum
        FROM
            Diagnosis dx,
            DiagnosisCode dc
        WHERE
            dx.PatientSerNum            = '$patientSer'
        AND dx.DiagnosisCode            = dc.DiagnosisCode

    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        push(@diagnoses, $data[0]);

    }

    return @diagnoses;

}

#======================================================================================
# Subroutine to check if our diagnosis exists in our MySQL db
#	@return: diagnosis object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
	my ($diagnosis) = @_; # our diagnosis object
	my $sourceuid   = $diagnosis->getDiagnosisSourceUID(); # retrieve the uid from our object
    my $sourcedbser = $diagnosis->getDiagnosisSourceDatabaseSer();

	my $DiagnosisSourceUIDInDB = 0; # false by default. Will be true if diagnosis exists
	my $ExistingDiagnosis = (); # data to be entered if diagnosis exists

	# Other diagnosis variables, if diagnosis exists
	my ($ser, $patientser, $datestamp, $description, $code, $stage, $stagecriteria);

	my $inDB_sql = "
		SELECT DISTINCT
			Diagnosis.DiagnosisSerNum,
			Diagnosis.DiagnosisAriaSer,
			Diagnosis.CreationDate,
			Diagnosis.Description_EN,
			Diagnosis.PatientSerNum,
            Diagnosis.DiagnosisCode,
            Diagnosis.Stage,
            Diagnosis.StageCriteria
		FROM
			Diagnosis
		WHERE
			Diagnosis.DiagnosisAriaSer	    = '$sourceuid'
        AND Diagnosis.SourceDatabaseSerNum  = '$sourcedbser'
	";

	# prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

		$ser			        = $data[0];
		$DiagnosisSourceUIDInDB = $data[1];
		$datestamp		        = $data[2];
		$description		    = $data[3];
		$patientser		        = $data[4];
        $code                   = $data[5];
        $stage 					= $data[6];
        $stagecriteria 			= $data[7];

	}

	if ($DiagnosisSourceUIDInDB) {

		$ExistingDiagnosis = new Diagnosis(); # initialize diagnosis object

		$ExistingDiagnosis->setDiagnosisSourceUID($DiagnosisSourceUIDInDB); # set the id from our retrieved id
		$ExistingDiagnosis->setDiagnosisSourceDatabaseSer($sourcedbser);
		$ExistingDiagnosis->setDiagnosisSer($ser);
		$ExistingDiagnosis->setDiagnosisDateStamp($datestamp);
		$ExistingDiagnosis->setDiagnosisDescription($description);
		$ExistingDiagnosis->setDiagnosisPatientSer($patientser);
		$ExistingDiagnosis->setDiagnosisCode($code);
		$ExistingDiagnosis->setDiagnosisStage($stage);
		$ExistingDiagnosis->setDiagnosisStageCriteria($stagecriteria);


		return $ExistingDiagnosis; # this is true (ie. diagnosis exists, return object)
	}

	else {return $ExistingDiagnosis;} # this is false (ie. diagnosis does not exist, return 0)
}

#======================================================================================
# Subroutine to insert our diagnosis info in our database
#======================================================================================
sub insertDiagnosisIntoOurDB
{
	my ($diagnosis) = @_; # our diagnosis object serial

	my $patientser		= $diagnosis->getDiagnosisPatientSer();
	my $sourceuid	    = $diagnosis->getDiagnosisSourceUID();
    my $sourcedbser     = $diagnosis->getDiagnosisSourceDatabaseSer();
	my $datestamp		= $diagnosis->getDiagnosisDateStamp();
	my $description		= $diagnosis->getDiagnosisDescription();
	my $code	    	= $diagnosis->getDiagnosisCode();
	my $stage 			= $diagnosis->getDiagnosisStage();
	my $stagecriteria 	= $diagnosis->getDiagnosisStageCriteria();

	# Insert diagnosis
	my $insert_sql = "
		INSERT INTO
			Diagnosis (
				DiagnosisSerNum,
				PatientSerNum,
                SourceDatabaseSerNum,
				DiagnosisAriaSer,
				CreationDate,
                DiagnosisCode,
				Description_EN,
				Stage,
				StageCriteria,
				LastUpdated
			)
		VALUES (
			NULL,
			'$patientser',
            '$sourcedbser',
			'$sourceuid',
			'$datestamp',
            '$code',
			\"$description\",
			\"$stage\",
			\"$stagecriteria\",
			NULL
		)
	";

	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the DiagnosisSer
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the Serial in our diagnosis object
	$diagnosis->setDiagnosisSer($ser);

	return $diagnosis;

}
#======================================================================================
# Subroutine to update our database with the diagnosis' updated info
#======================================================================================
sub updateDatabase
{
	my ($diagnosis) = @_; # our diagnosis object to update

	my $sourceuid		= $diagnosis->getDiagnosisSourceUID();
    my $sourcedbser     = $diagnosis->getDiagnosisSourceDatabaseSer();
	my $datestamp		= $diagnosis->getDiagnosisDateStamp();
	my $description		= $diagnosis->getDiagnosisDescription();
    my $code            = $diagnosis->getDiagnosisCode();
    my $stage 			= $diagnosis->getDiagnosisStage();
	my $stagecriteria 	= $diagnosis->getDiagnosisStageCriteria();

	my $update_sql = "

		UPDATE
			Diagnosis
		SET
			CreationDate		= '$datestamp',
			Description_EN		= \"$description\",
            DiagnosisCode       = '$code',
            Stage 				= \"$stage\",
            StageCriteria 		= \"$stagecriteria\"
		WHERE
			DiagnosisAriaSer	    = '$sourceuid'
        AND SourceDatabaseSerNum    = '$sourcedbser'
	";

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

}


#======================================================================================
# Subroutine to compare two diagnosis objects. If different, use setter functions
# to update diagnosis object.
#======================================================================================
sub compareWith
{
	my ($SuspectDiagnosis, $OriginalDiagnosis) = @_; # our two diagnosis objects from arguments
	my $UpdatedDiagnosis = dclone($OriginalDiagnosis);

	# retrieve parameters
	# Suspect Diagnosis...
	my $Sdatestamp		= $SuspectDiagnosis->getDiagnosisDateStamp();
	my $Sdescription	= $SuspectDiagnosis->getDiagnosisDescription();
	my $Scode       	= $SuspectDiagnosis->getDiagnosisCode();
	my $Sstage 			= $SuspectDiagnosis->getDiagnosisStage();
	my $Sstagecriteria 	= $SuspectDiagnosis->getDiagnosisStageCriteria();

	# Original Diagnosis...
	my $Odatestamp		= $OriginalDiagnosis->getDiagnosisDateStamp();
	my $Odescription	= $OriginalDiagnosis->getDiagnosisDescription();
	my $Ocode       	= $OriginalDiagnosis->getDiagnosisCode();
	my $Ostage 			= $OriginalDiagnosis->getDiagnosisStage();
	my $Ostagecriteria 	= $OriginalDiagnosis->getDiagnosisStageCriteria();

	# go through each parameter
	if ($Sdatestamp ne $Odatestamp) {

		print "Diagnosis DateStamp has changed from '$Odatestamp' to '$Sdatestamp'\n";
		my $updatedDateStamp = $UpdatedDiagnosis->setDiagnosisDateStamp($Sdatestamp); # update diagnosis datestamp
		print "Will update database entry to '$updatedDateStamp'.\n";
	}
	if ($Sdescription ne $Odescription) {

		print "Diagnosis Description has changed from '$Odescription' to '$Sdescription'\n";
		my $updatedDescription = $UpdatedDiagnosis->setDiagnosisDescription($Sdescription); # update diagnosis desc
		print "Will update database entry to '$updatedDescription'.\n";
	}
	if ($Scode ne $Ocode) {

		print "Diagnosis Code has changed from '$Ocode' to '$Scode'\n";
		my $updatedCode = $UpdatedDiagnosis->setDiagnosisCode($Scode); # update diagnosis code
		print "Will update database entry to '$updatedCode'.\n";
	}
	if ($Sstage ne $Ostage) {
		print "Diagnosis Stage has changed from '$Ostage' to '$Sstage'\n";
		my $updatedStage = $UpdatedDiagnosis->setDiagnosisStage($Sstage); # update diagnosis stage
		print "Will update database entry to '$updatedStage'.\n";
	}
	if ($Sstagecriteria ne $Ostagecriteria) {
		print "Diagnosis Stage Criteria has changed from '$Ostagecriteria' to '$Sstagecriteria'\n";
		my $updatedStageCriteria = $UpdatedDiagnosis->setDiagnosisStageCriteria($Sstagecriteria); # update diagnosis stage criteria
		print "Will update database entry to '$updatedStageCriteria'.\n";
	}

	return $UpdatedDiagnosis;
}

# To exit/return always true (for the module itself)
1;
