#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 05-Jun-2017 ++ File: Email.pm
#---------------------------------------------------------------------------------
# Perl module that creates an email class. This module calls a constructor
# to create an email object that contains email information stored as
# object variables
#
# There exists various subroutines to set / get information and compare information
# between two email objects.

package Email; # Declaring package name

use Database;
use MIME::Lite; # emailing

#---------------------------------------------------------------------------------
# Connect to the database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Email class 
#====================================================================================
sub new
{
	my $class = shift;
	my $email = {
		_controlser			=> undef,
		_toaddress 			=> undef,
		_fromaddress 		=> "opal\@muhc.mcgill.ca",
		_subject			=> undef,
		_body				=> undef,
		_type				=> undef,
		_status				=> undef,
		_cronlogser			=> undef,
	};

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $email, $class;
    return $email;
}

#====================================================================================
# Subroutine to set the Email Control Serial
#====================================================================================
sub setEmailControlSer
{
    my ($email, $controlser) = @_; # email object with provided serial in args
    $email->{_controlser} = $controlser; # set the ser
    return $email->{_controlser};
}

#====================================================================================
# Subroutine to set the Email To Address
#====================================================================================
sub setEmailToAddress
{
    my ($email, $toaddress) = @_; # email object with provided address in args
    $email->{_toaddress} = $toaddress; # set the toaddress
    return $email->{_toaddress};
}

#====================================================================================
# Subroutine to set the Email From Address
#====================================================================================
sub setEmailFromAddress
{
    my ($email, $fromaddress) = @_; # email object with provided address in args
    $email->{_fromaddress} = $fromaddress; # set the address
    return $email->{_fromaddress};
}

#====================================================================================
# Subroutine to set the Email Subject
#====================================================================================
sub setEmailSubject
{
    my ($email, $subject) = @_; # email object with provided subject in args
    $email->{_subject} = $subject; # set the subject
    return $email->{_subject};
}

#====================================================================================
# Subroutine to set the Email Body
#====================================================================================
sub setEmailBody
{
    my ($email, $body) = @_; # email object with provided body in args
    $email->{_body} = $body; # set the body
    return $email->{_body};
}

#====================================================================================
# Subroutine to set the Email Type
#====================================================================================
sub setEmailType
{
    my ($email, $type) = @_; # email object with provided type in args
    $email->{_type} = $type; # set the type
    return $email->{_type};
}

#====================================================================================
# Subroutine to set the Email Status
#====================================================================================
sub setEmailStatus
{
    my ($email, $status) = @_; # email object with provided status in args
    $email->{_status} = $status; # set the status
    return $email->{_status};
}

#====================================================================================
# Subroutine to set the Email Cron Log Serial
#====================================================================================
sub setEmailCronLogSer
{
    my ($email, $cronlogser) = @_; # email object with provided serial in args
    $email->{_cronlogser} = $cronlogser; # set the ser
    return $email->{_cronlogser};
}

#====================================================================================
# Subroutine to get the Email Control Serial
#====================================================================================
sub getEmailControlSer
{
	my ($email) = @_; # our email object
	return $email->{_controlser};
}

#====================================================================================
# Subroutine to get the Email To Address
#====================================================================================
sub getEmailToAddress
{
	my ($email) = @_; # our email object
	return $email->{_toaddress};
}

#====================================================================================
# Subroutine to get the Email From Address
#====================================================================================
sub getEmailFromAddress
{
	my ($email) = @_; # our email object
	return $email->{_fromaddress};
}

#====================================================================================
# Subroutine to get the Email Subject
#====================================================================================
sub getEmailSubject
{
	my ($email) = @_; # our email object
	return $email->{_subject};
}

#====================================================================================
# Subroutine to get the Email Body
#====================================================================================
sub getEmailBody
{
	my ($email) = @_; # our email object
	return $email->{_body};
}

#====================================================================================
# Subroutine to get the Email Type
#====================================================================================
sub getEmailType
{
	my ($email) = @_; # our email object
	return $email->{_type};
}

#====================================================================================
# Subroutine to get the Email Status
#====================================================================================
sub getEmailStatus
{
	my ($email) = @_; # our email object
	return $email->{_status};
}

#====================================================================================
# Subroutine to get the Email Cron Log Serial
#====================================================================================
sub getEmailCronLogSer
{
	my ($email) = @_; # our email object
	return $email->{_cronlogser};
}

#====================================================================================
# Subroutine to get email details according to patient and type
#====================================================================================
sub getEmailControlDetails
{
	my ($patientser, $emailtype) = @_; # args

	my $email = new Email(); # initialize

	my $select_sql = "
		SELECT DISTINCT
			ec.EmailControlSerNum,
			CASE 
				WHEN pt.Language = 'EN' THEN ec.Subject_EN
				WHEN pt.Language = 'FR' THEN ec.Subject_FR
			END AS Subject,
			CASE
				WHEN pt.Language = 'EN' THEN ec.Body_EN
				WHEN pt.Language = 'FR' THEN ec.Body_FR
			END AS Body
		FROM
			Patient pt,
			EmailControl ec,
			EmailType et
		WHERE
			pt.PatientSerNum 			= '$patientser'
		AND et.EmailTypeId 				= '$emailtype'
		AND et.EmailTypeSerNum			= ec.EmailTypeSerNum
	";

	# prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

		$controlser 		= $data[0];
		$subject 			= $data[1];
		$body 				= $data[2];

		$email->setEmailControlSer($controlser);
		$email->setEmailSubject($subject);
		$email->setEmailBody($body);
	}

	return $email;
}

#====================================================================================
# Subroutine to send an email 
#====================================================================================
sub sendEmail
{
	my ($email, $patientser) = @_; # get email object from args

	# get email details
	my $fromAddress = $email->getEmailFromAddress();
	my $toAddress = $email->getEmailToAddress();
	my $subject = $email->getEmailSubject();
	my $body = $email->getEmailBody();

	# send via MIME
	my $mime = MIME::Lite->new(
		'From'		=> $fromAddress,
		'To'		=> $toAddress,
		'Subject'	=> $subject,
		'Type'		=> 'text/html',
		'Data'		=> $body,
	);

	# TODO: check return, add status as parameter
	my $response = $mime->send('smtp', '172.25.123.208');

	print "EMAIL STATUS: $response";

	$email->setEmailStatus("T");
	if (!$response) {$email->setEmailStatus("F");}

	# insert email log 
	$email->insertEmailLogIntoOurDB($patientser);

}

#======================================================================================
# Subroutine to insert our email log info in our database
#======================================================================================
sub insertEmailLogIntoOurDB
{
	my ($email, $patientser) = @_; # our email log and patient serial

	# Retrieve necessary details 
	my $controlser = $email->getEmailControlSer();
	my $status = $email->getEmailStatus();
	my $cronlogser = $email->getEmailCronLogSer();

	my $insert_sql = "
		INSERT INTO 
			EmailLog (
				PatientSerNum,
				CronLogSerNum,
				EmailControlSerNum,
				Status,
				DateAdded
			)
		VALUES (
			'$patientser',
			'$cronlogser',
			'$controlser',
			'$status',
			NOW()
		)
	";

	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	return $email;

}

# exit smoothly 
1;
