#!/usr/bin/perl -w

# genEducReport : given patient report by passing PatientSerNum
 
#------------------------------------------------------------------------
# K.Agnew Feb 2019
#------------------------------------------------------------------------

#------------------------------------------------------------------------
# Declarations/initialisations
#------------------------------------------------------------------------
use strict;
use warnings;
#use diagnostics;

#------------------------------------------------------------------------
# Use the DBI module 
#------------------------------------------------------------------------
use DBI;
use Data::Dumper;
use CGI qw(:standard);
use JSON;
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
my $pser = param("psnum");
my $diagnosis = param("diagnosis");
my $appointments = param("appointments");
my $questionnaires = param("questionnaires");
my $education = param("education");
my $testresults = param("testresults");
my $notes = param("notes");
my $treatplan = param("treatplan");
my $clinicalnotes = param("clinicalnotes");
my $treatingteam = param("treatingteam");
my $general = param("general");
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

#$dbh = DBI->connect("DBI:mysql:opaldb",'root','')or die "Couldn't connect to database: " . DBI->errstr;

#build SQL reports...
my $query1;
my $data1;
if($diagnosis eq "true"){ #Reporting branch for the diagnosis
	my $sql1="
		SELECT
			DiagnosisSerNum,
			CreationDate,
			Description_EN
		FROM
			Diagnosis
		WHERE
			PatientSerNum = '$pser'
	";
	$query1 = $dbh->prepare($sql1) or die "Couldn't prepare statement: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute statement: " . $query1->errstr;
	$data1 = $query1->fetchall_arrayref(); #diagnosis data saved here
}

my $data2;
if($appointments eq "true"){ #Reporting branch for the appointments
	my $sql2="
		SELECT
			a.ScheduledStartTime,
			a.Status,
			a.DateAdded,
			als.AliasName_EN,
			als.AliasType,
			r.ResourceName
		FROM
			Appointment AS a,
			AliasExpression AS ae,
			Alias AS als,
			Resource AS r,
			ResourceAppointment AS ra
		WHERE
			PatientSerNum = '$pser'
			AND a.AliasExpressionSerNum = ae.AliasExpressionSerNum
			AND ae.AliasSerNum = als.AliasSerNum
			AND r.ResourceSerNum = ra.ResourceSerNum
			AND ra.AppointmentSerNum = a.AppointmentSerNum
	";
	$query1 = $dbh->prepare($sql2) or die "Couldn't prepare statement: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute statement: " . $query1->errstr;
	$data2 = $query1->fetchall_arrayref(); #appointment data saved here
}
my $data3;
if($questionnaires eq "true"){ #Reporting branch for questionnaires
	my $sql3="
		SELECT
			q.DateAdded,
			q.CompletionDate,
			qc.QuestionnaireName_EN
		FROM
			Questionnaire AS q,
			QuestionnaireControl AS qc
		WHERE
			q.QuestionnaireControlSerNum = qc.QuestionnaireControlSerNum
			AND PatientSerNum = '$pser'
	";
	$query1 = $dbh->prepare($sql3) or die "Couldn't prepare: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute: " . $query1->errstr;
	$data3 = $query1->fetchall_arrayref();
}
my $data4;
if($education eq "true"){ #Reporting branch for educational material
	my $sql4="
		SELECT
			em.DateAdded,
			em.ReadStatus,
			emc.EducationalMaterialType_EN,
			emc.Name_EN
		FROM
			EducationalMaterial AS em,
			EducationalMaterialControl AS emc
		WHERE
			em.EducationalMaterialControlSerNum = emc.EducationalMaterialControlSerNum
			AND PatientSerNum = '$pser'
	";
	$query1 = $dbh->prepare($sql4) or die "Couldn't prepare: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute: " . $query1->errstr;
	$data4 = $query1->fetchall_arrayref();
}

my $data5;
if($testresults eq "true"){
	my $sql5="
		SELECT
			DateAdded,
			TestDate,
			ComponentName,
			AbnormalFlag,
			TestValue,
			MinNorm,
			MaxNorm,
			UnitDescription,
			ReadStatus
		FROM
			TestResult
		WHERE
			PatientSerNum = '$pser'
	";
	$query1 = $dbh->prepare($sql5) or die "Couldn't prepare: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute: " . $query1->errstr;
	$data5 = $query1->fetchall_arrayref();
}

