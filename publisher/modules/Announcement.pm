#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 03-May-2016 ++ File: Announcement.pm
#---------------------------------------------------------------------------------
# Perl module that creates an announcement class. This module calls a constructor to
# create an announcement object that contains announcement information stored as object
# variables.
#
# There exists various subroutines to set and get announcement information and compare
# announcement information between two announcement objects.

package Announcement; # Declaring package name

use Database; # Our custom database module
use Time::Piece; # perl module
use Array::Utils qw(:all);
use POSIX; # perl module

use Patient; # Our custom patient module
use PatientDoctor; # PatientDoctor.pm
use Alias; # Alias.pm
use ResourceAppointment; # ResourceAppointment.pm
use Diagnosis; # Diagnosis.pm
use Appointment; # Our custom appointment module
use Filter; # Our custom filter module
use PostControl; # Our custom post control module
use PushNotification;

#---------------------------------------------------------------------------------
# Connect to the databases
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

my $verbose = 0;

#====================================================================================
# Constructor for our Announcement class 
#====================================================================================
sub new
{
    my $class = shift;
    my $announcement = {
        _ser            => undef,
        _patientser     => undef,
        _postcontrolser => undef,
        _readstatus     => undef,
        _cronlogser 	=> undef,
    };

    # bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
    bless $announcement, $class;
    return $announcement;
}

#====================================================================================
# Subroutine to set the Announcement Serial
#====================================================================================
sub setAnnouncementSer
{
    my ($announcement, $ser) = @_; # announcement object with provided serial in args
    $announcement->{_ser} = $ser; # set the announcement ser
    return $announcement->{_ser};
}

#====================================================================================
# Subroutine to set the Announcement Patient Serial
#====================================================================================
sub setAnnouncementPatientSer
{
    my ($announcement, $patientser) = @_; # announcement object with provided serial in args
    $announcement->{_patientser} = $patientser; # set the announcement ser
    return $announcement->{_patientser};
}

#====================================================================================
# Subroutine to set the Announcement Post Control Serial
#====================================================================================
sub setAnnouncementPostControlSer
{
    my ($announcement, $postcontrolser) = @_; # announcement object with provided serial in args
    $announcement->{_postcontrolser} = $postcontrolser; # set the announcement ser
    return $announcement->{_postcontrolser};
}

#====================================================================================
# Subroutine to set the Announcement Read Status
#====================================================================================
sub setAnnouncementReadStatus
{
    my ($announcement, $readstatus) = @_; # announcement object with provided status in args
    $announcement->{_readstatus} = $readstatus; # set the announcement status
    return $announcement->{_readstatus};
}

#====================================================================================
# Subroutine to set the Announcement Cron Log Serial
#====================================================================================
sub setAnnouncementCronLogSer
{
    my ($announcement, $cronlogser) = @_; # announcement object with provided serial in args
    $announcement->{_cronlogser} = $cronlogser; # set the announcement ser
    return $announcement->{_cronlogser};
}

#====================================================================================
# Subroutine to get the Announcement Serial
#====================================================================================
sub getAnnouncementSer
{
	my ($announcement) = @_; # our announcement object
	return $announcement->{_ser};
}

#====================================================================================
# Subroutine to get the Announcement Patient Serial
#====================================================================================
sub getAnnouncementPatientSer
{
	my ($announcement) = @_; # our announcement object
	return $announcement->{_patientser};
}

#====================================================================================
# Subroutine to get the Announcement Post Control Serial
#====================================================================================
sub getAnnouncementPostControlSer
{
	my ($announcement) = @_; # our announcement object
	return $announcement->{_postcontrolser};
}

#====================================================================================
# Subroutine to get the Announcement Read Status
#====================================================================================
sub getAnnouncementReadStatus
{
	my ($announcement) = @_; # our announcement object
	return $announcement->{_readstatus};
}

#====================================================================================
# Subroutine to get the Announcement Cron Log Serial
#====================================================================================
sub getAnnouncementCronLogSer
{
	my ($announcement) = @_; # our announcement object
	return $announcement->{_cronlogser};
}

