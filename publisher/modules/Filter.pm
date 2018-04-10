#!/usr/bin/perl
#---------------------------------------------------------------------------------
# A.Joseph 04-May-2016 ++ File: Filter.pm
#---------------------------------------------------------------------------------
# Perl module that creates a filter class. This module calls a constructor to 
# create a filter object that contains filter information stored as parameters
#
# There exists various subroutines to set / get filter information

package Filter; # Declare package name

use Database; # Our custom module Database.pm

#---------------------------------------------------------------------------------
# Connect to the database
#---------------------------------------------------------------------------------
my $SQLDatabase		= $Database::targetDatabase;

#====================================================================================
# Constructor for our Filters class 
#====================================================================================
sub new
{
    my $class = shift;
    my $filter = {
        _sex                => undef,
        _age                => undef,
		_patients 		    => undef,
        _appointments       => undef,
        _diagnoses          => undef,
        _doctors            => undef,
        _resources          => undef,
        _appointmentStatuses=> undef,
        _checkin            => undef,
        _frequencyflag      => undef,
    };

	# bless associates an object with a class so Perl knows which package to search for
	# when a method is invoked on this object
	bless $filter, $class; 
	return $filter;
}

#======================================================================================
# Subroutine to set the sex filter
#======================================================================================
sub setSexFilter
{
	my ($filter, $sex) = @_; # filter object with provided sex in arguments
	$filter->{_sex} = $sex; # set the sex
    return $filter->{_sex};
}

#======================================================================================
# Subroutine to set the age filter
#======================================================================================
sub setAgeFilter
{
	my ($filter, $age) = @_; # filter object with provided age in arguments
	$filter->{_age} = $age; # set the age
    return $filter->{_age};
}

#======================================================================================
# Subroutine to set the patient filters
#======================================================================================
sub setPatientFilters
{
	my ($filter, @patients) = @_; # filter object with provided patients in arguments
	@{$filter->{_patients}} = @patients; # set the patients
	return @{$filter->{_patients}};
}

#======================================================================================
# Subroutine to set the appointment filters
#======================================================================================
sub setAppointmentFilters
{
	my ($filter, @appointments) = @_; # filter object with provided appointments in arguments
	@{$filter->{_appointments}} = @appointments; # set the appointments
	return @{$filter->{_appointments}};
}

#======================================================================================
# Subroutine to set the diagnosis filters
#======================================================================================
sub setDiagnosisFilters
{
	my ($filter, @diagnoses) = @_; # filter object with provided diagnoses in arguments
	@{$filter->{_diagnoses}} = @diagnoses; # set the diagnoses
	return @{$filter->{_diagnoses}};
}

#======================================================================================
# Subroutine to set the doctor filters
#======================================================================================
sub setDoctorFilters
{
	my ($filter, @doctors) = @_; # filter object with provided doctors in arguments
	@{$filter->{_doctors}} = @doctors; # set the doctors
	return @{$filter->{_doctors}};
}

#======================================================================================
# Subroutine to set the resource filters
#======================================================================================
sub setResourceFilters
{
	my ($filter, @resources) = @_; # filter object with provided resources in arguments
	@{$filter->{_resources}} = @resources; # set the resources
	return @{$filter->{_resources}};
}

#======================================================================================
# Subroutine to set the appointment status filters
#======================================================================================
sub setAppointmentStatusFilters
{
    my ($filter, @appointmentStatuses) = @_; # filter object with provided statuses in arguments
    @{$filter->{_appointmentStatuses}} = @appointmentStatuses; # set the statuses
    return @{$filter->{_appointmentStatuses}};
}

#======================================================================================
# Subroutine to set the checkin filters
#======================================================================================
sub setCheckinFilters
{
    my ($filter, @checkin) = @_; # filter object with provided flag in arguments
    @{$filter->{_checkin}} = @checkin; # set the flag
    return @{$filter->{_checkin}};
}

#======================================================================================
# Subroutine to set the frequency filter
#======================================================================================
sub setFrequencyFilter
{
    my ($filter, $frequencyflag) = @_; # filter object with provided flag in arguments
    $filter->{_frequencyflag} = $frequencyflag; # set the flag
    return $filter->{_frequencyflag};
}

#======================================================================================
# Subroutine to get the sex filter
#======================================================================================
sub getSexFilter
{
	my ($filter) = @_; # our filter object
	return $filter->{_sex};
}

