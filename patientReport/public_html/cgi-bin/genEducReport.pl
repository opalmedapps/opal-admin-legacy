#!/usr/bin/perl -w 

# genEducReport: Given the educ material type and name, return the list
#					of educational materials sent to all patients through Opal
 
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

#------------------------------------------------------------------------
# Read in the command line arguments
#------------------------------------------------------------------------
my $type = param("type");
my $name = param("name");
my $sdb = param("db");

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
my $sql1="
	SELECT
		p.FirstName,
		p.LastName,
		p.PatientSerNum,
		p.Sex,
		p.Age,
		p.DateOfBirth,
		em.DateAdded,
		em.ReadStatus,
		em.LastUpdated
	FROM
		Patient AS p,
		EducationalMaterial AS em,
		EducationalMaterialControl AS emc
	WHERE
		em.PatientSerNum = p.PatientSerNum
		AND p.LastName NOT IN $nameList
		AND em.EducationalMaterialControlSerNum = emc.EducationalMaterialControlSerNum
		AND emc.EducationalMaterialType_EN = '$type'
		AND emc.Name_EN = '$name'";

$query1 = $dbh->prepare($sql1) or die "Couldn't prepare statement: " . $dbh->errstr;
$query1->execute() or die "Couldn't execute statement: " . $query1->errstr;
$data1 = $query1->fetchall_arrayref(); #diagnosis data saved here

package rec;
sub newEducReport{
	my $class = shift,
	my $row = {
		fname => shift,
		lname => shift,
		pser => shift,
		psex => shift,
		page => shift,
		pdob => shift,
		datesent => shift,
		readflag => shift,
		lastupdate => shift,
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
my $educ_json_data;

foreach my $r (@$data1){
	$nextRow = encode_json(\@$r);
	$newRec = newEducReport rec(split(',',substr($nextRow,1,-1)));
	my $key = (keys %$newRec)[0];
	$educ_json_data->{$key} ||= [];
	push @{$educ_json_data->{$key}}, $newRec->{$key};
}

my $JSON = $json_str->encode($educ_json_data); #Encode the diagnosis JSON (And any of the other JSONs that have been appended to diagnosis)
print "$JSON\n"; #Return
$query1->finish;
$dbh->disconnect;
exit;