#======================================================================================
# Subroutine to publish announcement
#======================================================================================
sub publishAnnouncements
{
    my ($cronLogSer, @patientList) = @_; # patient list and cron log serial from args

    my $today_date = strftime("%Y-%m-%d", localtime(time));
    my $now = Time::Piece->strptime(strftime("%Y-%m-%d %H:%M:%S", localtime(time)), "%Y-%m-%d %H:%M:%S");

    # Date object of today at 8AM
    my $today_at_eightAM = Time::Piece->strptime($today_date . " 08:00:00", "%Y-%m-%d %H:%M:%S");
    # Date object of today at 8PM
    my $today_at_eightPM = Time::Piece->strptime($today_date . " 20:00:00", "%Y-%m-%d %H:%M:%S");

    # If we are not within the window to publish announcements then return
    if ( (($now - $today_at_eightAM) < 0) or (($now - $today_at_eightPM) > 0) ) {return;}

    my @announcementControls = PostControl::getPostControlsMarkedForPublish('Announcement');

    foreach my $Patient (@patientList) {

        my $patientSer          = $Patient->getPatientSer(); # get patient serial
		my $patientId 			= $Patient->getPatientId(); # get patient id 

		print "Patient ID: $patientId\n" if $verbose;

        foreach my $PostControl (@announcementControls) {

            my $postControlSer          = $PostControl->getPostControlSer();
            my $postPublishDate         = $PostControl->getPostControlPublishDate();
            my $postFilters             = $PostControl->getPostControlFilters();

			print "Post Control Serial: $postControlSer\n" if $verbose;

            if ($postPublishDate) { # which there should be for announcements

                # Make datetime into object
                $postPublishDate = Time::Piece->strptime($postPublishDate, "%Y-%m-%d %H:%M:%S");
                # Extract date part only
                $postPublishDate = $postPublishDate->date;
                #extract today's date
                $nowDate = $now->date;

				print "Publish date: $postPublishDate\n" if $verbose;
				print "today's date: $nowDate\n" if $verbose;

				if ($postPublishDate ne $nowDate) { # only publish on today's date
					next;
				}

				# Fetch patient filters (if any)
				my @patientFilters = $postFilters->getPatientFilters();

				# We will flag whether there are patient filters or other (non-patient) filters
				# The reason is that the patient filter will combine as an OR with the non-patient filters
				# If any of the non-patient filters exist, all non-patient filters combine in an AND (i.e. intersection)
				# However, we don't want to lose the exception that a patient filter has been defined
				# If there is a patient filter defined, then we only send the content to the patients 
				# selected in the filter UNLESS other non-patient filters have been defined. In that case,
				# we send to the patients defined in the patient filters AND to the patients that pass
				# in the non-patient filters  
				my $isNonPatientSpecificFilterDefined = 0;
				my $isPatientSpecificFilterDefined = 0;
				my $patientPassed = 0;

                my @aliasSerials = ();

                my @diagnosisNames = Diagnosis::getPatientsDiagnosesFromOurDB($patientSer);

                my @patientDoctors = PatientDoctor::getPatientsDoctorsFromOurDB($patientSer);
                    
                # Fetch appointment filters (if any)
                my @appointmentFilters =  $postFilters->getAppointmentFilters();
                if (@appointmentFilters) {

					print "Appointment filters exist for this announcement\n" if $verbose;
      
					# toggle flag
					$isNonPatientSpecificFilterDefined = 1;

                    # Retrieve the patient appointment(s) if one (or more) lands within one day of today
                    my @patientAppointments = Appointment::getPatientsAppointmentsFromDateInOurDB($patientSer, $postPublishDate, 0);

                    # we build all possible appointment names, and diagnoses for each appointment found
                    foreach my $appointment (@patientAppointments) {

                        my $expressionSer = $appointment->getApptAliasExpressionSer();
                        my $aliasSer = Alias::getAliasFromOurDB($expressionSer);
                        push(@aliasSerials, $aliasSer) unless grep{$_ == $aliasSer} @aliasSerials;

                    }

                    # if all appointments were selected as triggers then patient passes
	                # else do further checks 
	                unless ('ALL' ~~ @appointmentFilters and @aliasSerials) {

	                    # Finding the existence of the patient appointment in the appointment filters
						# If there is an intersection, then patient is so far part of this publishing announcement
	                    if (!intersect(@appointmentFilters, @aliasSerials)) {

							print "Patient's appointments are not in filters\n" if $verbose;
							if (@patientFilters) {
	                        	# if the patient failed to match the appointment filter but there are patient filters
								# then we flag to check later if this patient matches with the patient filters
								$isPatientSpecificFilterDefined = 1;

								print "Patient filters exist\n" if $verbose;
							}
							# else no patient filters were defined and failed to match the expression filter
							# move on to the next announcement
							else{
								print "Patient filters do not exist\n" if $verbose;
								next;
							}
						}
					}
                }

                # Fetch diagnosis filters (if any)
                my @diagnosisFilters = $postFilters->getDiagnosisFilters();
                if (@diagnosisFilters) {

					print "Diagnosis filters exist for this announcement\n" if $verbose;

					# toggle flag
					$isNonPatientSpecificFilterDefined = 1;

					# if all diagnoses were selected as triggers then patient passes
	                # else do further checks 
	                unless ('ALL' ~~ @diagnosisFilters and @diagnosisNames) {
	                    # Finding the intersection of the patient's diagnosis and the diagnosis filters
						# If there is an intersection, then patient is so far part of this publishing announcement
	                    if (!intersect(@diagnosisFilters, @diagnosisNames)) {
							print "Patient's diagnoses are not in filters\n" if $verbose;
							if (@patientFilters) {
								# if the patient failed to match the diagnosis filter but there are patient filters
								# then we flag to check later if this patient matches with the patient filters
								$isPatientSpecificFilterDefined = 1;
								print "Patient filters exist\n" if $verbose;
							}
							# else no patient filters were defined and failed to match the diagnosis filter
							# move on to the next announcement
							else{
								print "Patient filters do not exist\n" if $verbose;
								next;
							}
						}
					}
                }

                # Fetch doctor filters (if any)
                my @doctorFilters = $postFilters->getDoctorFilters();
                if (@doctorFilters) {

					print "Doctor filters exist for this announcement\n" if $verbose;
					# toggle flag
					$isNonPatientSpecificFilterDefined = 1;

					# if all doctors were selected as triggers then patient passes
	                # else do further checks 
	                unless ('ALL' ~~ @doctorFilters and @patientDoctors) {
	                    # Finding the intersection of the patient's doctor(s) and the doctor filters
						# If there is an intersection, then patient is so far part of this publishing announcement
	                    if (!intersect(	@doctorFilters, @patientDoctors)) {
							print "Patient's doctors are not in filters\n" if $verbose;
							if (@patientFilters) {
								# if the patient failed to match the doctor filter but there are patient filters
								# then we flag to check later if this patient matches with the patient filters
								$isPatientSpecificFilterDefined = 1;
								print "Patient filters exist\n" if $verbose;
							}
							# else no patient filters were defined and failed to match the doctor filter
							# move on to the next announcement
							else{
								print "Patient filters do not exist\n" if $verbose;
								next;
							}
						}
					}
                }

                # Fetch resource filters (if any)
                my @resourceFilters = $postFilters->getResourceFilters();
                if (@resourceFilters) {

					print "Treatment machine filters exist for this announcement\n" if $verbose;
					# toggle flag
					$isNonPatientSpecificFilterDefined = 1;

					# if all resources were selected as triggers then patient passes
	                # else do further checks 
	                unless ('ALL' ~~ @resourceFilters and @patientResources) {
	                    # Finding the intersection of the patient resource(s) and the resource filters
						# If there is an intersection, then patient is so far part of this publishing announcement
	                    if (!intersect(@resourceFilters, @patientResources)) {
							print "Patient's machine are not in filters\n" if $verbose;
							if (@patientFilters) {
								# if the patient failed to match the resource filter but there are patient filters
								# then we flag to check later if this patient matches with the patient filters
								print "Patient filters exist\n" if $verbose;
								$isPatientSpecificFilterDefined = 1;
							}
							# else no patient filters were defined and failed to match the resource filter
							# move on to the next announcement
							else{
								print "Patient filters do not exist\n" if $verbose;
								next;
							}
						}
					}
                }

				# We look into whether any patient-specific filters have been defined 
				# If we enter this if statement, then we check if that patient is in that list
				if (@patientFilters) {

					# if the patient-specific flag was enabled then it means this patient failed
					# one of the filters above 
					# OR if the non patient specific flag was disabled then there were no filters defined above
					# and this is the last test to see if this patient passes
					if ($isPatientSpecificFilterDefined eq 1 or $isNonPatientSpecificFilterDefined eq 0) {
						# Finding the existence of the patient in the patient-specific filters
						# If the patient exists, or all patients were selected as triggers, 
	                    # then patient passes else move on to next patient
                        if ($patientId ~~ @patientFilters or 'ALL' ~~ @patientFilters) {
                        	$patientPassed = 1;
							print "Patient is in patient filters\n" if $verbose;
						}
                        else {
							print "Patient not in patient filters\n" if $verbose;
							next;
						}
					}
				}

	            if ($isNonPatientSpecificFilterDefined eq 1 or $isPatientSpecificFilterDefined eq 1 or ($isNonPatientSpecificFilterDefined eq 0 and $patientPassed eq 1)) {
				
	                # If we've reached this point, we've passed all catches (filter restrictions). We make
	                # an announcement object, check if it exists already in the database. If it does 
	                # this means the announcement has already been published to the patient. If it doesn't
	                # exist then we publish to the patient (insert into DB).
	                $announcement = new Announcement();

	                # set the necessary values
	                $announcement->setAnnouncementPatientSer($patientSer);
	                $announcement->setAnnouncementPostControlSer($postControlSer);
	                $announcement->setAnnouncementCronLogSer($cronLogSer);

	                if (!$announcement->inOurDatabase()) {
	    
						print "Announcement not in our database \n" if $verbose;
	                    $announcement = $announcement->insertAnnouncementIntoOurDB();

						print "Inserted announcement\n" if $verbose;
	                    # send push notification
	                    my $announcementSer = $announcement->getAnnouncementSer();
	                    my $patientSer = $announcement->getAnnouncementPatientSer();
	                    PushNotification::sendPushNotification($patientSer, $announcementSer, 'Announcement');

	                }
	            }
            } # End if postPublishDate

        } # End forEach PostControl   

    } # End forEach Patient

}

