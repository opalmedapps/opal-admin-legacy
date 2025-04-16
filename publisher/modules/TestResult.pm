# SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

#---------------------------------------------------------------------------------
# Y.Mo 03-Apr-2020
# This module is no longer required because the test results will be fetch from Oacis.
#
# Will be removed after a few next release.
#
#---------------------------------------------------------------------------------
# A.Joseph 14-Oct-2015 ++ File: TestResult.pm
#---------------------------------------------------------------------------------
# Perl module that creates a TestResult class. This module calls a constructor to
# create a TestResult object that contains TestResult information stored as object
# variables.
#
# There exists various subroutines to set TestResult information, get TestResult information
# and compare TestResult information between two TestResult objects.
# There exists various subroutines that use the Database.pm module to update the
# MySQL database and check if a TestResult exists already in this database.

package TestResult; # Declare package name

use Exporter; # To export subroutines and variables
use Database; # Use our custom database module Database.pm
use Time::Piece; # To parse and convert date time
use POSIX;
use Storable qw(dclone);
use Data::Dumper;

use Patient; # Our patient module
use TestResultControl; # Our Test Result Control module

#---------------------------------------------------------------------------------
# Connect to our database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our class
#====================================================================================
sub new
{
	my $class = shift;
    my $testresult = {
        _ser                		=> undef,
        _sourceuid          		=> undef,
        _sourcedbser        		=> undef,
        _controlser					=> undef,
        _expressionser				=> undef,
        _patientser         		=> undef,
        _name               		=> undef,
        _facname            		=> undef,
        _abnormalflag       		=> undef,
        _testdate           		=> undef,
        _maxnorm            		=> undef,
        _minnorm            		=> undef,
        _approvedflag       		=> undef,
        _testvalue          		=> undef,
        _testvaluestring    		=> undef,
        _unitdesc           		=> undef,
        _validentry         		=> undef,
        _cronlogser 				=> undef,
    };
	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $testresult, $class;
	return $testresult;
}

#====================================================================================
# Subroutine to set the testresult serial
#====================================================================================
sub setTestResultSer
{
	my ($testresult, $ser) = @_; # object with provided serial in arguments
	$testresult->{_ser} = $ser; # set the ser
	return $testresult->{_ser};
}

#====================================================================================
# Subroutine to set the testresult source database serial
#====================================================================================
sub setTestResultSourceDatabaseSer
{
	my ($testresult, $sourcedbser) = @_; # object with provided serial in arguments
	$testresult->{_sourcedbser} = $sourcedbser; # set the ser
	return $testresult->{_sourcedbser};
}

#====================================================================================
# Subroutine to set the testresult uid
#====================================================================================
sub setTestResultSourceUID
{
	my ($testresult, $sourceuid) = @_; # object with provided id in arguments
	$testresult->{_sourceuid} = $sourceuid; # set the id
	return $testresult->{_sourceuid};
}

#====================================================================================
# Subroutine to set the testresult patient serial
#====================================================================================
sub setTestResultPatientSer
{
	my ($testresult, $patientser) = @_; # object with provided serial in arguments
	$testresult->{_patientser} = $patientser; # set the ser
	return $testresult->{_patientser};
}

#====================================================================================
# Subroutine to set the testresult control serial
#====================================================================================
sub setTestResultControlSer
{
	my ($testresult, $controlser) = @_; # object with provided serial in arguments
	$testresult->{_controlser} = $controlser; # set the ser
	return $testresult->{_controlser};
}

#====================================================================================
# Subroutine to set the testresult expression serial
#====================================================================================
sub setTestResultExpressionSer
{
	my ($testresult, $expressionser) = @_; # object with provided serial in arguments
	$testresult->{_expressionser} = $expressionser; # set the ser
	return $testresult->{_expressionser};
}

