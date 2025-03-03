#!/usr/bin/perl

# SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

#---------------------------------------------------------------------------------
# A.Joseph 04-May-2016 ++ File: PostControl.pm
#---------------------------------------------------------------------------------
# Perl module that creates a post control class. This module calls a constructor to 
# create a post control object that contains post control information stored parameters
# 
# There exists various subroutines to set / get post control information. 

package PostControl; # Declare package name

use Database; # Our custom module Database.pm
use Filter; # Our custom module Filter.pm

#---------------------------------------------------------------------------------
# Connect to the database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our PostControl class 
#====================================================================================
sub new
{
    my $class = shift;
    my $postcontrol = {
        _ser            => undef,
        _type           => undef,
        _publishflag    => undef,
        _publishdate    => undef,
        _lastpublished  => undef,
        _filters        => undef,
    };

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $postcontrol, $class; 
	return $postcontrol;
}

#======================================================================================
# Subroutine to set the post control serial
#======================================================================================
sub setPostControlSer
{
	my ($postcontrol, $ser) = @_; # post control object with provided serial in arguments
	$postcontrol->{_ser} = $ser; # set the serial
	return $postcontrol->{_ser};
}

#======================================================================================
# Subroutine to set the post control type
#======================================================================================
sub setPostControlType
{
	my ($postcontrol, $type) = @_; # post control object with provided type in arguments
	$postcontrol->{_type} = $type; # set the type
	return $postcontrol->{_type};
}

#======================================================================================
# Subroutine to set the post control publish flag
#======================================================================================
sub setPostControlPublishFlag
{
	my ($postcontrol, $publishflag) = @_; # post control object with provided flag in arguments
	$postcontrol->{_publishflag} = $publishflag; # set the flag
	return $postcontrol->{_publishflag};
}

#======================================================================================
# Subroutine to set the post control publish date
#======================================================================================
sub setPostControlPublishDate
{
	my ($postcontrol, $publishdate) = @_; # post control object with provided date in arguments
	$postcontrol->{_publishdate} = $publishdate; # set the date
	return $postcontrol->{_publishdate};
}

#======================================================================================
# Subroutine to set the post control last published
#======================================================================================
sub setPostControlLastPublished
{
	my ($postcontrol, $lastpublished) = @_; # post control object with provided lastpublished in arguments
	$postcontrol->{_lastpublished} = $lastpublished; # set the last published
	return $postcontrol->{_lastpublished};
}

#======================================================================================
# Subroutine to set the post control filters
#======================================================================================
sub setPostControlFilters
{
	my ($postcontrol, $filters) = @_; # post control object with provided filters in arguments
	$postcontrol->{_filters} = $filters; # set the filter
	return $postcontrol->{_filters};
}

#======================================================================================
# Subroutine to get the post control serial
#======================================================================================
sub getPostControlSer
{
	my ($postcontrol) = @_; # our post control object
	return $postcontrol->{_ser};
}

#======================================================================================
# Subroutine to get the post control type
#======================================================================================
sub getPostControlType
{
	my ($postcontrol) = @_; # our post control object
	return $postcontrol->{_type};
}

#======================================================================================
# Subroutine to get the post control publish flag
#======================================================================================
sub getPostControlPublishFlag
{
	my ($postcontrol) = @_; # our post control object
	return $postcontrol->{_publishflag};
}

#======================================================================================
# Subroutine to get the post control publish date
#======================================================================================
sub getPostControlPublishDate
{
	my ($postcontrol) = @_; # our post control object
	return $postcontrol->{_publishdate};
}

#======================================================================================
# Subroutine to get the post control last published
#======================================================================================
sub getPostControlLastPublished
{
	my ($postcontrol) = @_; # our post control object
	return $postcontrol->{_lastpublished};
}

#======================================================================================
# Subroutine to get the post control filters
#======================================================================================
sub getPostControlFilters
{
	my ($postcontrol) = @_; # our post control object
	return $postcontrol->{_filters};
}