my $data6;
if($notes eq "true"){
	my $sql6="
	SELECT
		n.DateAdded,
		n.LastUpdated,
		n.ReadStatus,
		nc.Name_EN,
		n.RefTableRowTitle_EN
	FROM
		Patient AS p,
		Notification AS n,
		NotificationControl AS nc
	WHERE
		p.PatientSerNum = n.PatientSerNum
		AND n.NotificationControlSerNum = nc.NotificationControlSerNum
		AND p.PatientSerNum = '$pser'
	";
	$query1 = $dbh->prepare($sql6) or die "Couldn't prepare: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute: " . $query1->errstr;
	$data6 = $query1->fetchall_arrayref();
}

my $data7;
if($clinicalnotes eq "true"){
	my $sql7="
	SELECT
		d.OriginalFileName,
		d.FinalFileName,
		d.CreatedTimeStamp,
		d.ApprovedTimeStamp,
		ae.ExpressionName
	FROM
		Document AS d,
		Patient AS p,
		AliasExpression AS ae
	WHERE
		d.PatientSerNum = p.PatientSerNum
		AND d.AliasExpressionSerNum = ae.AliasExpressionSerNum
		AND p.PatientSerNum = '$pser'";
	$query1 = $dbh->prepare($sql7) or die "Couldn't prepare: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute: " . $query1->errstr;
	$data7 = $query1->fetchall_arrayref();
}

my $data8;
if($treatingteam eq "true"){
	my $sql8="
	SELECT
		tx.DateAdded,
		pc.PostName_EN,
		tx.ReadStatus,
		pc.Body_EN
	FROM
		TxTeamMessage AS tx,
		PostControl AS pc,
		Patient AS p
	WHERE
	tx.PatientSerNum = p.PatientSerNum
	AND tx.PostControlSerNum = pc.PostControlSerNum
	AND p.PatientSerNum = '$pser'";
	$query1 = $dbh->prepare($sql8) or die "Couldn't prepare: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute: " . $query1->errstr;
	$data8 = $query1->fetchall_arrayref();
}

my $data9;
if($general eq "true"){
	my $sql9="
	SELECT
		a.DateAdded,
		a.ReadStatus,
		pc.PostName_EN,
		pc.Body_EN
	FROM
		Patient AS p,
		Announcement AS a,
		PostControl AS pc
	WHERE
		p.PatientSerNum = a.PatientSerNum
		AND a.PostControlSerNum = pc.PostControlSerNum
		AND p.PatientSerNum = '$pser'";
	$query1 = $dbh->prepare($sql9) or die "Couldn't prepare: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute: " . $query1->errstr;
	$data9 = $query1->fetchall_arrayref();
}

my $data10;
if($treatplan eq "true"){
	my $sql10="
	SELECT
		d.Description_EN,
		a.AliasType,
		pr.PriorityCode,
		ae.Description,
		a.AliasName_EN,
		a.AliasDescription_EN,
		t.Status,
		t.State,
		t.DueDateTime,
		t.CompletionDate
	FROM
		Task AS t,
		Patient AS p,
		AliasExpression AS ae,
		Alias AS a,
		Diagnosis AS d,
		Priority AS pr
	WHERE
		t.PatientSerNum = p.PatientSerNum
		AND p.PatientSerNum = pr.PatientSerNum
		AND ae.AliasExpressionSerNum = t.AliasExpressionSerNum
		AND ae.AliasSerNum = a.AliasSerNum
		AND t.DiagnosisSerNum = d.DiagnosisSerNum
		AND t.PrioritySerNum = pr.PrioritySerNum
		AND p.PatientSerNum = '$pser'";
	$query1 = $dbh->prepare($sql10) or die "Couldn't prepare: " . $dbh->errstr;
	$query1->execute() or die "Couldn't execute: " . $query1->errstr;
	$data10 = $query1->fetchall_arrayref();
}

# rec package used to format query data into a JSON object
package rec;
sub newDiag{ #This sub handles encoding for diagnosis records
	my $class = shift;
	my $row = {
		dsnum => shift,
		created => shift,
		desc => shift
	};
	my $rowHead = {
		diagrecord => $row
	};
	bless $rowHead, $class; #associate the Diag rowHead object with this class
	return $rowHead;
}