#====================================================================================
# Subroutine to set the testresult name
#====================================================================================
sub setTestResultName
{
	my ($testresult, $name) = @_; # object with provided name in arguments
	$testresult->{_name} = $name; # set the name
	return $testresult->{_name};
}

#====================================================================================
# Subroutine to set the testresult faculty name
#====================================================================================
sub setTestResultFacName
{
	my ($testresult, $facname) = @_; # object with provided name in arguments
	$testresult->{_facname} = $facname; # set the name
	return $testresult->{_facname};
}

#====================================================================================
# Subroutine to set the testresult abnormal flag
#====================================================================================
sub setTestResultAbnormalFlag
{
	my ($testresult, $abnormalflag) = @_; # object with provided flag in arguments
	$testresult->{_abnormalflag} = $abnormalflag; # set the flag
	return $testresult->{_abnormalflag};
}

#====================================================================================
# Subroutine to set the testresult date
#====================================================================================
sub setTestResultTestDate
{
	my ($testresult, $testdate) = @_; # object with provided date in arguments
	$testresult->{_testdate} = $testdate; # set the date
	return $testresult->{_testdate};
}

#====================================================================================
# Subroutine to set the testresult max norm
#====================================================================================
sub setTestResultMaxNorm
{
	my ($testresult, $maxnorm) = @_; # object with provided norm in arguments
	$testresult->{_maxnorm} = $maxnorm; # set the norm
	return $testresult->{_maxnorm};
}

#====================================================================================
# Subroutine to set the testresult min norm
#====================================================================================
sub setTestResultMinNorm
{
	my ($testresult, $minnorm) = @_; # object with provided norm in arguments
	$testresult->{_minnorm} = $minnorm; # set the norm
	return $testresult->{_minnorm};
}

#====================================================================================
# Subroutine to set the testresult approved flag
#====================================================================================
sub setTestResultApprovedFlag
{
	my ($testresult, $apprvflag) = @_; # object with provided flag in arguments
	$testresult->{_approvedflag} = $apprvflag; # set the flag
	return $testresult->{_approvedflag};
}

#====================================================================================
# Subroutine to set the testresult test value
#====================================================================================
sub setTestResultTestValue
{
	my ($testresult, $testvalue) = @_; # object with provided value in arguments
	$testresult->{_testvalue} = $testvalue; # set the value
	return $testresult->{_testvalue};
}

#====================================================================================
# Subroutine to set the testresult test value string
#====================================================================================
sub setTestResultTestValueString
{
	my ($testresult, $testvaluestring) = @_; # object with provided value in arguments
	$testresult->{_testvaluestring} = $testvaluestring; # set the value
	return $testresult->{_testvaluestring};
}

#====================================================================================
# Subroutine to set the testresult unit description
#====================================================================================
sub setTestResultUnitDesc
{
	my ($testresult, $unitdesc) = @_; # object with provided unit in arguments
	$testresult->{_unitdesc} = $unitdesc; # set the unit
	return $testresult->{_unitdesc};
}

#====================================================================================
# Subroutine to set the testresult valid entry
#====================================================================================
sub setTestResultValidEntry
{
	my ($testresult, $validentry) = @_; # object with provided value in arguments
	$testresult->{_validentry} = $validentry; # set the value
	return $testresult->{_validentry};
}

#====================================================================================
# Subroutine to set the testresult cron log serial
#====================================================================================
sub setTestResultCronLogSer
{
	my ($testresult, $cronlogser) = @_; # object with provided serial in arguments
	$testresult->{_cronlogser} = $cronlogser; # set the ser
	return $testresult->{_cronlogser};
}

#====================================================================================
# Subroutine to get the testresult ser
#====================================================================================
sub getTestResultSer
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_ser};
}

#====================================================================================
# Subroutine to get the testresult source database
#====================================================================================
sub getTestResultSourceDatabaseSer
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_sourcedbser};
}

