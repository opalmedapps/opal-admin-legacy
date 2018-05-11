#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 10-Aug-2015 ++ File: Task.pm
#---------------------------------------------------------------------------------
# Perl module that creates a task class. This module calls a constructor to 
# create a task object that contains task information stored as object 
# variables.
#
# There exists various subroutines to set task information, get task information
# and compare task information between two task objects. 
# There exists various subroutines that use the Database.pm module to update the
# MySQL database and check if a task exists already in this database.

package Task; # Declare package name


use Exporter; # To export subroutines and variables
use Database; # Use our custom database module Database.pm
use Time::Piece; # To parse and convert date time
use POSIX;
use Storable qw(dclone); # for deep copies

use Patient; # Our patient module
use Alias; # Our Alias module
use Priority; # Our priority module
use Diagnosis; # Our diagnosis module

#---------------------------------------------------------------------------------
# Connect to our database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Task class 
#====================================================================================
sub new
{
	my $class = shift;
	my $task = {
		_ser			    => undef,
        _sourcedbser        => undef,
		_sourceuid		    => undef,
		_patientser		    => undef,
		_aliasexpressionser	=> undef,
		_duedatetime		=> undef,
        _diagnosisser       => undef,
        _priorityser        => undef,
		_creationdate		=> undef,
		_status			    => undef,
        _state              => undef,
		_completiondate		=> undef,  
		_cronlogser 		=> undef,  
	};

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $task, $class; 
	return $task;
}

#====================================================================================
# Subroutine to set the task serial
#====================================================================================
sub setTaskSer
{
	my ($task, $ser) = @_; # task object with provided serial in arguments
	$task->{_ser} = $ser; # set the ser
	return $task->{_ser};
}

#====================================================================================
# Subroutine to set the task source database serial
#====================================================================================
sub setTaskSourceDatabaseSer
{
	my ($task, $sourcedbser) = @_; # task object with provided serial in arguments
	$task->{_sourcedbser} = $sourcedbser; # set the ser
	return $task->{_sourcedbser};
}

#====================================================================================
# Subroutine to set the task patient serial
#====================================================================================
sub setTaskPatientSer
{
	my ($task, $patientser) = @_; # task object with provided serial in arguments
	$task->{_patientser} = $patientser; # set the ser
	return $task->{_patientser};
}

#====================================================================================
# Subroutine to set the task uid
#====================================================================================
sub setTaskSourceUID
{
	my ($task, $sourceuid) = @_; # task object with provided id in arguments
	$task->{_sourceuid} = $sourceuid; # set the id
	return $task->{_sourceuid};
}

#====================================================================================
# Subroutine to set the task alias expression serial
#====================================================================================
sub setTaskAliasExpressionSer
{
	my ($task, $aliasexpressionser) = @_; # task object with provided serial in arguments
	$task->{_aliasexpressionser} = $aliasexpressionser; # set the serial
	return $task->{_aliasexpressionser};
}

#====================================================================================
# Subroutine to set the task Due DateTime 
#====================================================================================
sub setTaskDueDateTime
{
	my ($task, $duedatetime) = @_; # task object with provided due datetime in arguments
	$task->{_duedatetime} = $duedatetime; # set the due datetime
	return $task->{_duedatetime};
}

#====================================================================================
# Subroutine to set the task creation date
#====================================================================================
sub setTaskCreationDate
{
	my ($task, $creationdate) = @_; # task object with provided creation date in arguments
	$task->{_creationdate} = $creationdate; # set the creation date
	return $task->{_creationdate};
}

#====================================================================================
# Subroutine to set the task status
#====================================================================================
sub setTaskStatus
{
	my ($task, $status) = @_; # task object with provided status in arguments
	$task->{_status} = $status; # set the status
	return $task->{_status};
}

#====================================================================================
# Subroutine to set the task state
#====================================================================================
sub setTaskState
{
	my ($task, $state) = @_; # task object with provided state in arguments
	$task->{_state} = $state; # set the state
	return $task->{_state};
}

#====================================================================================
# Subroutine to set the task completion date
#====================================================================================
sub setTaskCompletionDate
{
	my ($task, $completiondate) = @_; # task object with provided completion date in arguments
	$task->{_completiondate} = $completiondate; # set the date
	return $task->{_completiondate};
}