sub newAppt{ #This sub handles encoding for appointment records
	my $class = shift;
	my $row = {
		starttime => shift,
		status => shift,
		created => shift,
		aptDesc => shift,
		aptType => shift,
		resName => shift,
	};
	my $rowHead = {
		apptrecord => $row
	};
	bless $rowHead, $class; #associate the Appt rowHead object with this class
	return $rowHead;
}
sub newEduc{
	my $class = shift,
	my $row = {
		dateAdd => shift,
		readStatus => shift,
		matType => shift,
		matName => shift,
	};
	my $rowHead = {
		edcrecord => $row
	};
	bless $rowHead, $class;
	return $rowHead;
}

sub newTres{
	my $class = shift,
	my $row = {
		dateAdd => shift,
		dateTest => shift,
		compName => shift,
		flag => shift,
		testValue => shift,
		minNorm => shift,
		maxNorm => shift,
		unitDescription => shift,
		readStatus => shift,
	};
	my $rowHead ={
		resrecord => $row
	};
	bless $rowHead, $class;
	return $rowHead;
}

sub newQst{
	my $class = shift,
	my $row = {
		dateAdd => shift,
		dateComplete => shift,
		name => shift,
	};
	my $rowHead = {
		qstrecord => $row
	};
	bless $rowHead, $class; #associate the Qst rowHead object with this class
	return $rowHead;
}

sub newNotes{
	my $class = shift,
	my $row = {
		dateadd => shift,
		dateread => shift,
		readstatus => shift,
		notetype => shift,
		notedesc => shift,
	};
	my $rowHead ={
		noterecord => $row
	};
	bless $rowHead, $class;
	return $rowHead;
}

sub newTxPlanNote{
	my $class = shift,
	my $row = {
		diagdesc => shift,
		aliastype => shift,
		priority => shift,
		aedescription => shift,
		aliasname => shift,
		aliasdescription => shift,
		status => shift,
		state => shift,
		duedate => shift,
		completiondate => shift,
	};
	my $rowHead ={
		treatplanrecord => $row
	};
	bless $rowHead, $class;
	return $rowHead;
}

sub newgeneralNote{
	my $class = shift,
	my $row = {
		dateadded => shift,
		readstatus => shift,
		postname => shift,
		postbody => shift
	};
	my $rowHead = {
		generalrecord => $row
	};
	bless $rowHead, $class;
	return $rowHead;
}

sub newtxteamNote{
	my $class = shift,
	my $row = {
		dateadded => shift,
		postname => shift,
		readstatus => shift,
		postbody => shift,
	};
	my $rowHead = {
		txteamrecord => $row
	};
	bless $rowHead, $class;
	return $rowHead;
}

sub newClinNotes{
	my $class = shift,
	my $row = {
		ofilename => shift,
		ffilename => shift,
		ctimestamp => shift,
		atimestamp => shift,
		desc => shift,
	};
	my $rowHead = {
		clinnoterecord => $row
	};
	bless $rowHead, $class;
	return $rowHead;
}

package main;
my $json_str = JSON->new->allow_nonref;
$json_str->convert_blessed(1);
my $nextRow;
my $newRec;
my $diag_json_data;
my $appt_json_data;
my $qst_json_data;
my $educ_json_data;
my $tres_json_data;
my $notes_json_data;
my $clinnotes_json_data;
my $txteam_json_data;
my $general_json_data;
my $treatplan_json_data;

#iterate through each data structure and format into JSON using the rec package
foreach my $r (@$data1){ #Even if diagnosis is empty, we create this json data to serve as an anchor
	$nextRow = encode_json(\@$r);
	$newRec = newDiag rec(split(',',substr($nextRow,1,-1)));
	my $key = (keys %$newRec)[0];
	$diag_json_data->{$key} ||= [];
	push @{$diag_json_data->{$key}}, $newRec->{$key};
}