#====================================================================================
# Subroutine to get the testresult source uid
#====================================================================================
sub getTestResultSourceUID
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_sourceuid};
}

#====================================================================================
# Subroutine to get the testresult patient ser
#====================================================================================
sub getTestResultPatientSer
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_patientser};
}

#====================================================================================
# Subroutine to get the testresult control ser
#====================================================================================
sub getTestResultControlSer
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_controlser};
}

#====================================================================================
# Subroutine to get the testresult expression ser
#====================================================================================
sub getTestResultExpressionSer
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_expressionser};
}

#====================================================================================
# Subroutine to get the testresult name
#====================================================================================
sub getTestResultName
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_name};
}

#====================================================================================
# Subroutine to get the testresult facility name
#====================================================================================
sub getTestResultFacName
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_facname};
}

#====================================================================================
# Subroutine to get the testresult abnormal flag
#====================================================================================
sub getTestResultAbnormalFlag
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_abnormalflag};
}

#====================================================================================
# Subroutine to get the testresult test date
#====================================================================================
sub getTestResultTestDate
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_testdate};
}

#====================================================================================
# Subroutine to get the testresult max norm
#====================================================================================
sub getTestResultMaxNorm
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_maxnorm};
}

#====================================================================================
# Subroutine to get the testresult min norm
#====================================================================================
sub getTestResultMinNorm
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_minnorm};
}

#====================================================================================
# Subroutine to get the testresult approved flag
#====================================================================================
sub getTestResultApprovedFlag
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_approvedflag};
}

#====================================================================================
# Subroutine to get the testresult test value
#====================================================================================
sub getTestResultTestValue
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_testvalue};
}

#====================================================================================
# Subroutine to get the testresult test value string
#====================================================================================
sub getTestResultTestValueString
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_testvaluestring};
}

#====================================================================================
# Subroutine to get the testresult unit description
#====================================================================================
sub getTestResultUnitDesc
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_unitdesc};
}

#====================================================================================
# Subroutine to get the testresult valid entry
#====================================================================================
sub getTestResultValidEntry
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_validentry};
}

#====================================================================================
# Subroutine to get the testresult cron log ser
#====================================================================================
sub getTestResultCronLogSer
{
	my ($testresult) = @_; # our testresult object
	return $testresult->{_cronlogser};
}