#====================================================================================
# Subroutine to set the task priority serial
#====================================================================================
sub setTaskPrioritySer
{
	my ($task, $priorityser) = @_; # task object with provided serial in arguments
	$task->{_priorityser} = $priorityser; # set the ser
	return $task->{_priorityser};
}

#====================================================================================
# Subroutine to set the task diagnosis serial
#====================================================================================
sub setTaskDiagnosisSer
{
	my ($task, $diagnosisser) = @_; # task object with provided serial in arguments
	$task->{_diagnosisser} = $diagnosisser; # set the ser
	return $task->{_diagnosisser};
}

#====================================================================================
# Subroutine to set the task cron log serial
#====================================================================================
sub setTaskCronLogSer
{
	my ($task, $cronlogser) = @_; # task object with provided serial in arguments
	$task->{_cronlogser} = $cronlogser; # set the ser
	return $task->{_cronlogser};
}

#====================================================================================
# Subroutine to get the Task ser
#====================================================================================
sub getTaskSer
{
	my ($task) = @_; # our task object
	return $task->{_ser};
}

#====================================================================================
# Subroutine to get the Task source database serial
#====================================================================================
sub getTaskSourceDatabaseSer
{
	my ($task) = @_; # our task object
	return $task->{_sourcedbser};
}

#====================================================================================
# Subroutine to get the Task patient serial
#====================================================================================
sub getTaskPatientSer
{
	my ($task) = @_; # our task object
	return $task->{_patientser};
}

#====================================================================================
# Subroutine to get the task uid
#====================================================================================
sub getTaskSourceUID
{
	my ($task) = @_; # our task object
	return $task->{_sourceuid};
}

#====================================================================================
# Subroutine to get the task alias expression serial 
#====================================================================================
sub getTaskAliasExpressionSer
{
	my ($task) = @_; # our task object
	return $task->{_aliasexpressionser};
}

#====================================================================================
# Subroutine to get the task Due DateTime 
#====================================================================================
sub getTaskDueDateTime
{
	my ($task) = @_; # our task object
	return $task->{_duedatetime};
}

#====================================================================================
# Subroutine to get the task creation date 
#====================================================================================
sub getTaskCreationDate
{
	my ($task) = @_; # our task object
	return $task->{_creationdate};
}

#====================================================================================
# Subroutine to get the task status
#====================================================================================
sub getTaskStatus
{
	my ($task) = @_; # our task object
	return $task->{_status};
}

#====================================================================================
# Subroutine to get the task state
#====================================================================================
sub getTaskState
{
	my ($task) = @_; # our task object
	return $task->{_state};
}

#====================================================================================
# Subroutine to get the task completion date 
#====================================================================================
sub getTaskCompletionDate
{
	my ($task) = @_; # our task object
	return $task->{_completiondate};
}

#====================================================================================
# Subroutine to get the Task priority serial
#====================================================================================
sub getTaskPrioritySer
{
	my ($task) = @_; # our task object
	return $task->{_priorityser};
}

#====================================================================================
# Subroutine to get the Task diagnosis ser
#====================================================================================
sub getTaskDiagnosisSer
{
	my ($task) = @_; # our task object
	return $task->{_diagnosisser};
}

#====================================================================================
# Subroutine to get the Task cron log ser
#====================================================================================
sub getTaskCronLogSer
{
	my ($task) = @_; # our task object
	return $task->{_cronlogser};
}

