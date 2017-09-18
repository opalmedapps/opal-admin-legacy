#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 26-Jul-2017 ++ File: Venue.pm
#---------------------------------------------------------------------------------
# Perl module that creates a venue class. This module calls a constructor to 
# create a venue object that contains venue information stored as object 
# variables.
#
# There exists various subroutines to set venue information, get venue information
# There exists various subroutines that use the Database.pm module to update the
# MySQL database and check if a venue exists already in this database.

package Venue; # Declare package name

use Database; # Use our custom Database module
use Storable qw(dclone); # for deep copies

#---------------------------------------------------------------------------------
# Connect to our database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Venue class 
#====================================================================================
sub new
{
	my $class = shift;
	my $venue = {
		_ser 			=> undef,
		_sourcedbser	=> undef,
		_sourceuid 		=> undef,
		_id 			=> undef,
	};
	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $venue, $class;
	return $venue;
}	

#====================================================================================
# Subroutine to set the venue serial
#====================================================================================
sub setVenueSer
{
	my ($venue, $ser) = @_; # venue object with provided serial in arguments
	$venue->{_ser} = $ser; # set the serial
	return $venue->{_ser};
}

#====================================================================================
# Subroutine to set the venue source database serial
#====================================================================================
sub setVenueSourceDatabaseSer
{
	my ($venue, $sourcedbser) = @_; # venue object with provided serial in arguments
	$venue->{_sourcedbser} = $sourcedbser; # set the serial
	return $venue->{_sourcedbser};
}

#====================================================================================
# Subroutine to set the venue source uid 
#====================================================================================
sub setVenueSourceUID
{
	my ($venue, $sourceuid) = @_; # venue object with provided uid in arguments
	$venue->{_sourceuid} = $sourceuid; # set the uid
	return $venue->{_sourceuid};
}

#====================================================================================
# Subroutine to set the venue id
#====================================================================================
sub setVenueId
{
	my ($venue, $id) = @_; # venue object with provided id in arguments
	$venue->{_id} = $id; # set the id
	return $venue->{_id};
}

#======================================================================================
# Subroutine to get the venue serial
#======================================================================================
sub getVenueSer
{
	my ($venue) = @_; # our venue object
	return $venue->{_ser};
}

#======================================================================================
# Subroutine to get the venue source database serial
#======================================================================================
sub getVenueSourceDatabaseSer
{
	my ($venue) = @_; # our venue object
	return $venue->{_sourcedbser};
}

#======================================================================================
# Subroutine to get the venue source uid 
#======================================================================================
sub getVenueSourceUID
{
	my ($venue) = @_; # our venue object
	return $venue->{_sourceuid};
}

#======================================================================================
# Subroutine to get the venue id
#======================================================================================
sub getVenueId
{
	my ($venue) = @_; # our venue object
	return $venue->{_id};
}

#======================================================================================
# Subroutine to get venue info from source DBs given a serial
#======================================================================================
sub getVenueInfoFromSourceDB
{
	my ($Venue) = @_; # Venue object from args

	my $sourcedbser = $Venue->getVenueSourceDatabaseSer();
	my $sourceuid = $Venue->getVenueSourceUID();

	######################################
    # ARIA
    ######################################
    if ($sourcedbser eq 1) {

        my $sourceDatabase = Database::connectToSourceDatabase($sourcedbser);

		my $venue_sql = "
			SELECT DISTINCT
				Venue.VenueId
			FROM
				variansystem.dbo.Venue Venue
			WHERE
				Venue.ResourceSer = '$sourceuid'
		";

		# prepare query
    	my $query = $sourceDatabase->prepare($venue_sql)
	    	or die "Could not prepare query: " . $sourceDatabase->errstr;
    
	    # execute query
    	$query->execute()
	    	or die "Could not execute query: " . $query->errstr;

    	while (my @data = $query->fetchrow_array()) {

			# query results 
			$id = $data[0];

			$Venue->setVenueId($id);
		}

		$sourceDatabase->disconnect();

	}

	######################################
    # MediVisit
    ######################################
    if ($sourcedbser eq 2) {

        my $sourceDatabase = Database::connectToSourceDatabase($sourcedbser);

		my $venue_sql = "
			SELECT DISTINCT 
				Venue.VenueId
			FROM
				Venue
			WHERE
				Venue.ResourceSer = '$sourceuid'
		";

		# prepare query
    	my $query = $sourceDatabase->prepare($venue_sql)
	    	or die "Could not prepare query: " . $sourceDatabase->errstr;
    
	    # execute query
    	$query->execute()
	    	or die "Could not execute query: " . $query->errstr;

    	while (my @data = $query->fetchrow_array()) {

			# query results
			$id = $data[0];

			$Venue->setVenueId($id);
		}

		$sourceDatabase->disconnect();

	}

	######################################
    # MOSAIQ
    ######################################
    if ($sourcedbser eq 3) {

        my $sourceDatabase = Database::connectToSourceDatabase($sourcedbser);

    	my $venue_sql = "SELECT 'QUERY_HERE'";

    	# prepare query
    	my $query = $sourceDatabase->prepare($venue_sql)
	    	or die "Could not prepare query: " . $sourceDatabase->errstr;
    
	    # execute query
    	$query->execute()
	    	or die "Could not execute query: " . $query->errstr;

    	while (my @data = $query->fetchrow_array()) {
    
    		# use setters to set appropriate resource information from query

    	}

    	$sourceDatabase->disconnect();
    }

	return $Venue;
}