#======================================================================================
# Subroutine to check if a particular test result exists in our MySQL db
#	@return: test result object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
    my ($testresult) = @_; # our object

    my $sourceuid   = $testresult->getTestResultSourceUID();
    my $sourcedbser = $testresult->getTestResultSourceDatabaseSer();

    my $TRSourceUIDInDB = 0; # false by default. Will be true if test result exists
    my $ExistingTR = (); # data to be entered if test result exists

    # Other variables, if it exists
    my ($ser, $patientser, $expressionser, $name, $facname, $abnormalflag, $testdate, $maxnorm, $minnorm);
    my ($apprvflag, $testvalue, $testvaluestring, $unitdesc, $validentry, $cronlogser);

    my $inDB_sql = "
        SELECT
            tr.TestResultSerNum,
            tr.PatientSerNum,
            tr.TestResultAriaSer,
            tr.ComponentName,
            tr.FacComponentName,
            tr.AbnormalFlag,
            tr.TestDate,
            tr.MaxNorm,
            tr.MinNorm,
            tr.ApprovedFlag,
            tr.TestValue,
            tr.TestValueString,
            tr.UnitDescription,
            tr.ValidEntry,
            tr.TestResultExpressionSerNum,
            tr.CronLogSerNum
        FROM
            TestResult AS tr
        WHERE
            tr.TestResultAriaSer    = '$sourceuid'
        AND tr.SourceDatabaseSerNum = '$sourcedbser'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

    while (my @data = $query->fetchrow_array()) {

        $ser                = $data[0];
        $patientser         = $data[1];
        $TRSourceUIDInDB    = $data[2];
        $name               = $data[3];
        $facname            = $data[4];
        $abnormalflag       = $data[5];
        $testdate           = $data[6];
        $maxnorm            = $data[7];
        $minnorm            = $data[8];
        $apprvflag          = $data[9];
        $testvalue          = $data[10];
        $testvaluestring    = $data[11];
        $unitdesc           = $data[12];
        $validentry         = $data[13];
        $expressionser 		= $data[14];
        $cronlogser 		= $data[15];
    }

    if ($TRSourceUIDInDB) {

        $ExistingTR = new TestResult();

        $ExistingTR->setTestResultSer($ser);
        $ExistingTR->setTestResultPatientSer($patientser);
        $ExistingTR->setTestResultSourceUID($TRSourceUIDInDB);
        $ExistingTR->setTestResultSourceDatabaseSer($sourcedbser);
        $ExistingTR->setTestResultExpressionSer($expressionser);
        $ExistingTR->setTestResultName($name);
        $ExistingTR->setTestResultFacName($facname);
        $ExistingTR->setTestResultAbnormalFlag($abnormalflag);
        $ExistingTR->setTestResultTestDate($testdate);
        $ExistingTR->setTestResultMaxNorm($maxnorm);
        $ExistingTR->setTestResultMinNorm($minnorm);
        $ExistingTR->setTestResultApprovedFlag($apprvflag);
        $ExistingTR->setTestResultTestValue($testvalue);
        $ExistingTR->setTestResultTestValueString($testvaluestring);
        $ExistingTR->setTestResultUnitDesc($unitdesc);
        $ExistingTR->setTestResultValidEntry($validentry);
        $ExistingTR->setTestResultCronLogSer($cronlogser);

        return $ExistingTR; # this is true (ie. TR exists)
    }

    else {return $ExistingTR;} # this is false (ie. TR does not exist)
}