#======================================================================================
# Subroutine to get tasks from the source db
#======================================================================================
sub getTasksFromSourceDB
{
	my ($cronLogSer, @patientList) = @_; # a list of patients and cron log serial from args 

	my @taskList = (); # initialize a list for task objects

	# when we retrieve query results
	my ($sourceuid, $duedatetime, $priorityser, $diagnosisser); 
    my ($creationdate, $status, $state, $completiondate);
    my $lasttransfer;

    # retrieve all aliases that are marked for update
    my @aliasList = Alias::getAliasesMarkedForUpdate('Task');

	foreach my $Patient (@patientList) {

		my $patientSer		    = $Patient->getPatientSer(); # get patient serial
		my $patientSSN    		= $Patient->getPatientSSN(); # get patient ssn
		my $patientLastTransfer	= $Patient->getPatientLastTransfer(); # get last transfer

		my $formatted_PLU = Time::Piece->strptime($patientLastTransfer, "%Y-%m-%d %H:%M:%S");

        foreach my $Alias (@aliasList) {

            my $aliasSer            = $Alias->getAliasSer(); # get alias serial
            my @expressions         = $Alias->getAliasExpressions(); 
            my $sourceDBSer         = $Alias->getAliasSourceDatabaseSer();

            ######################################
		    # ARIA
		    ######################################
            if ($sourceDBSer eq 1) {

                my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);
                my $numOfExpressions = @expressions; 
                my $counter = 0;
                my $taskInfo_sql = "
					WITH vva AS (
						SELECT DISTINCT 
							Expression.Expression1,
							Expression.LookupValue
						FROM
							variansystem.dbo.vv_ActivityLng Expression
					)
					SELECT DISTINCT
						NonScheduledActivity.NonScheduledActivitySer,
						CONVERT(VARCHAR, NonScheduledActivity.DueDateTime, 120),
						CONVERT(VARCHAR, NonScheduledActivity.CreationDate, 120),
						NonScheduledActivity.NonScheduledActivityCode,
						NonScheduledActivity.ObjectStatus,
						CONVERT(VARCHAR, NonScheduledActivityMH.HstryDateTime, 120) HstryDateTime,
						vva.Expression1
					FROM  
						variansystem.dbo.Patient Patient,
						variansystem.dbo.ActivityInstance ActivityInstance,
						variansystem.dbo.Activity Activity,
						vva,
						variansystem.dbo.NonScheduledActivity NonScheduledActivity
                    LEFT JOIN variansystem.dbo.NonScheduledActivityMH NonScheduledActivityMH
                    ON  NonScheduledActivityMH.NonScheduledActivitySer = NonScheduledActivity.NonScheduledActivitySer
                    AND NonScheduledActivityMH.NonScheduledActivityRevCount = (
                        SELECT MIN(nsamh.NonScheduledActivityRevCount)
                        FROM variansystem.dbo.NonScheduledActivityMH nsamh
                        WHERE nsamh.NonScheduledActivitySer = NonScheduledActivity.NonScheduledActivitySer
                        AND nsamh.NonScheduledActivityCode = 'Completed'
                    )
					WHERE     
						NonScheduledActivity.ActivityInstanceSer 	= ActivityInstance.ActivityInstanceSer
					AND ActivityInstance.ActivitySer 			    = Activity.ActivitySer
					AND Activity.ActivityCode 				        = vva.LookupValue
					AND Patient.PatientSer 				            = NonScheduledActivity.PatientSer     
					AND	LEFT(LTRIM(Patient.SSN), 12)			            = '$patientSSN'
					AND (
				";

                foreach my $Expression (@expressions) {

                	my $expressionser = $Expression->{_ser};
                	my $expressionName = $Expression->{_name};
                	my $expressionLastTransfer = $Expression->{_lasttransfer};
                	my $formatted_ELU = Time::Piece->strptime($expressionLastTransfer, "%Y-%m-%d %H:%M:%S");

                	# compare last updates to find the earliest date 
		            # get the diff in seconds
		            my $date_diff = $formatted_PLU - $formatted_ELU;
		            if ($date_diff < 0) {
		                $lasttransfer = $patientLastTransfer;
		            } else {
		                $lasttransfer = $expressionLastTransfer;
		            }

	        		$taskInfo_sql .= "
						(REPLACE(vva.Expression1, '''', '')    			= '$expressionName'
						AND NonScheduledActivity.HstryDateTime		    > '$lasttransfer')
	         		";
	         		$counter++;
	        		# concat "UNION" until we've reached the last query
	        		if ($counter < $numOfExpressions) {
	        			$taskInfo_sql .= "OR";
	        		}
					# close bracket at end
					else {
						$taskInfo_sql .= ")";
					}
	        	}
                
                #print "$taskInfo_sql\n";

	        	# prepare query
    		    my $query = $sourceDatabase->prepare($taskInfo_sql)
	    		    or die "Could not prepare query: " . $sourceDatabase->errstr;

    		    # execute query
        		$query->execute()
	        		or die "Could not execute query: " . $query->errstr;

                my $data = $query->fetchall_arrayref();
        		foreach my $row (@$data) {
		
	        		my $task = new Task(); # new task object

    		    	$sourceuid	    = $row->[0];
    	    		$duedatetime	= $row->[1]; # convert date format
              	    $creationdate   = $row->[2];
                    $status         = $row->[3];
                    $state          = $row->[4];
                    $completiondate = $row->[5];
                    $expressionname = $row->[6];

                    $priorityser	= Priority::getClosestPriority($patientSer, $duedatetime);
    		    	$diagnosisser	= Diagnosis::getClosestDiagnosis($patientSer, $duedatetime);

					my $expressionser;
					foreach my $checkExpression (@expressions) {
						if ($checkExpression->{_name} eq $expressionname){ #match
							$expressionser = $checkExpression->{_ser};
							last; # break out of loop
						}
					}
    
        			$task->setTaskPatientSer($patientSer);
	        		$task->setTaskSourceUID($sourceuid); # assign id
                    $task->setTaskSourceDatabaseSer($sourceDBSer);
		        	$task->setTaskAliasExpressionSer($expressionser); # assign expression serial
			        $task->setTaskDueDateTime($duedatetime); # assign duedatetime
    				$task->setTaskPrioritySer($priorityser);
	    		    $task->setTaskDiagnosisSer($diagnosisser);
			        $task->setTaskCreationDate($creationdate); # assign creation date
    			    $task->setTaskStatus($status); # assign status
	    		    $task->setTaskCompletionDate($completiondate); # assign completion date
		    	    $task->setTaskState($state); # assign state
		    	    $task->setTaskCronLogSer($cronLogSer); # assign cron log serail

        			push(@taskList, $task);
		    		
                }
	    	 				
	    	 	$sourceDatabase->disconnect();
	    	}

	    	######################################
		    # MediVisit
		    ######################################
            if ($sourceDBSer eq 2) {

                my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);
                my $numOfExpressions = @expressions; 
                my $counter = 0;
                my $taskInfo_sql = "";

                foreach my $Expression (@expressions) {

                	my $expressionser = $Expression->{_ser};
                	my $expressionName = $Expression->{_name};
                	my $expressionLastTransfer = $Expression->{_lasttransfer};
                	my $formatted_ELU = Time::Piece->strptime($expressionLastTransfer, "%Y-%m-%d %H:%M:%S");

                	# compare last updates to find the earliest date 
		            # get the diff in seconds
		            my $date_diff = $formatted_PLU - $formatted_ELU;
		            if ($date_diff < 0) {
		                $lasttransfer = $patientLastTransfer;
		            } else {
		                $lasttransfer = $expressionLastTransfer;
		            }

		            $taskInfo_sql .= "SELECT 'QUERY_HERE' ";

		            $counter++;
	        		# concat "UNION" until we've reached the last query
	        		if ($counter < $numOfExpressions) {
	        			$taskInfo_sql .= "UNION";
	        		}
	        	}

	        	# prepare query
    		    my $query = $sourceDatabase->prepare($taskInfo_sql)
	    		    or die "Could not prepare query: " . $sourceDatabase->errstr;

    		    # execute query
        		$query->execute()
	        		or die "Could not execute query: " . $query->errstr;

                my $data = $query->fetchall_arrayref();
        		foreach my $row (@$data) {
		
	        		#my $task = new Task(); # uncomment for use

	        		# use setters to set appropriate task information from query

	        		#push(@taskList, $task); # uncomment for use
		    		
                }
	    	 				
	    	 	$sourceDatabase->disconnect();
	    	}

	    	######################################
		    # MOSAIQ
		    ######################################
            if ($sourceDBSer eq 3) {

                my $sourceDatabase = Database::connectToSourceDatabase($sourceDBSer);
                my $numOfExpressions = @expressions; 
                my $counter = 0;
                my $taskInfo_sql = "";

                foreach my $Expression (@expressions) {

                	my $expressionser = $Expression->{_ser};
                	my $expressionName = $Expression->{_name};
                	my $expressionLastTransfer = $Expression->{_lasttransfer};
                	my $formatted_ELU = Time::Piece->strptime($expressionLastTransfer, "%Y-%m-%d %H:%M:%S");

                	# compare last updates to find the earliest date 
		            # get the diff in seconds
		            my $date_diff = $formatted_PLU - $formatted_ELU;
		            if ($date_diff < 0) {
		                $lasttransfer = $patientLastTransfer;
		            } else {
		                $lasttransfer = $expressionLastTransfer;
		            }

		            $taskInfo_sql .= "SELECT 'QUERY_HERE' ";

		            $counter++;
	        		# concat "UNION" until we've reached the last query
	        		if ($counter < $numOfExpressions) {
	        			$taskInfo_sql .= "UNION";
	        		}
	        	}

	        	# prepare query
    		    my $query = $sourceDatabase->prepare($taskInfo_sql)
	    		    or die "Could not prepare query: " . $sourceDatabase->errstr;

    		    # execute query
        		$query->execute()
	        		or die "Could not execute query: " . $query->errstr;

                my $data = $query->fetchall_arrayref();
        		foreach my $row (@$data) {
		
	        		#my $task = new Task(); # uncomment for use

	        		# use setters to set appropriate task information from query

	        		#push(@taskList, $task); # uncomment for use
		    		
                }
	    	 				
	    	 	$sourceDatabase->disconnect();
	    	}

        }
	}

	return @taskList;
}

#======================================================================================
# Subroutine to check if a particular task exists in our MySQL db
#	@return: task object (if exists) .. NULL otherwise
#======================================================================================
sub inOurDatabase
{
	my ($task) = @_; # our task object

	my $sourceuid   = $task->getTaskSourceUID(); # retrieve Task uid
    my $sourcedbser = $task->getTaskSourceDatabaseSer();

	my $TaskSourceUIDInDB = 0; # false by default. Will be true if task exists
	my $ExistingTask = (); # data to be entered if task exists

	# Other task variable, if task exists
	my ($ser, $patientser, $aliasexpressionser, $duedatetime, $priorityser, $diagnosisser);
    my ($creationdate, $status, $state, $completiondate, $cronlogser);

	my $inDB_sql = "
		SELECT
			Task.TaskAriaSer,
			Task.AliasExpressionSerNum,
			Task.DueDateTime,
			Task.TaskSerNum,
			Task.PatientSerNum,
            Task.PrioritySerNum,
            Task.DiagnosisSerNum,
            Task.CreationDate,
            Task.Status,
            Task.State,
            Task.CompletionDate,
            Task.CronLogSerNum
		FROM
			Task
		WHERE
			Task.TaskAriaSer = $sourceuid
	";

	# prepare query
	my $query = $SQLDatabase->prepare($inDB_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {

		$TaskSourceUIDInDB	= $data[0];
		$aliasexpressionser	= $data[1];
		$duedatetime		= $data[2];
		$ser			    = $data[3];
		$patientser		    = $data[4];
        $priorityser        = $data[5];
        $diagnosisser       = $data[6];
        $creationdate       = $data[7];
        $status             = $data[8];
        $state              = $data[9];
        $completiondate     = $data[10];
        $cronlogser 		= $data[11];
	}

	if ($TaskSourceUIDInDB) {

		$ExistingTask = new Task(); # initialize task object

		$ExistingTask->setTaskSourceUID($TaskSourceUIDInDB); # set the task uid
        $ExistingTask->setTaskSourceDatabaseSer($sourcedbser);
		$ExistingTask->setTaskAliasExpressionSer($aliasexpressionser); # set the expression serial
		$ExistingTask->setTaskDueDateTime($duedatetime); # set the due datetime
		$ExistingTask->setTaskSer($ser);
		$ExistingTask->setTaskPatientSer($patientser);
        $ExistingTask->setTaskPrioritySer($priorityser);
        $ExistingTask->setTaskDiagnosisSer($diagnosisser);
		$ExistingTask->setTaskStatus($status); # set the status
		$ExistingTask->setTaskCreationDate($creationdate); # set the creation date
		$ExistingTask->setTaskCompletionDate($completiondate); # set the completion date
		$ExistingTask->setTaskState($state); # set the state
		$ExistingTask->setTaskCronLogSer($cronlogser); # set the cron log serial

		return $ExistingTask; # this is true (ie. task exists, return object)
	}
	
	else {return $ExistingTask;} # this is false (ie. task DNE, return empty)
}

#======================================================================================
# Subroutine to insert our task info in our database
#======================================================================================
sub insertTaskIntoOurDB
{
	my ($task) = @_; # our task object to insert

	my $patientser		    = $task->getTaskPatientSer();
	my $sourceuid           = $task->getTaskSourceUID();
    my $sourcedbser         = $task->getTaskSourceDatabaseSer();
	my $aliasexpressionser	= $task->getTaskAliasExpressionSer();
	my $duedatetime		    = $task->getTaskDueDateTime();
	my $diagnosisser		= $task->getTaskDiagnosisSer();
	my $priorityser		    = $task->getTaskPrioritySer();
	my $creationdate	    = $task->getTaskCreationDate();
	my $status		        = $task->getTaskStatus();
	my $completiondate	    = $task->getTaskCompletionDate();
	my $state		        = $task->getTaskState();
	my $cronlogser		    = $task->getTaskCronLogSer();

	my $insert_sql = "
		INSERT INTO 
			Task (
				PatientSerNum,
				CronLogSerNum,
                SourceDatabaseSerNum,
				TaskAriaSer,
				AliasExpressionSerNum,
				DueDateTime,
                CreationDate,
                Status,
                State,
                CompletionDate,
                PrioritySerNum,
                DiagnosisSerNum,
                DateAdded
			)
		VALUES (
			'$patientser',
			'$cronlogser',
            '$sourcedbser',
			'$sourceuid',
			'$aliasexpressionser',
			'$duedatetime',
            '$creationdate',
            '$status',
            '$state',
            '$completiondate',
            '$priorityser',
            '$diagnosisser',
            NOW()
		)
	";
	
	# prepare query
	my $query = $SQLDatabase->prepare($insert_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;

	# Retrieve the TaskSer
	my $ser = $SQLDatabase->last_insert_id(undef, undef, undef, undef);

	# Set the Serial in our task object
	$task->setTaskSer($ser);

	return $task;
}

#======================================================================================
# Subroutine to update our database with the task's updated info
#======================================================================================
sub updateDatabase
{
	my ($task) = @_; # our task object to update

	my $sourceuid	        = $task->getTaskSourceUID();
    my $sourcedbser         = $task->getTaskSourceDatabaseSer();
	my $aliasexpressionser	= $task->getTaskAliasExpressionSer();
	my $duedatetime		    = $task->getTaskDueDateTime();
	my $diagnosisser		= $task->getTaskDiagnosisSer();
	my $priorityser		    = $task->getTaskPrioritySer();
	my $creationdate	    = $task->getTaskCreationDate();
	my $status		        = $task->getTaskStatus();
	my $completiondate	    = $task->getTaskCompletionDate();
	my $state		        = $task->getTaskState();
	my $cronlogser		    = $task->getTaskCronLogSer();

	my $update_sql = "
		
		UPDATE
			Task
		SET
			AliasExpressionSerNum	= '$aliasexpressionser',
			DueDateTime		        = '$duedatetime',
 			Status			        = '$status',
            State                   = '$state',
			CreationDate		    = '$creationdate',
			CompletionDate		    = '$completiondate',           
            PrioritySerNum          = '$priorityser',
            DiagnosisSerNum         = '$diagnosisser',
            CronLogSerNum 			= '$cronlogser'
		WHERE
			TaskAriaSer		        = '$sourceuid'
        AND SourceDatabaseSerNum    = '$sourcedbser'
	";

	# prepare query
	my $query = $SQLDatabase->prepare($update_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
}

#======================================================================================
# Subroutine to compare two task objects. If different, use setter functions
# to update task object.
#======================================================================================
sub compareWith
{
	my ($SuspectTask, $OriginalTask) = @_; # our two task objects from arguments
	my $UpdatedTask = dclone($OriginalTask); 

	# retrieve parameters
	# Suspect Task...
	my $Sduedatetime	    = $SuspectTask->getTaskDueDateTime();
	my $Saliasexpressionser	= $SuspectTask->getTaskAliasExpressionSer();
    my $Spriorityser        = $SuspectTask->getTaskPrioritySer();
    my $Sdiagnosisser       = $SuspectTask->getTaskDiagnosisSer();
	my $Screationdate	    = $SuspectTask->getTaskCreationDate();
	my $Sstatus		        = $SuspectTask->getTaskStatus();
	my $Scompletiondate	    = $SuspectTask->getTaskCompletionDate();
	my $Sstate		        = $SuspectTask->getTaskState();
	my $Scronlogser		    = $SuspectTask->getTaskCronLogSer();

	# Original Task...
	my $Oduedatetime	    = $OriginalTask->getTaskDueDateTime();
	my $Oaliasexpressionser	= $OriginalTask->getTaskAliasExpressionSer();
    my $Opriorityser        = $OriginalTask->getTaskPrioritySer();
    my $Odiagnosisser       = $OriginalTask->getTaskDiagnosisSer();
	my $Ocreationdate	    = $OriginalTask->getTaskCreationDate();
	my $Ostatus		        = $OriginalTask->getTaskStatus();
	my $Ocompletiondate	    = $OriginalTask->getTaskCompletionDate();
	my $Ostate	    	    = $OriginalTask->getTaskState();
	my $Ocronlogser	    	= $OriginalTask->getTaskCronLogSer();

	# go through each parameter
	if ($Sduedatetime ne $Oduedatetime) {

		print "Task Due Date has changed from '$Oduedatetime' to '$Sduedatetime'\n";
		my $updatedDueDateTime = $UpdatedTask->setTaskDueDateTime($Sduedatetime); # update 
		print "Will update database entry to '$updatedDueDateTime'.\n";
	}
	if ($Saliasexpressionser ne $Oaliasexpressionser) {

		print "Task Alias Expression Serial has changed from '$Oaliasexpressionser' to '$Saliasexpressionser'\n";
		my $updatedAESer = $UpdatedTask->setTaskAliasExpressionSer($Saliasexpressionser); # update 
		print "Will update database entry to '$updatedAESer'.\n";
	}
	if ($Spriorityser ne $Opriorityser) {

		print "Task Priority serial has changed from '$Opriorityser' to '$Spriorityser'\n";
		my $updatedPrioritySer = $UpdatedTask->setTaskPrioritySer($Spriorityser); # update 
		print "Will update database entry to '$updatedPrioritySer'.\n";
	}
	if ($Sdiagnosisser ne $Odiagnosisser) {

		print "Task Diagnosis serial has changed from '$Odiagnosisser' to '$Sdiagnosisser'\n";
		my $updatedDiagnosisSer = $UpdatedTask->setTaskDiagnosisSer($Sdiagnosisser); # update 
		print "Will update database entry to '$updatedDiagnosisSer'.\n";
	}
	if ($Screationdate ne $Ocreationdate) {

		print "Task Creation Date has changed from '$Ocreationdate' to '$Screationdate'\n";
		my $updatedCreationDate = $UpdatedTask->setTaskCreationDate($Screationdate); # update 
		print "Will update database entry to '$updatedCreationDate'.\n";
	}
	if ($Sstatus ne $Ostatus) {

		print "Task Status has changed from '$Ostatus' to '$Sstatus'\n";
		my $updatedStatus = $UpdatedTask->setTaskStatus($Sstatus); # update 
		print "Will update database entry to '$updatedStatus'.\n";
	}
    if ($Sstate ne $Ostate) {

		print "Task State has changed from '$Ostate' to '$Sstate'\n";
		my $updatedState = $UpdatedTask->setTaskState($Sstate); # update 
		print "Will update database entry to '$updatedState'.\n";
	}
	if ($Scompletiondate ne $Ocompletiondate) {

		print "Task Completion Date has changed from '$Ocompletiondate' to '$Scompletiondate'\n";
		my $updatedCompletionDate = $UpdatedTask->setTaskCompletionDate($Scompletiondate); # update 
		print "Will update database entry to '$updatedCompletionDate'.\n";
	}
	if ($Scronlogser ne $Ocronlogser) {

		print "Task Cron Log serial has changed from '$Ocronlogser' to '$Scronlogser'\n";
		my $updatedCronLogSer = $UpdatedTask->setTaskCronLogSer($Scronlogser); # update 
		print "Will update database entry to '$updatedCronLogSer'.\n";
	}

	return $UpdatedTask;
}

# To exit/return always true (for the module itself)
1;	