#======================================================================================
# Subroutine to get the age filter
#======================================================================================
sub getAgeFilter
{
	my ($filter) = @_; # our filter object
	return $filter->{_age};
}

#======================================================================================
# Subroutine to get the patient filters
#======================================================================================
sub getPatientFilters
{
	my ($filter) = @_; # our filter object
	return @{$filter->{_patients}};
}

#======================================================================================
# Subroutine to get the appointment filters
#======================================================================================
sub getAppointmentFilters
{
	my ($filter) = @_; # our filter object
	return @{$filter->{_appointments}};
}

#======================================================================================
# Subroutine to get the diagnosis filters
#======================================================================================
sub getDiagnosisFilters
{
	my ($filter) = @_; # our filter object
	return @{$filter->{_diagnoses}};
}

#======================================================================================
# Subroutine to get the doctor filters
#======================================================================================
sub getDoctorFilters
{
	my ($filter) = @_; # our filter object
	return @{$filter->{_doctors}};
}

#======================================================================================
# Subroutine to get the resource filters
#======================================================================================
sub getResourceFilters
{
	my ($filter) = @_; # our filter object
	return @{$filter->{_resources}};
}

#======================================================================================
# Subroutine to get the appointment status filters
#======================================================================================
sub getAppointmentStatusFilters
{
    my ($filter) = @_; # our filter object
    return @{$filter->{_appointmentStatuses}};
}

#======================================================================================
# Subroutine to get the checkin filters
#======================================================================================
sub getCheckinFilters
{
    my ($filter) = @_; # our filter object
    return @{$filter->{_checkin}};
}

#======================================================================================
# Subroutine to get the frequency filter flag
#======================================================================================
sub getFrequencyFilter
{
    my ($filter) = @_; # our filter object
    return $filter->{_frequencyflag};
}

#======================================================================================
# Subroutine to get all filters given a control serial number and table name
#======================================================================================
sub getAllFiltersFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my $sexFilter                   = getSexFilterFromOurDB($controlSer, $controlTable);
    my $ageFilter                   = getAgeFilterFromOurDB($controlSer, $controlTable);
	my @patientFilters 		        = getPatientFiltersFromOurDB($controlSer, $controlTable);
    my @appointmentFilters          = getAppointmentFiltersFromOurDB($controlSer, $controlTable);
    my @diagnosisFilters            = getDiagnosisFiltersFromOurDB($controlSer, $controlTable);
    my @doctorFilters               = getDoctorFiltersFromOurDB($controlSer, $controlTable);
    my @resourceFilters             = getResourceFiltersFromOurDB($controlSer, $controlTable);
    my @appointmentStatusFilters    = getAppointmentStatusFiltersFromOurDB($controlSer, $controlTable);
    my @checkinFilter               = getCheckinFiltersFromOurDB($controlSer, $controlTable);
    my $frequencyFilter             = getFrequencyFilterFromOurDB($controlSer, $controlTable);

    my $Filter = new Filter(); # initialize object

    $Filter->setSexFilter($sexFilter);
    $Filter->setAgeFilter($ageFilter);
	$Filter->setPatientFilters(@patientFilters);
    $Filter->setAppointmentFilters(@appointmentFilters);
    $Filter->setDiagnosisFilters(@diagnosisFilters);
    $Filter->setDoctorFilters(@doctorFilters);
    $Filter->setResourceFilters(@resourceFilters);
    $Filter->setAppointmentStatusFilters(@appointmentStatusFilters);
    $Filter->setCheckinFilters(@checkinFilter);
    $Filter->setFrequencyFilter($frequencyFilter);

    return $Filter;

}

#======================================================================================
# Subroutine to get sex filter from DB given a control serial number and table name
#======================================================================================
sub getSexFilterFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my $sexFilter = undef; # initialize
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'Sex'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {
        $sexFilter = $data[0];
    }

    return $sexFilter;
}

#======================================================================================
# Subroutine to get age filter from DB given a control serial number and table name
#======================================================================================
sub getAgeFilterFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my $ageFilter = undef; # initialize
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'Age'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {
        my @ageSplit = split(',', $data[0]);
        $ageFilter = {
            _min    => @ageSplit[0],
            _max    => @ageSplit[1]
        };
            
    }

    return $ageFilter;
}

