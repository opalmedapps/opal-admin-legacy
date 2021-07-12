#!/usr/bin/perl
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
# Subroutine to get test results from the ARIA db for automatic cron
#======================================================================================
sub getTestResultsFromSourceDB
{
	my $cronLogSer = @_[0];
	my @patientList = @_[1];
    my $global_patientInfo_sql = @_[2];
    my @TRList = (); # a list for test result objects

    # when we retrieve query results
    my ($pt_id, $visit_id, $test_id, $tr_group_id, $tr_id, $sourceuid, $name, $facname, $testdate);
    my ($maxnorm, $minnorm, $apprvflag, $abnormalflag, $testvalue, $testvaluestring);
    my ($unitdesc, $validentry);
    my $lastpublished;

    # retrieve all test results that are marked for update
    my @testResultList = TestResultControl::getTestResultControlsMarkedForUpdate();

	######################################
    # ARIA
    ######################################
    my $sourceDBSer = 1;
	{
        my $sourceDatabase	= Database::connectToSourceDatabase($sourceDBSer);

        if ($sourceDatabase) {

			my $expressionHash = {};
			my $expressionDict = {};
			foreach my $TestResult (@testResultList) {
				my $testResultSourceDBSer 	= $TestResult->getTestResultControlSourceDatabaseSer();
				my @expressions         = $TestResult->getTestResultControlExpressions();

				if ($sourceDBSer eq $testResultSourceDBSer) {
					if (!exists $expressionHash{$sourceDBSer}) {
						$expressionHash{$sourceDBSer} = {}; # initialize key value
					}

					foreach my $Expression (@expressions) {

						my $expressionSer = $Expression->{_ser};
						my $expressionName = $Expression->{_name};
						my $expressionLastPublished = $Expression->{_lastpublished};

						# append expression (surrounded by single quotes) to string
						if (exists $expressionHash{$sourceDBSer}{$expressionLastPublished}) {
							$expressionHash{$sourceDBSer}{$expressionLastPublished} .= ",'$expressionName'";
						} else {
							# start a new string
							$expressionHash{$sourceDBSer}{$expressionLastPublished} = "'$expressionName'";
						}

						$expressionDict{$expressionName} = $expressionSer;

					}
				}
			}

			my $patientInfo_sql = "
				use VARIAN;

                IF OBJECT_ID('tempdb.dbo.#tempTR', 'U') IS NOT NULL
                	DROP TABLE #tempTR;

				IF OBJECT_ID('tempdb.dbo.#tempPatient', 'U') IS NOT NULL
					DROP TABLE #tempPatient;

				WITH PatientInfo (ID, LastTransfer, PatientSerNum) AS (
			";
			$patientInfo_sql .= $global_patientInfo_sql; #use pre-loaded patientInfo from dataControl
			$patientInfo_sql .= ")
			Select c.* into #tempTR
			from PatientInfo c;
			Create Index temporaryindexTR1 on #tempTR (ID);
			Create Index temporaryindexTR2 on #tempTR (PatientSerNum);

			Select p.PatientSer, p.PatientId into #tempPatient
			from VARIAN.dbo.Patient p;
			Create Index temporaryindexPatient1 on #tempPatient (PatientId);
			Create Index temporaryindexPatient2 on #tempPatient (PatientSer);
			";

			my $trInfo_sql = $patientInfo_sql . "
				SELECT DISTINCT
					tr.pt_id,
					tr.pt_visit_id,
					tr.test_id,
					tr.test_result_group_id,
					tr.test_result_id,
					RTRIM(tr.abnormal_flag_cd),
					RTRIM(tr.comp_name),
					RTRIM(tr.fac_comp_name),
					CONVERT(VARCHAR, tr.date_test_pt_test, 120),
					tr.max_norm,
					tr.min_norm,
					tr.result_appr_ind,
					case
						when RTRIM(tr.comp_name) = 'SARS-2 Coronavirus-2019, NAA' then
							case
								when LEFT(LTRIM(tr.test_value_string), 11) = 'Non détecté' then 0
								when LEFT(LTRIM(tr.test_value_string), 11) = 'Non-détecté' then 0
								when LEFT(LTRIM(tr.test_value_string), 7) = 'Détecté' then 1
							end
						else
							tr.test_value
					end as test_value,
					tr.test_value_string,
					RTRIM(tr.unit_desc),
					tr.valid_entry_ind,
					PatientInfo.PatientSerNum
				FROM
					VARIAN.dbo.test_result tr with(nolock),
					VARIAN.dbo.pt pt with(nolock),
					#tempTR as PatientInfo
				WHERE
					tr.pt_id                		= pt.pt_id
				AND pt.patient_ser          		= (select pt.PatientSer
					from #tempPatient pt where pt.PatientId = PatientInfo.ID)
				AND tr.valid_entry_ind 				= 'Y'
				AND (
			";

			my $numOfExpressions = keys %{$expressionHash{$sourceDBSer}};
			my $counter = 0;
			# loop through each transfer date
			foreach my $lastTransferDate (keys %{$expressionHash{$sourceDBSer}}) {

				# concatenate query

				# 2020-02-05 YM: removed the filter so that we get all of the lab results as per John's request
				# $trInfo_sql .= "
				# (tr.comp_name IN ($expressionHash{$sourceDBSer}{$lastTransferDate})
					# AND tr.trans_log_mtstamp > (SELECT CASE WHEN '$lastTransferDate' > PatientInfo.LastTransfer THEN PatientInfo.LastTransfer ELSE '$lastTransferDate' END) )
				# ";
				$trInfo_sql .= "
				( tr.trans_log_mtstamp > (SELECT CASE WHEN '$lastTransferDate' > PatientInfo.LastTransfer THEN PatientInfo.LastTransfer ELSE '$lastTransferDate' END) )
				";

				$counter++;
				# concat "UNION" until we've reached the last query
				if ($counter < $numOfExpressions) {
					$trInfo_sql .= "OR";
				}
				# close bracket at end
				else {
					$trInfo_sql .= ")";
				}
			}

			# print "query: $trInfo_sql\n";
			# prepare query

			my $query = $sourceDatabase->prepare($trInfo_sql)
				or die "Could not prepare query: " . $sourceDatabase->errstr;

			# execute query
			$query->execute()
				or die "Could not execute query: " . $query->errstr;

			$data = $query->fetchall_arrayref();
			foreach my $row (@$data) {

				my $testresult = new TestResult();

				$pt_id              = $row->[0];
				$visit_id           = $row->[1];
				$test_id            = $row->[2];
				$tr_group_id        = $row->[3];
				$tr_id              = $row->[4];
				# combine the above id to create a unique id
				$sourceuid          = $pt_id.$visit_id.$test_id.$tr_group_id.$tr_id;

				$abnormalflag       = $row->[5];
				$expressionname     = $row->[6];
				$facname            = $row->[7];
				$testdate           = $row->[8];
				$maxnorm            = $row->[9];
				$minnorm            = $row->[10];
				$apprvflag          = $row->[11];
				$testvalue          = $row->[12];
				$testvaluestring    = $row->[13];
				$unitdesc           = $row->[14];
				$validentry         = $row->[15];
				$patientSer 		= $row->[16];

				$testresult->setTestResultPatientSer($patientSer);
				$testresult->setTestResultSourceDatabaseSer($sourceDBSer);
				$testresult->setTestResultSourceUID($sourceuid);
				$testresult->setTestResultExpressionSer($expressionDict{$expressionname} // 0);
				$testresult->setTestResultName($expressionname);
				$testresult->setTestResultFacName($facname);
				$testresult->setTestResultAbnormalFlag($abnormalflag);
				$testresult->setTestResultTestDate($testdate);
				$testresult->setTestResultMaxNorm($maxnorm);
				$testresult->setTestResultMinNorm($minnorm);
				$testresult->setTestResultApprovedFlag($apprvflag);
				$testresult->setTestResultTestValue($testvalue);
				$testresult->setTestResultTestValueString($testvaluestring);
				$testresult->setTestResultUnitDesc($unitdesc);
				$testresult->setTestResultValidEntry($validentry);
				$testresult->setTestResultCronLogSer($cronLogSer);

				push(@TRList, $testresult);
			}

			$sourceDatabase->disconnect();
		}

	}

	######################################
    # MediVisit
    ######################################
    my $sourceDBSer = 2;
	{
        my $sourceDatabase	= Database::connectToSourceDatabase($sourceDBSer);

        if ($sourceDatabase) {

			my $trInfo_sql = "SELECT 'QUERY_HERE'";

			# # prepare query
			# my $query = $sourceDatabase->prepare($trInfo_sql)
			# 	or die "Could not prepare query: " . $sourceDatabase->errstr;

			# # execute query
			# $query->execute()
			# 	or die "Could not execute query: " . $query->errstr;

			# $data = $query->fetchall_arrayref();
			# foreach my $row (@$data) {

			# 	#my $testresult = new TestResult(); # uncomment for use

			# 	# use setters to set appropriate test result information from query

			# 	#push(@TRList, $testresult); # uncomment for use
			# }

			$sourceDatabase->disconnect();

		}
	}

	 ######################################
    # MOSAIQ
    ######################################
    my $sourceDBSer = 3;
	{
        my $sourceDatabase	= Database::connectToSourceDatabase($sourceDBSer);

        if ($sourceDatabase) {

			# my $trInfo_sql = "SELECT 'QUERY_HERE'";

			# # prepare query
			# my $query = $sourceDatabase->prepare($trInfo_sql)
			# 	or die "Could not prepare query: " . $sourceDatabase->errstr;

			# # execute query
			# $query->execute()
			# 	or die "Could not execute query: " . $query->errstr;

			# $data = $query->fetchall_arrayref();
			# foreach my $row (@$data) {

			#     #my $testresult = new TestResult(); # uncomment for use

			#     # use setters to set appropriate test result information from query

			#     #push(@TRList, $testresult); # uncomment for use
			# }

			$sourceDatabase->disconnect();

		}

    }

    return @TRList;

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

    else {return $ExistingTR;} # this is false (ie. TR DNE)
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
