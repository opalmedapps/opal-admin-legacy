#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 04-Jan-2018 ++ File: TestResult.pm
#---------------------------------------------------------------------------------
# Perl module that creates a test result control class. This module calls a constructor to 
# create a test result control object that contains test result control information stored as object 
# variables.
#
# There exists various subroutines to set / get test result control information.

package TestResultControl; 

use Database; # Our custom module Database.pm

#---------------------------------------------------------------------------------
# Connect to the database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Test Result Control class 
#====================================================================================
sub new
{
    my $class = shift;
    my $testresultcontrol = {
    	_ser 			=> undef,
    	_sourcedbser 	=> undef,
    	_publishflag	=> undef,
    	_lastpublished 	=> undef,
    	_expressions 	=> undef,
    };

    # bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $testresultcontrol, $class; 
	return $testresultcontrol;
}

#======================================================================================
# Subroutine to set the test result control serial
#======================================================================================
sub setTestResultControlSer
{
	my ($testresultcontrol, $ser) = @_; # test result control object with provided serial in arguments
	$testresultcontrol->{_ser} = $ser; # set the serial
	return $testresultcontrol->{_ser};
}

#======================================================================================
# Subroutine to set the test result source database serial
#======================================================================================
sub setTestResultControlSourceDatabaseSer
{
	my ($testresultcontrol, $sourcedbser) = @_; # test result control object with provided serial in arguments
	$testresultcontrol->{_sourcedbser} = $sourcedbser; # set the serial
	return $testresultcontrol->{_sourcedbser};
}

#======================================================================================
# Subroutine to set the test result control publish flag
#======================================================================================
sub setTestResultControlPublishFlag
{
	my ($testresultcontrol, $publishflag) = @_; # test result control object with provided publish flag in arguments
	$testresultcontrol->{_publishflag} = $publishflag; # set the flag
	return $testresultcontrol->{_publishflag};
}

#======================================================================================
# Subroutine to set the test result control last published 
#======================================================================================
sub setTestResultControlLastPublished
{
	my ($testresultcontrol, $lastpublished) = @_; # test result control object with provided date in arguments
	$testresultcontrol->{_lastpublished} = $lastpublished; # set the LP
	return $testresultcontrol->{_lastpublished};
}

#======================================================================================
# Subroutine to set the test result control expressions
#======================================================================================
sub setTestResultControlExpressions
{
	my ($testresultcontrol, @expressions) = @_; # test result control object with provided serial in arguments
	@{$testresultcontrol->{_expressions}} = @expressions; # set the expression array
	return @{$testresultcontrol->{_expressions}};
}

#======================================================================================
# Subroutine to get the test result control serial
#======================================================================================
sub getTestResultControlSer
{
	my ($testresultcontrol) = @_; # our test result control object
	return $testresultcontrol->{_ser};
}

#======================================================================================
# Subroutine to get the test result control source database serial
#======================================================================================
sub getTestResultControlSourceDatabaseSer
{
	my ($testresultcontrol) = @_; # our test result control object
	return $testresultcontrol->{_sourcedbser};
}

#======================================================================================
# Subroutine to get the test result control publish flag
#======================================================================================
sub getTestResultControlPublishFlag
{
	my ($testresultcontrol) = @_; # our test result control object
	return $testresultcontrol->{publishflag};
}

#======================================================================================
# Subroutine to get the test result control last published
#======================================================================================
sub getTestResultControlLastPublished
{
	my ($testresultcontrol) = @_; # our test result control object
	return $testresultcontrol->{_lastpublished};
}

#======================================================================================
# Subroutine to get the test result control expressions
#======================================================================================
sub getTestResultControlExpressions
{
	my ($testresultcontrol) = @_; # our test result control object
	return @{$testresultcontrol->{_expressions}};
}

#======================================================================================
# Subroutine to get the test results marked for update
#======================================================================================
sub getTestResultControlsMarkedForUpdate
{
	my @testResultControlList = (); # initialize a list 
	my ($ser, $sourcedbser, $lastpublished);
	my @expressions;

	my $info_sql = "
		SELECT DISTINCT
			trc.TestResultControlSerNum,
			trc.LastPublished,
			trc.SourceDatabaseSerNum
		FROM
			TestResultControl trc,
			SourceDatabase sd
		WHERE
			trc.PublishFlag 			= 1
		AND trc.SourceDatabaseSerNum 	= sd.SourceDatabaseSerNum
		AND sd.Enabled 					= 1
	";

	# prepare query
	my $query = $SQLDatabase->prepare($info_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

		my $TestResultControl = new TestResultControl(); # object

		$ser 			= $data[0];
		$lastpublished 	= $data[1];
		$sourcedbser 	= $data[2];

		# set test result control information
		$TestResultControl->setTestResultControlSer($ser);
		$TestResultControl->setTestResultControlLastPublished($lastpublished);
		$TestResultControl->setTestResultControlSourceDatabaseSer($sourcedbser);

		# get expressions for this test result
		@expressions	= $TestResultControl->getTestResultControlExpressionsFromOurDB();

		# finally, set expressions 
		$TestResultControl->setTestResultControlExpressions(@expressions);

		push(@testResultControlList, $TestResultControl);
	}

	return @testResultControlList;
}

#======================================================================================
# Subroutine to get the expressions for a particular test result control from MySQL
#======================================================================================
sub getTestResultControlExpressionsFromOurDB
{
	my ($TestResultControl) = @_; # our test result control object

	my @expressions = (); # initialize a list of expressions

	# get test result control serial 
	my $ser = $TestResultControl->getTestResultControlSer();

	#======================================================================================
	# Retrieve the test result expressions
	#======================================================================================
	my $expressionInfo_sql = "
		SELECT DISTINCT
			tre.TestResultExpressionSerNum,
			RTRIM(tre.ExpressionName),
			tre.LastPublished
		FROM
			TestResultControl trc,
			TestResultExpression tre 
		WHERE
			trc.TestResultControlSerNum 	= $ser 
		AND trc.TestResultControlSerNum 	= tre.TestResultControlSerNum
	";

	# prepare query
	my $query = $SQLDatabase->prepare($expressionInfo_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {
		my $testResultExpression = {
			_ser			=> $data[0],
			_name			=> $data[1],
			_lastpublished 	=> $data[2]
		};
		push(@expressions, $testResultExpression); # push in our list
	}

	return @expressions;
}

#======================================================================================
# Subroutine to set/update the "last published" field to current time 
#======================================================================================
sub setTestResultLastPublishedIntoOurDB
{	
	my ($current_datetime) = @_; # our current datetime in arguments

	my $update_sql = "
		
		UPDATE
			TestResultControl,
			TestResultExpression
		SET
			TestResultControl.LastPublished				= '$current_datetime',
            TestResultControl.LastUpdated     			= TestResultControl.LastUpdated,
            TestResultExpression.LastPublished			= '$current_datetime',
            TestResultExpression.LastUpdated 			= TestResultExpression.LastUpdated
		WHERE
			TestResultControl.PublishFlag				= 1
		AND TestResultControl.TestResultControlSerNum 	= TestResultExpression.TestResultControlSerNum
		";

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
}

# exit smoothly for module
1;