#======================================================================================
# Subroutine to get patient filters from DB given a control serial number and table name
#======================================================================================
sub getPatientFiltersFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my @patientFilters = (); # initialize list
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'Patient'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {
        push(@patientFilters, $data[0]);
    }

    return @patientFilters;
}

#======================================================================================
# Subroutine to get appointment filters from DB given a control serial number and table name
#======================================================================================
sub getAppointmentFiltersFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my @appointmentFilters = (); # initialize list
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'Appointment'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {
        push(@appointmentFilters, $data[0]);
    }

    return @appointmentFilters;
}

#======================================================================================
# Subroutine to get diagnosis filters from DB given a control serial number and table name
#======================================================================================
sub getDiagnosisFiltersFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my @diagnosisFilters = (); # initialize list
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'Diagnosis'
    ";

    # prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {
        push(@diagnosisFilters, $data[0]);
    }

    return @diagnosisFilters;
}

#======================================================================================
# Subroutine to get doctor filters from DB given a control serial number and table name
#======================================================================================
sub getDoctorFiltersFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my @doctorFilters = (); # initialize list
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'Doctor'
    ";

    	# prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {
        push(@doctorFilters, $data[0]);
    }

    return @doctorFilters;
}

#======================================================================================
# Subroutine to get resource filters from DB given a control serial number and table name
#======================================================================================
sub getResourceFiltersFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my @resourceFilters = (); # initialize list
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'Machine'
    ";

    	# prepare query
	my $query = $SQLDatabase->prepare($select_sql)
		or die "Could not prepare query: " . $SQLDatabase->errstr;

	# execute query
	$query->execute()
		or die "Could not execute query: " . $query->errstr;
	
	while (my @data = $query->fetchrow_array()) {
        push(@resourceFilters, $data[0]);
    }

    return @resourceFilters;
}

#======================================================================================
# Subroutine to get appointment status filters from DB given a control serial number and table name
#======================================================================================
sub getAppointmentStatusFiltersFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my @appointmentStatusFilters = (); # initialize list
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'AppointmentStatus'
    ";

    # prepare query
    my $query = $SQLDatabase->prepare($select_sql)
        or die "Could not prepare query: " . $SQLDatabase->errstr;

    # execute query
    $query->execute()
        or die "Could not execute query: " . $query->errstr;
    
    while (my @data = $query->fetchrow_array()) {
        push(@appointmentStatusFilters, $data[0]);
    }

    return @appointmentStatusFilters;
}
#======================================================================================
# Subroutine to get checkin filters from DB given a control serial number and table name
#======================================================================================
sub getCheckinFiltersFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my @checkinFilters = (); # initialize list
    my $select_sql = "
        SELECT DISTINCT
            Filters.FilterId
        FROM
            Filters
        WHERE
            Filters.ControlTable         = '$controlTable'
        AND Filters.ControlTableSerNum   = '$controlSer'
        AND Filters.FilterType           = 'CheckedInFlag'
    ";

    # prepare query
    my $query = $SQLDatabase->prepare($select_sql)
        or die "Could not prepare query: " . $SQLDatabase->errstr;

    # execute query
    $query->execute()
        or die "Could not execute query: " . $query->errstr;
    
    while (my @data = $query->fetchrow_array()) {
        push(@checkinFilters, $data[0]);
    }

    return @checkinFilters;
}

#======================================================================================
# Subroutine to get frequency filter from DB given a control serial number and table name
#======================================================================================
sub getFrequencyFilterFromOurDB
{
    my ($controlSer, $controlTable) = @_; # args

    my $frequencyFlag = undef; # initialize
    my $select_sql = "
        SELECT DISTINCT
            fe.MetaKey 
        FROM
            FrequencyEvents fe
        WHERE
            fe.ControlTable         = '$controlTable'
        AND fe.ControlTableSerNum   = '$controlSer'
        AND fe.MetaKey              = 'repeat_start'
    ";

    # prepare query
    my $query = $SQLDatabase->prepare($select_sql)
        or die "Could not prepare query: " . $SQLDatabase->errstr;

    # execute query
    $query->execute()
        or die "Could not execute query: " . $query->errstr;
    
    while (my @data = $query->fetchrow_array()) {
        $frequencyFlag = 1;
    }

    return $frequencyFlag;
}


# exit smoothly for module
1;
