#!/usr/bin/perl -w 
use strict;
use warnings;
#se diagnostics;

use DBI;
use Data::Dumper;
use CGI qw(:standard);
use JSON;
use Exclude;
use Switch;
#------------------------------------------------------------------------
# Internal Variables
#------------------------------------------------------------------------
my $verbose = 0;
my $sdb = param("db");
# my $namestring = "(";
# foreach my $r (@nameList) {
# 	$namestring = join '', $namestring, '\'', $r, '\',';
# }
# $namestring = join '', $namestring, '\'\'', ')';
# print "$namestring \n\n";
print "$nameList\n\n";

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
		FirstName,
		LastName,
		PatientId
	FROM Patient
	WHERE Patient.LastName NOT IN $nameList
";

$query1 = $dbh->prepare($sql1) or die "Couldn't prepare statement: " . $dbh->errstr;
$query1->execute() or die "Couldn't execute statement: " . $query1->errstr;
$data1 = $query1->fetchall_arrayref(); #diagnosis data saved here

print Dumper($data1);