my $apptkey;
if(($appointments eq "true") && (scalar @$data2 != 0)){ #Encode the appointment data into json if the appointment param was "true"
	foreach my $r (@$data2){
		$nextRow = encode_json(\@$r);
		$newRec = newAppt rec(split(',',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$appt_json_data->{$key} ||= [];
		push @{$appt_json_data->{$key}}, $newRec->{$key};
	}
	$apptkey = (keys %$appt_json_data)[0]; #These lines use keys to append appointment json to the anchor diagnosis json
	push @{$diag_json_data->{$apptkey}}, $appt_json_data->{$apptkey};
}

my $qstkey;
if(($questionnaires eq "true") && (scalar @$data3 != 0)){
	foreach my $r (@$data3){
		$nextRow = encode_json(\@$r);
		$newRec = newQst rec(split(',',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$qst_json_data->{$key} ||= [];
		push @{$qst_json_data->{$key}}, $newRec->{$key};
	}
	$qstkey = (keys %$qst_json_data)[0];
	push @{$diag_json_data->{$qstkey}}, $qst_json_data->{$qstkey};
}

my $edckey;
if(($education eq "true") && (scalar @$data4 != 0)){
	foreach my $r (@$data4){
		$nextRow = encode_json(\@$r);
		$newRec = newEduc rec(split(',',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$educ_json_data->{$key} ||= [];
		push @{$educ_json_data->{$key}}, $newRec->{$key};
	}
	$edckey = (keys %$educ_json_data)[0];
	push @{$diag_json_data->{$edckey}}, $educ_json_data->{$edckey};
}

my $treskey;
if(($testresults eq "true") && (scalar @$data5 != 0)){
	foreach my $r (@$data5){
		$nextRow = encode_json(\@$r);
		$newRec = newTres rec(split('",',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$tres_json_data->{$key} ||= [];
		push @{$tres_json_data->{$key}}, $newRec->{$key};
	}
	$treskey = (keys %$tres_json_data)[0];
	push @{$diag_json_data->{$treskey}}, $tres_json_data->{$treskey};
}

my $notekey;
if(($notes eq "true") && (scalar @$data6 != 0)){
	foreach my $r (@$data6){
		$nextRow = encode_json(\@$r);
		$newRec = newNotes rec(split(',',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$notes_json_data->{$key} ||= [];
		push @{$notes_json_data->{$key}}, $newRec->{$key};
	}
	$notekey = (keys %$notes_json_data)[0];
	push @{$diag_json_data->{$notekey}}, $notes_json_data->{$notekey};
}

my $clinkey;
if(($clinicalnotes eq "true") && (scalar @$data7 != 0)){
	foreach my $r (@$data7){
		$nextRow = encode_json(\@$r);
		$newRec = newClinNotes rec(split(',',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$clinnotes_json_data->{$key} ||= [];
		push @{$clinnotes_json_data->{$key}}, $newRec->{$key};
	}
	$clinkey = (keys %$clinnotes_json_data)[0];
	push @{$diag_json_data->{$clinkey}}, $clinnotes_json_data->{$clinkey};
}

my $txteamkey;
if(($treatingteam eq "true") && (scalar @$data8 != 0)){
	foreach my $r (@$data8){
		$nextRow = encode_json(\@$r);
		$newRec = newtxteamNote rec(split(',',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$txteam_json_data->{$key} ||= [];
		push @{$txteam_json_data->{$key}}, $newRec->{$key};
	}
	$txteamkey = (keys %$txteam_json_data)[0];
	push @{$diag_json_data->{$txteamkey}}, $txteam_json_data->{$txteamkey};
}

my $generalkey;
if(($general eq "true") && (scalar @$data9 != 0)){
	foreach my $r (@$data9){
		$nextRow = encode_json(\@$r);
		$newRec = newgeneralNote rec(split(',',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$general_json_data->{$key} ||= [];
		push @{$general_json_data->{$key}}, $newRec->{$key};
	}
	$generalkey = (keys %$general_json_data)[0];
	push @{$diag_json_data->{$generalkey}}, $general_json_data->{$generalkey};
}

my $txplankey;
if(($treatplan eq "true") && (scalar @$data10 != 0)){
	foreach my $r (@$data10){
		$nextRow = encode_json(\@$r);
		$newRec = newTxPlanNote rec(split(',',substr($nextRow,1,-1)));
		my $key = (keys %$newRec)[0];
		$treatplan_json_data->{$key} ||= [];
		push @{$treatplan_json_data->{$key}}, $newRec->{$key};
	}
	$txplankey = (keys %$treatplan_json_data)[0];
	push @{$diag_json_data->{$txplankey}}, $treatplan_json_data->{$txplankey};
}

my $JSON = $json_str->encode($diag_json_data); #Encode the diagnosis JSON (And any of the other JSONs that have been appended to diagnosis)
print "$JSON\n"; #Return
$query1->finish;
$dbh->disconnect;
exit;