#======================================================================================
# Subroutine to get a list of post controls marked for publish
#======================================================================================
sub getPostControlsMarkedForPublish
{
    my ($postType) = @_; # the type from args
    my @postControlList = (); # initialize a list 

    my $info_sql = "
        SELECT DISTINCT
            pc.PostControlSerNum,
            pc.PostType,
            pc.PublishDate,
            pc.LastPublished
        FROM
            PostControl pc
        WHERE
            pc.PublishFlag      = 1
        AND pc.PostType         = '$postType'
    ";

	# prepare query
	my $query = $SQLDatabase->prepare($info_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        my $postControl = new PostControl(); # new object

        my $ser            = $data[0];
        my $type           = $data[1];
        my $publishdate    = $data[2];
        my $lastpublished  = $data[3];

        # set post control information
        $postControl->setPostControlSer($ser);
        $postControl->setPostControlType($type);
        $postControl->setPostControlPublishDate($publishdate);
        $postControl->setPostControlLastPublished($lastpublished);

        # get all the filters
        my $filters = Filter::getAllFiltersFromOurDB($ser, 'PostControl');

        $postControl->setPostControlFilters($filters);

        push(@postControlList, $postControl);
    }

    return @postControlList;
}
# get post controls marked for publish from the cronControlPost table, by type
sub getPostControlsMarkedForPublishModularCron
{

    my ($postType) = @_; # the type from args
    my @postControlList = (); # initialize a list

    $control_table = "";
    if($postType eq 'Announcement'){
        $control_table = "cronControlPost_Announcement";
    }elsif($postType eq 'Treatment Team Message'){
        $control_table = "cronControlPost_TreatmentTeamMessage";
    }

    my $info_sql = "
        SELECT DISTINCT
            ccp.cronControlPostSerNum as PostControlSerNum,
            pc.PostType,
            pc.PublishDate,
            ccp.lastPublished
        FROM
            PostControl pc,
            $control_table ccp
        WHERE
            ccp.publishFlag      = 2
        AND pc.PostControlSerNum = ccp.cronControlPostSerNum
    ";

	# prepare query
	my $query = $SQLDatabase->prepare($info_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	while (my @data = $query->fetchrow_array()) {

        my $postControl = new PostControl(); # new object

        my $ser            = $data[0];
        my $type           = $data[1];
        my $publishdate    = $data[2];
        my $lastpublished  = $data[3];

        # set post control information
        $postControl->setPostControlSer($ser);
        $postControl->setPostControlType($type);
        $postControl->setPostControlPublishDate($publishdate);
        $postControl->setPostControlLastPublished($lastpublished);

        # get all the filters
        my $filters = Filter::getAllFiltersFromOurDB($ser, 'PostControl');

        $postControl->setPostControlFilters($filters);

        push(@postControlList, $postControl);
    }

    return @postControlList;
}

#======================================================================================
# Subroutine to set/update the "last published" field to current time 
#======================================================================================
sub setPostControlLastPublishedIntoOurDB
{
    my ($current_datetime) = @_; # our current datetime in args

    my $update_sql = "
        UPDATE
            PostControl
        SET
            LastPublished = '$current_datetime'
        WHERE
            PublishFlag = 1
    ";
    	
    # prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
}


#======================================================================================
# Subroutine to set/update the "last published" field to current time for modular controllers
#======================================================================================
sub setPostControlLastPublishedModularControllers
{
    my ($current_datetime, $module) = @_; # our current datetime in args

    $control_table = "";
    if($module eq 'Announcement'){
        $control_table = "cronControlPost_Announcement";
    }elsif($module eq 'Treatment Team Message'){
        $control_table = "cronControlPost_TreatmentTeamMessage";
    }

    my $update_sql = "
        UPDATE $control_table CCP, PostControl PC
        SET PC.LastPublished = '$current_datetime',
            CCP.lastPublished = '$current_datetime',
            CCP.publishFlag = 1
        WHERE CCP.cronControlPostSerNum = PC.PostControlSerNum
            AND CCP.publishFlag = 2
        ;
    ";
    	
    # prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
}

#======================================================================================
# Subroutine to sync the master table to the slave table and then set the publish flag 
# from 1 to 2. This will identify what is currently being process by the cron job vs what
# have just been activated during the cron running
#======================================================================================
sub CheckPostControlsMarkedForPublishModularCron
{
	my ($module) = @_; # current datetime, cron module type,

    $control_table = "";
    if($module eq 'Announcement'){
        $control_table = "cronControlPost_Announcement";
    }elsif($module eq 'Treatment Team Message'){
        $control_table = "cronControlPost_TreatmentTeamMessage";
    }

    # --------------------------------------------------
    # First step is to make sure that the two tables have the same amount of records
	my $insert_sql = "
		INSERT INTO $control_table (cronControlPostSerNum, publishFlag, lastPublished, lastUpdated, sessionId)
		SELECT PC.PostControlSerNum, PC.PublishFlag, PC.LastPublished, PC.LastUpdated, PC.SessionId
		FROM PostControl PC
		WHERE PC.PostType = '$module'
			AND PC.PostControlSerNum NOT IN (SELECT cronControlPostSerNum FROM $control_table);
    	";

    # prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

    # --------------------------------------------------
    # Second step is to sync the publish flag between the two tables
    # PostControl is the master and cronControlPost is the slave
	my $update_sql = "
        UPDATE PostControl PC, $control_table CCP
        SET CCP.publishFlag = PC.PublishFlag
        WHERE PC.PostControlSerNum = CCP.cronControlPostSerNum
            AND CCP.publishFlag <> PC.PublishFlag;
    	";

    # prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

    # --------------------------------------------------
    # Third step update the publish flag from 1 to 2
	my $update2_sql = "
        UPDATE $control_table
        SET publishFlag = 2
        WHERE publishFlag = 1;
    	";

    # prepare query
	my $query = $SQLDatabase->prepare($update2_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

}

# exit smoothly for module
1;