#======================================================================================
# Subroutine to insert our testresult info in our database
#======================================================================================
sub insertTestResultIntoOurDB
{
	my ($testresult) = @_; # our object

	my $patientser				= $testresult->getTestResultPatientSer();
	my $sourceuid				= $testresult->getTestResultSourceUID();
	my $sourcedbser             = $testresult->getTestResultSourceDatabaseSer();
	my $expressionser			= $testresult->getTestResultExpressionSer();
	my $name                    = $testresult->getTestResultName();
	my $facname                 = $testresult->getTestResultFacName();
	my $abnormalflag            = $testresult->getTestResultAbnormalFlag();
	my $testdate                = $testresult->getTestResultTestDate();
	my $maxnorm                 = $testresult->getTestResultMaxNorm();
	my $minnorm                 = $testresult->getTestResultMinNorm();
	my $apprvflag               = $testresult->getTestResultApprovedFlag();
	my $testvalue               = $testresult->getTestResultTestValue();
	my $testvaluestring         = $testresult->getTestResultTestValueString();
	$testvaluestring			=~ tr/'/`/;

	my $unitdesc                = $testresult->getTestResultUnitDesc();
	my $validentry              = $testresult->getTestResultValidEntry();
	my $cronlogser              = $testresult->getTestResultCronLogSer();

	my $insert_sql = "
		INSERT INTO TestResult (
			PatientSerNum,
			CronLogSerNum,
			SourceDatabaseSerNum,
			TestResultAriaSer,
			TestResultExpressionSerNum,
			ComponentName,
			FacComponentName,
			AbnormalFlag,
			TestDate,
			MaxNorm,
			MinNorm,
			ApprovedFlag,
			TestValue,
			TestValueString,
			UnitDescription,
			ValidEntry,
			DateAdded
		)
	VALUES (
		'$patientser',
		'$cronlogser',
		'$sourcedbser',
		'$sourceuid',
		'$expressionser',
		\"$name\",
		\"$facname\",
		'$abnormalflag',
		'$testdate',
		'$maxnorm',
		'$minnorm',
		'$apprvflag',
		'$testvalue',
		'$testvaluestring',
		'$unitdesc',
		'$validentry',
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

	# Set the serial object
	$testresult->setTestResultSer($ser);

	# Get the number of Lab Results based on the cron log serial number and patient serial number
	my $CheckCount = getLabResultCount($cronlogser, $patientser);
	# print "Cron Log Count : $CheckCount\n";

	# Send push notification if the counter is 1
	if ($CheckCount eq 1) {
		print "Send Push Notification to $patientser\n";
  	PushNotification::sendPushNotification($patientser, $ser, 'NewLabResult');
	}

	return $testresult;
}

#======================================================================================
# Subroutine to update our database with the test result's updated info
#======================================================================================
sub updateDatabase
{
	my ($testresult) = @_; # our object

	my $sourceuid               = $testresult->getTestResultSourceUID();
	my $sourcedbser             = $testresult->getTestResultSourceDatabaseSer();
	my $expressionser 			= $testresult->getTestResultExpressionSer();
	my $name                    = $testresult->getTestResultName();
	my $facname                 = $testresult->getTestResultFacName();
	my $abnormalflag            = $testresult->getTestResultAbnormalFlag();
	my $testdate                = $testresult->getTestResultTestDate();
	my $maxnorm                 = $testresult->getTestResultMaxNorm();
	my $minnorm                 = $testresult->getTestResultMinNorm();
	my $apprvflag               = $testresult->getTestResultApprovedFlag();
	my $testvalue               = $testresult->getTestResultTestValue();
	my $testvaluestring         = $testresult->getTestResultTestValueString();
	$testvaluestring			=~ tr/'/`/;

	my $unitdesc                = $testresult->getTestResultUnitDesc();
	my $validentry              = $testresult->getTestResultValidEntry();
	my $cronlogser              = $testresult->getTestResultCronLogSer();

	my $update_sql = "
		UPDATE
			TestResult
		SET
			TestResultExpressionSerNum	= '$expressionser',
			CronLogSerNum 				= '$cronlogser',
			ComponentName           	= \"$name\",
			FacComponentName        	= \"$facname\",
			AbnormalFlag            	= '$abnormalflag',
			TestDate                	= '$testdate',
			MaxNorm                 	= '$maxnorm',
			MinNorm                 	= '$minnorm',
			ApprovedFlag            	= '$apprvflag',
			TestValue               	= '$testvalue',
			TestValueString         	= '$testvaluestring',
			UnitDescription         	= '$unitdesc',
			ValidEntry              	= '$validentry',
			ReadStatus					= 0
		WHERE
			TestResultAriaSer       	= '$sourceuid'
			AND SourceDatabaseSerNum	= '$sourcedbser'
	";

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
}

#======================================================================================
# Subroutine to compare two test result objects. If different, use setter functions
# to update test result object.
#======================================================================================
sub compareWith
{
    my ($SuspectTR, $OriginalTR) = @_; # our two TR objects
    my $UpdatedTestResult = dclone($OriginalTR);
	my $change = 0; # boolean to recognize an actual difference between objects

    # retrieve params
    # Suspect TR
    my $Sexpressionser 		= $SuspectTR->getTestResultExpressionSer();
    my $Sname               = $SuspectTR->getTestResultName();
    my $Sfacname            = $SuspectTR->getTestResultFacName();
    my $Sabnormalflag       = $SuspectTR->getTestResultAbnormalFlag();
    my $Stestdate           = $SuspectTR->getTestResultTestDate();
    my $Smaxnorm            = $SuspectTR->getTestResultMaxNorm();
    my $Sminnorm            = $SuspectTR->getTestResultMinNorm();
    my $Sapprvflag          = $SuspectTR->getTestResultApprovedFlag();
    my $Stestvalue          = $SuspectTR->getTestResultTestValue();
    my $Stestvaluestring    = $SuspectTR->getTestResultTestValueString();
    my $Sunitdesc           = $SuspectTR->getTestResultUnitDesc();
    my $Svalidentry         = $SuspectTR->getTestResultValidEntry();
    my $Scronlogser         = $SuspectTR->getTestResultCronLogSer();

    # Original TR
    my $Oexpressionser 		= $OriginalTR->getTestResultExpressionSer();
    my $Oname               = $OriginalTR->getTestResultName();
    my $Ofacname            = $OriginalTR->getTestResultFacName();
    my $Oabnormalflag       = $OriginalTR->getTestResultAbnormalFlag();
    my $Otestdate           = $OriginalTR->getTestResultTestDate();
    my $Omaxnorm            = $OriginalTR->getTestResultMaxNorm();
    my $Ominnorm            = $OriginalTR->getTestResultMinNorm();
    my $Oapprvflag          = $OriginalTR->getTestResultApprovedFlag();
    my $Otestvalue          = $OriginalTR->getTestResultTestValue();
    my $Otestvaluestring    = $OriginalTR->getTestResultTestValueString();
    my $Ounitdesc           = $OriginalTR->getTestResultUnitDesc();
    my $Ovalidentry         = $OriginalTR->getTestResultValidEntry();
    my $Ocronlogser         = $OriginalTR->getTestResultCronLogSer();

	if(
		$Sexpressionser ne $Oexpressionser
		or $Sname ne $Oname
		or $Sfacname ne $Ofacname
		or $Sabnormalflag ne $Oabnormalflag
		or $Stestdate ne $Otestdate
		or $Smaxnorm != $Omaxnorm
		or $Sminnorm != $Ominnorm
		or $Sapprvflag ne $Oapprvflag
		or $Stestvalue != $Otestvalue
		or $Stestvaluestring ne $Otestvaluestring
		or $Sunitdesc ne $Ounitdesc
		or $Svalidentry ne $Ovalidentry
	) {
		$change = 1; # change occurred
		$UpdatedTestResult->setTestResultExpressionSer($Sexpressionser);
		$UpdatedTestResult->setTestResultName($Sname);
		$UpdatedTestResult->setTestResultFacName($Sfacname);
		$UpdatedTestResult->setTestResultAbnormalFlag($Sabnormalflag);
		$UpdatedTestResult->setTestResultTestDate($Stestdate);
		$UpdatedTestResult->setTestResultMaxNorm($Smaxnorm);
		$UpdatedTestResult->setTestResultMinNorm($Sminnorm);
		$UpdatedTestResult->setTestResultApprovedFlag($Sapprvflag);
		$UpdatedTestResult->setTestResultTestValue($Stestvalue);
		$UpdatedTestResult->setTestResultTestValueString($Stestvaluestring);
		$UpdatedTestResult->setTestResultUnitDesc($Sunitdesc);
		$UpdatedTestResult->setTestResultValidEntry($Svalidentry);
		$UpdatedTestResult->setTestResultCronLogSer($Scronlogser);
	}

    return ($UpdatedTestResult, $change);
}

#======================================================================================
# Subroutine to get the count of Lab Result for a specific patient
#======================================================================================
sub getLabResultCount
{
	my ($cronlogser, $patientser) = @_;  # our object of CronLogSerNum and PatientSer

	# Default counter to 0;
	my $returnCount = 0;

	# Prepare SQL statement
	my $qSQL = "
			SELECT count(*) as Total
			FROM TestResult
			WHERE CronLogSerNum = '$cronlogser'
			AND PatientSerNum = '$patientser'
	";

	# Prepare query
	my $query = $SQLDatabase->prepare($qSQL)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# Execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the results
	while (my @data = $query->fetchrow_array()) {
		$returnCount = $data[0];
	}

	# Return the results
	return $returnCount;
}


# To exit/return always true (for the module itself)
1;