#======================================================================================
# Subroutine to check if our announcement exists in our MySQL db
#	@return: announcement object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
    my ($announcement) = @_; # our announcement object
    my $patientser = $announcement->getAnnouncementPatientSer(); # get patient serial
    my $postcontrolser = $announcement->getAnnouncementPostControlSer(); # get post control serial

    my $serInDB = 0; # false by default. Will be true if announcement exists
    my $ExistingAnnouncement = (); # data to be entered if announcement exists

    # Other variables, if announcement exists
    my ($readstatus, $cronlogser);

    my $inDB_sql = "
        SELECT
            an.AnnouncementSerNum,
            an.ReadStatus,
            an.CronLogSerNum
        FROM
            Announcement an
        WHERE
            an.PatientSerNum = '$patientser'
        AND an.PostControlSerNum = '$postcontrolser'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

        $serInDB    = $data[0];
        $readstatus = $data[1];
        $cronlogser = $data[2];
    }

    if ($serInDB) {

        $ExistingAnnouncement = new Announcement(); # initialize object

        # set parameters
        $ExistingAnnouncement->setAnnouncementSer($serInDB); 
        $ExistingAnnouncement->setAnnouncementPatientSer($patientser);
        $ExistingAnnouncement->setAnnouncementPostControlSer($postcontrolser); 
        $ExistingAnnouncement->setAnnouncementReadStatus($readstatus);
        $ExistingAnnouncement->setAnnouncementCronLogSer($cronlogser);

        return $ExistingAnnouncement; # this is true (ie. announcement exists, return object)
    }

    else {return $ExistingAnnouncement;} # this is false (ie. announcement DNE, return empty)
}

#======================================================================================
# Subroutine to insert our announcement info in our database
#======================================================================================
sub insertAnnouncementIntoOurDB
{
    my ($announcement) = @_; # our announcement object 

    my $patientser      = $announcement->getAnnouncementPatientSer();
    my $postcontrolser  = $announcement->getAnnouncementPostControlSer();
    my $cronlogser  	= $announcement->getAnnouncementCronLogSer();

    my $insert_sql = "
        INSERT INTO
            Announcement (
                PatientSerNum,
                CronLogSerNum,
                PostControlSerNum,
                DateAdded
            )
        VALUES (
            '$patientser',
            '$cronlogser',
            '$postcontrolser',
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

	# Set the Serial in our object
	$announcement->setAnnouncementSer($ser);
	
	return $announcement;
}


# To exit/return always true (for the module itself)
1;	
