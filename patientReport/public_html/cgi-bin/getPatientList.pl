#!/usr/bin/perl -w 

# getPatientList: return the full list of patients within Opal Dev for demographics reporting
 
#------------------------------------------------------------------------
# K.Agnew Feb 2019
#------------------------------------------------------------------------

#------------------------------------------------------------------------
# Declarations/initialisations
#------------------------------------------------------------------------
use strict;
#use warnings;
#se diagnostics;

#------------------------------------------------------------------------
# Use the DBI module 
#------------------------------------------------------------------------
use DBI;
use Data::Dumper;
use CGI qw(:standard);
use JSON;
use Exclude;

#------------------------------------------------------------------------
# Modules needed for SOAP webservices
#------------------------------------------------------------------------
use Switch;
#------------------------------------------------------------------------
# Internal Variables
#------------------------------------------------------------------------
my $verbose = 0;

my $sdb = param("db");

#------------------------------------------------------------------------
# Read in the command line arguments
#------------------------------------------------------------------------
#begin feedback
print "Content-type: application/json\n\n";

my $db;
my $un;
my $ps;
my $dbh;

if($sdb eq "true"){ #Then we want to connect to prod
	#login info for the prod server
	$db = "OpalDB";
	$un = 'reports';
	$ps = 'r3p0rt$246!'; #DONT DELETE THIS!! Not written down anywhere
	$dbh = DBI->connect("DBI:mysql:database=$db;host=172.26.120.179", $un, $ps)
		or die "Couldn't connect to database: " . DBI->errstr;
}else{ #we want preprod
	#login info for the preprod server
	$db = "OpalDB";
	$un = 'opalAdmin';
	$ps = 'nChs2Gfs1FeubVK0';
	$dbh = DBI->connect("DBI:mysql:database=$db;host=172.26.120.187", $un, $ps)
		or die "Couldn't connect to database: " . DBI->errstr;
}

#build SQL reports...
my $query1;
my $data1;

# my $sql1="
	# SELECT
		# p.PatientSerNum,
		# p.FirstName,
		# p.LastName,
		# p.Sex,
		# p.DateOfBirth,
		# p.Age,
		# p.Language,
		# p.RegistrationDate,
		# p.ConsentFormExpirationDate,
		# d.Description_EN,
		# d.CreationDate
	# FROM
		# Patient AS p,
		# Diagnosis AS d
	# WHERE
		# p.PatientSerNum = d.PatientSerNum
		# AND p.LastName NOT IN $nameList";

my $sql1="
	SELECT
		p.PatientSerNum,
		p.FirstName,
		p.LastName,
		p.Sex,
		p.DateOfBirth,
		p.Age,
		p.Email,
		p.Language,
		p.RegistrationDate,
		p.ConsentFormExpirationDate,
		ifnull((select d1.Description_EN from Diagnosis d1 where p.PatientSerNum = d1.PatientSerNum order by CreationDate desc limit 1), 'NA') as Description_EN,
		ifnull((select d2.CreationDate from Diagnosis d2 where p.PatientSerNum = d2.PatientSerNum order by CreationDate desc limit 1), now()) as CreationDate
	FROM
		Patient AS p
	WHERE
		p.LastName NOT IN $nameList
	ORDER BY p.RegistrationDate";

$query1 = $dbh->prepare($sql1) or die "Couldn't prepare statement: " . $dbh->errstr;
$query1->execute() or die "Couldn't execute statement: " . $query1->errstr;
$data1 = $query1->fetchall_arrayref(); #diagnosis data saved here

package rec;
sub newReport{
	my $class = shift,
	my $row = {
		pser => shift,
		fname => shift,
		lname => shift,
		psex => shift,
		pdob => shift,
		age => shift,
		email => shift,
		lang => shift,
		regdate => shift,
		consentexp => shift,
		diagdesc => shift,
		diagdate => shift,
	};
	my $rowHead ={
		report => $row
	};
	bless $rowHead, $class;
	return $rowHead;
}


package main;
my $json_str = JSON->new->allow_nonref;
$json_str->convert_blessed(1);
my $nextRow;
my $newRec;
my $json_data;

foreach my $r (@$data1){
	$nextRow = encode_json(\@$r);
	$newRec = newReport rec(split(',',substr($nextRow,1,-1)));
	my $key = (keys %$newRec)[0];
	$json_data->{$key} ||= [];
	push @{$json_data->{$key}}, $newRec->{$key};
}

my $JSON = $json_str->encode($json_data); #Encode the diagnosis JSON (And any of the other JSONs that have been appended to diagnosis)
print "$JSON\n"; #Return
$query1->finish;
$dbh->disconnect;
exit;