#======================================================================================
# Subroutine to check if our venue exists in our MySQL db
#	@return: venue object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
	my ($venue) = @_; # venue object from args

	my $sourceuid 	= $venue->getVenueSourceUID();
	my $sourcedbser = $venue->getVenueSourceDatabaseSer();

	my $VenueSourceUIDInDB = 0; # false by default. Will be true if venue exists
	my $ExistingVenue = (); # data to be entered if venue exists 

	my ($ser, $id);

	my $inDB_sql = "
		SELECT DISTINCT
			Venue.VenueSerNum,
			Venue.SourceUID,
			Venue.VenueId
		FROM 
			Venue
		WHERE
			Venue.SourceUID 			= $sourceuid
		AND Venue.SourceDatabaseSerNum	= $sourcedbser
	";
		# prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

		$ser 				= $data[0];
		$VenueSourceUIDInDB = $data[1];
		$id 				= $data[2];
	}

	if ($VenueSourceUIDInDB) {

		$ExistingVenue = new Venue(); # initialize new object

		$ExistingVenue->setVenueSer($ser);
		$ExistingVenue->setVenueSourceDatabaseSer($sourcedbser);
		$ExistingVenue->setVenueSourceUID($sourceuid);
		$ExistingVenue->setVenueId($id);

		return $ExistingVenue; # this is true (i.e. venue exists) return object
	}

	else {return $ExistingVenue;} # this is false (i.e. venue DNE) return empty
}

#======================================================================================
# Subroutine to insert our venue info in our database
#======================================================================================
sub insertVenueIntoOurDB
{
	my ($venue) = @_; # our venue object

	# get all necessary details
	my $sourceuid 		= $venue->getVenueSourceUID();
	my $sourcedbser 	= $venue->getVenueSourceDatabaseSer();
	my $id 				= $venue->getVenueId();

	my $insert_sql = "
		INSERT INTO
			Venue (
				SourceDatabaseSerNum,
				SourceUID,
				VenueId,
				DateAdded
			)
		VALUE (
			'$sourcedbser',
			'$sourceuid',
			\"$id\",
			NOW()
		)
	";

	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the Venue
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the serial in our venue object
	$venue->setVenueSer($ser);

	return $venue;

}

#======================================================================================
# Subroutine to update our database with the venue's updated info
#======================================================================================
sub updateDatabase
{
	my ($venue) = @_; # our venue object

	# get all necessary details
	my $sourceuid 		= $venue->getVenueSourceUID();
	my $sourcedbser 	= $venue->getVenueSourceDatabaseSer();
	my $id 				= $venue->getVenueId();

	my $update_sql = "
		UPDATE
			Venue
		SET
			VenueId = \"$id\"
		WHERE
			SourceDatabaseSerNum 	= '$sourcedbser'
		AND SourceUID 				= '$sourceuid'
	";
	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;	
}

#======================================================================================
# Subroutine to compare two venue objects. If different, use setter functions
# to update venue object.
#======================================================================================
sub compareWith
{
	my ($SuspectVenue, $OriginalVenue) = @_; # two venue object
	my $UpdatedVenue = dclone($OriginalVenue); # deep copy

	# retrieve parameters
	# Suspect venue
	my $Sid = $SuspectVenue->getVenueId();

	# Original venue
	my $Oid = $OriginalVenue->getVenueId();

	# go through each parameter
	if ($Sid ne $Oid) {
		print "Venue ID has changed from '$Oid' to '$Sid'\n";
		my $updatedName = $UpdatedVenue->setVenueId($Sid); # update
		print "Will update database entry to '$updatedId'.\n";
	}

	return $UpdatedVenue;
}

#======================================================================================
# Subroutine to reassign our venue serial in ARIA to a venue serial in MySQL. 
# In the process, insert venue into our database if it DNE
#======================================================================================
sub reassignVenue
{
	my ($sourceuid, $sourcedbser) = @_; 

	# first check if the venue serial is defined
	# if not, return zero 
	if (!$sourceuid) {
		return 0;
	}

	my $Venue = new Venue(); # initialize venue object

	$Venue->setVenueSourceUID($sourceuid);
	$Venue->setVenueSourceDatabaseSer($sourcedbser);

	# get venue info from source DB
	$Venue = $Venue->getVenueInfoFromSourceDB();

	# check if the venue exists in our database
	my $VenueExists = $Venue->inOurDatabase();

	if ($VenueExists) {

		my $ExistingVenue = dclone($VenueExists); # reassign variable

		my $UpdatedVenue = $Venue->compareWith($ExistingVenue);

		# update database
		$UpdatedVenue->updateDatabase();

		my $venueSer = $ExistingVenue->getVenueSer(); # get serial

		return $venueSer;
	}
	else { # venue DNE
	
		# insert venue into our DB
		$Venue = $Venue->insertVenueIntoOurDB();

		# get serial
		my $venueSer = $Venue->getVenueSer();

		return $venueSer;
	}
}

# To exit/return always true (for the module itself)
1;