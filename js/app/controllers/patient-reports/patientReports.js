angular.module('opalAdmin.controllers.patientReports', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('patientReports', function($scope, Session, ErrorHandler, MODULE, $uibModal){

    $scope.foundPatient = false; //only show the report once patient is found/selected
    $scope.selectPatient = false; //only show if multiple patients are found from search and user must choose one
    $scope.featureList = { // Which features will be added into the report
        diagnosis: false,
        appointments: false,
        questionnaires: false,
        education: false,
        testresults: false,
        pattestresults: false,
        notifications: false,
        treatplan: false,
        clinicalnotes: false,
        treatingteam: false,
        general: false,
    };

    // Initialize varibales for patient search parameters, patient identifiers, and patient report segments
    $scope.searchName = ""; //search parameters
    $scope.searchMRN = "";
    $scope.searchRAMQ = "";

    $scope.searchResult = "";

    $scope.psnum = ""; //the selected patient identifiers for our report
    $scope.pname = "";
    $scope.pfname = "";
    $scope.psex = "";
    $scope.pemail = "";
    $scope.pramq = "";
    $scope.pmrn = "";
    $scope.planguage = "";


    $scope.diagReport = ""; //empty report segments 
    $scope.qstReport = "";
    $scope.apptReport = "";
    $scope.educReport = "";
    $scope.testReport = "";
    $scope.pattestReport = "";
    $scope.noteReport = "";
    $scope.clinnoteReport = "";
    $scope.txteamReport = "";
    $scope.generalReport = "";
    $scope.txplanReport = "";
    
    // Initialize gridOptions objects for each report segment, set to track corresponding report segment data
    $scope.diagGridOptions = {
        data: 'diagReport',
        columnDefs: [
            { field: 'description', displayName: 'Diagnosis Description', width: '40%', enableColumnMenu: false },
            { field: 'creationdate', displayName: 'Diagnosis Date', width: '40%', enableColumnMenu: false },
            { field: 'sernum', displayName: 'Opal Diagnosis Serial', width: '20%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true,    
    };

    $scope.qstGridOptions = {
        data: 'qstReport',
        columnDefs: [
            { field: 'name', displayName: 'Questionnaire Name', width: '40%', enableColumnMenu: false },
            { field: 'dateadded', displayName: 'Date Sent', width: '40%', enableColumnMenu: false },
            { field: 'datecompleted', displayName: 'Date Completed', width: '20%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true,
    };

    $scope.apptGridOptions = {
        data: 'apptReport',
        columnDefs: [
            { field: 'starttime', displayName: 'Scheduled Appointment Time', width: '20%', enableColumnMenu: false },
            { field: 'dateadded', displayName: 'Added to Opal', width: '20%', enableColumnMenu: false },
            { field: 'status', displayName: 'Status', width: '10%', enableColumnMenu: false },
            { field: 'aliasname', displayName: 'Appointment Name', width: '20%', enableColumnMenu: false },
            { field: 'aliastype', displayName: 'Appointment Type', width: '10%', enableColumnMenu: false },
            { field: 'resourcename', displayName: 'Resource Name', width: '20%', enableColumnMenu: false },

        ],
        enableFiltering: true,
        enableColumnResizing: true,    
    };

    $scope.educGridOptions = {
        data: 'educReport',
        columnDefs: [
            { field: 'name', displayName: 'Material Name', width: '40%', enableColumnMenu: false },
            { field: 'materialtype', displayName: 'Material Type', width: '20%', enableColumnMenu: false },
            { field: 'dateadded', displayName: 'Date Sent', width: '30%', enableColumnMenu: false },
            { field: 'readstatus', displayName: 'Read Status', width: '10%', enableColumnMenu: false },

        ],
        enableFiltering: true,
        enableColumnResizing: true,
    };

    $scope.testGridOptions = {
        data: 'testReport',
        columnDefs: [
            { field: 'componentname', displayName: 'Test Name', width: '15%', enableColumnMenu: false },
            { field: 'unitdescription', displayName: 'Test Unit', width: '10%', enableColumnMenu: false },
            { field: 'testdate', displayName: 'Test Date', width: '15%', enableColumnMenu: false },
            { field: 'dateadded', displayName: 'Opal Date Added', width: '15%', enableColumnMenu: false },
            { field: 'minnorm', displayName: 'Min Normal Value', width: '10%', enableColumnMenu: false },
            { field: 'testvalue', displayName: 'Test Result', width: '10%', enableColumnMenu: false },
            { field: 'maxnorm', displayName: 'Max Normal Value', width: '10%', enableColumnMenu: false },
            { field: 'abnormalflag', displayName: 'Abnormal Flag', width: '10%', enableColumnMenu: false },
            { field: 'readstatus', displayName: 'Read Status', width: '5%', enableColumnMenu: false },

        ],
        enableFiltering: true,
        enableColumnResizing: true, 
    };
    $scope.pattestGridOptions = {
        data: 'pattestReport',
        columnDefs: [
            { field: 'groupname', displayName: 'Group Name', width: '10%', enableColumnMenu: false },
            { field: 'readstatus', displayName: 'Read Status', width: '5%', enableColumnMenu: false },
            { field: 'testname', displayName: 'Test Name', width: '10%', enableColumnMenu: false },
            { field: 'description', displayName: 'Description', width: '12%', enableColumnMenu: false },
            { field: 'abnormalflag', displayName: 'Flags', width: '5%', enableColumnMenu: false },
            { field: 'normalrange', displayName: 'Normal Range', width: '10%', enableColumnMenu: false },
            { field: 'testvalue', displayName: 'Test Result', width: '12%', enableColumnMenu: false },
            { field: 'datecollected', displayName: 'Test Time', width: '12%', enableColumnMenu: false },
            { field: 'resultdate', displayName: 'Result Time', width: '12%', enableColumnMenu: false },
            { field: 'dateadded', displayName: 'Added to Opal', width: '12%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true,
    };

    $scope.noteGridOptions = {
        data: 'noteReport',
        columnDefs: [
            { field: 'name', displayName: 'Note Type', width: '15%', enableColumnMenu: false },
            { field: 'tablerowtitle', displayName: 'Note Name', width: '30%', enableColumnMenu: false },
            { field: 'dateadded', displayName: 'Date Sent', width: '20%', enableColumnMenu: false },
            { field: 'lastupdated', displayName: 'Date Read', width: '20%', enableColumnMenu: false },

        ],
        enableFiltering: true,
        enableColumnResizing: true, 
    };

    $scope.clinnoteGridOptions = {
        data: 'clinnoteReport',
        columnDefs: [
            { field: 'aliasexpressionname', displayName: 'Note Type', width: '20%', enableColumnMenu: false },
            { field: 'originalname', displayName: 'Original Name', width: '20%', enableColumnMenu: false },
            { field: 'finalname', displayName: 'Final Name', width: '20%', enableColumnMenu: false },
            { field: 'created', displayName: 'Date Created', width: '20%', enableColumnMenu: false },
            { field: 'approved', displayName: 'Date Approved', width: '20%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true, 
    };

    $scope.txteamGridOptions = {
        data: 'txteamReport',
        columnDefs: [
            { field: 'name', displayName: 'Title', width: '30%', enableColumnMenu: false },
            { field: 'body', displayName: 'Body', width: '45%', enableColumnMenu: false },
            { field: 'dateadded', displayName: 'Date Sent', width: '15%', enableColumnMenu: false },
            { field: 'readstatus', displayName: 'Read Status', width: '10%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true, 
    };

    $scope.generalGridOptions = {
        data: 'generalReport',
        columnDefs: [
            { field: 'name', displayName: 'Title', width: '30%', enableColumnMenu: false },
            { field: 'body', displayName: 'Body', width: '45%', enableColumnMenu: false },
            { field: 'dateadded', displayName: 'Date Sent', width: '15%', enableColumnMenu: false },
            { field: 'readstatus', displayName: 'Read Status', width: '10%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true, 
    };

    $scope.txplanGridOptions = {
        data: 'txplanReport',
        columnDefs: [
            { field: 'diagnosisdescription', displayName: 'Diagnosis Description', width: '25%', enableColumnMenu: false },
            { field: 'aliastype', displayName: 'Type', width: '10%', enableColumnMenu: false },
            { field: 'prioritycode', displayName: 'Priority', width: '15%', enableColumnMenu: false },
            { field: 'aliasexpressiondescription', displayName: 'Expression Desc.', width: '10%', enableColumnMenu: false },
            { field: 'aliasname', displayName: 'Alias Name', width: '10%', enableColumnMenu: false },
            { field: 'aliasdescription', displayName: 'Alias Desc.', width: '10%', enableColumnMenu: false },
            { field: 'taskstatus', displayName: 'Task Status', width: '5%', enableColumnMenu: false },
            { field: 'taskstate', displayName: 'Task State', width: '5%', enableColumnMenu: false },
            { field: 'taskdue', displayName: 'Task Due', width: '10%', enableColumnMenu: false },
            { field: 'taskcompletiondate', displayName: 'Task Complete', width: '10%', enableColumnMenu: false },

        ],
        enableFiltering: true,
        enableColumnResizing: true, 
    };

    $scope.selectedName = "";

    /**
     *  Main search function for finding desired patient
     *  -- Uses scope variables instead of explicit parameters
     *  -- All ajax calls get rerouted through the main .htaccess Rewrite rules
     */
    $scope.findPat = function() {
        if ($scope.searchName == "" && $scope.searchMRN == "" && $scope.searchRAMQ == "") {
            $scope.foundPatient = false;
        }else if ($scope.searchName){ //find by name
            $.ajax({
                type: "POST",
                url: "patient-reports/find/patient-name",
                data: {pname: $scope.searchName},
                success: function(response){
                    displayName(JSON.parse(response));
                },
                error: function(err){
                    ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.DB_ERROR'));
                }
            });
        }else if ($scope.searchMRN){ //find by MRN
            $.ajax({
                type: "POST",
                url: "patient-reports/find/patient-mrn",
                data: {pmrn: $scope.searchMRN},
                success: function(response){
                    displayName(JSON.parse(response));
                },
                error: function(err){
                    ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.DB_ERROR'));
                }
            });
        }else if ($scope.searchRAMQ){ //find my RAMQ
            $.ajax({
                type: "POST",
                url: "patient-reports/find/patient-ramq",
                data: {pramq: $scope.searchRAMQ},
                success: function(response){
                    displayName(JSON.parse(response));
                },
                error: function(err){
                    ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.DB_ERROR'));
                }
            });
        }else{ //some other error?
            ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.UNKNOWN'));
        }
    }

    /**
     *  Process results of ajax patient search
     * 
     *  @param result: patient(s) info
     *  @return 
     */
    function displayName(result){
        $scope.searchResult = result;
        if(!$scope.searchResult){ //no match found for input parameter
            $scope.foundPatient = false;
            ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.SEARCH_FAIL'));
        }else if ($scope.searchResult.length > 1){ //found multiple patients matching search
            $scope.patOptions = [];
            var tmp = "";
            //load each result into patOptions array for selection
            for (var i = 0; i < $scope.searchResult.length; i++){
                tmp = i + " , " + $scope.searchResult[i].pname + " , " + $scope.searchResult[i].plname;
                $scope.patOptions.push(tmp);
                tmp = "";
            }
            $scope.selectPatient = true; //display dialog to select patient, result stored in scope.selectedName and displayPatient called 
        } else { //exactly one match
            $scope.foundPatient = true; //display patient table
            $scope.resetReportValues(); //set all report options to true by default

            // set selected patient identifiers
            if($scope.searchResult[0].pname){
                $scope.pname = $scope.searchResult[0].pname.replace(/["']/g, "");
            }
            if($scope.searchResult[0].plname){
                $scope.plname = $scope.searchResult[0].plname.replace(/["']/g, "");
            }
            if($scope.searchResult[0].psnum){
                $scope.psnum = $scope.searchResult[0].psnum.replace(/["']/g, "");
            }
            if($scope.searchResult[0].pid){
                $scope.pid = $scope.searchResult[0].pid.replace(/["']/g, "");
            }
            if($scope.searchResult[0].pramq){
                $scope.pramq = $scope.searchResult[0].pramq.replace(/["']/g, "");
            }
            if($scope.searchResult[0].psex){
                $scope.psex = $scope.searchResult[0].psex.replace(/["' ]/g, "");
            }
            if($scope.searchResult[0].plang){
                $scope.plang = $scope.searchResult[0].plang.replace(/["']/g, "");
            }
            if($scope.searchResult[0].pemail){
                $scope.pemail = $scope.searchResult[0].pemail.replace(/["']/g, "");
            }
            
        }
    }

    // display the selected patient (this function is called by the template after selecting a patient from the list of options)
    $scope.displaySelection = function() {
        $scope.foundPatient = true; //display patient table
        $scope.resetReportValues(); //set report features all true
        var idx = $scope.selectedName.split(" , ")[0]; // index of selected patient
        //Set the chosen patient identifier variables
        if($scope.searchResult[idx].pname){
            $scope.pname = $scope.searchResult[idx].pname.replace(/["']/g, "");
        }
        if($scope.searchResult[idx].plname){
            $scope.plname = $scope.searchResult[idx].plname.replace(/["']/g, "");
        }
        if($scope.searchResult[idx].psnum){
            $scope.psnum = $scope.searchResult[idx].psnum.replace(/["']/g, "");
        }
        if($scope.searchResult[idx].pid){
            $scope.pid = $scope.searchResult[idx].pid.replace(/["']/g, "");
        }
        if($scope.searchResult[idx].pramq){
            $scope.pramq = $scope.searchResult[idx].pramq.replace(/["']/g, "");
        }
        if($scope.searchResult[idx].psex){
            $scope.psex = $scope.searchResult[idx].psex.replace(/["' ]/g, "");
        }
        if($scope.searchResult[idx].pemail){
            $scope.pemail = $scope.searchResult[idx].pemail.replace(/["']/g, "");
        }
        if($scope.searchResult[idx].plang){
            $scope.plang = $scope.searchResult[idx].plang.replace(/["']/g, "");
        }
    }

    //Reset field values and hide duplicate patient dropdown
    $scope.resetFieldValues = function() {
        $scope.searchName = "";
        $scope.searchSerial = "";
        $scope.patientMRN = "";
        $scope.patientEmail = "";
        $scope.patientRAMQ = "";
        $scope.searchName = "";
        $scope.selectPatient = false;
        $scope.foundPatient = false;

        // Call function to reset the report
        $scope.resetReportValues();    
    }

    //Reset report values and hide list
    $scope.resetReportValues = function() {
      
        $scope.featureList.diagnosis = true;
        $scope.featureList.appointments = true;
        $scope.featureList.questionnaires = true;
        $scope.featureList.education = true;
        $scope.featureList.testresults = true;
        $scope.featureList.pattestresults = true;
        $scope.featureList.notifications = true;
        $scope.featureList.treatplan = true;
        $scope.featureList.clinicalnotes = true;
        $scope.featureList.treatingteam = true;
        $scope.featureList.general = true;

        // Reset the report values
        $scope.diagReport = "";
        $scope.qstReport = "";
        $scope.apptReport = "";
        $scope.educReport = "";
        $scope.testReport = "";
        $scope.pattestReport = "";
        $scope.noteReport = "";
        $scope.clinnoteReport = "";
        $scope.txteamReport = "";
        $scope.generalReport = "";
        $scope.txplanReport = "";  
    }
    /**
     *  Generate the desired report based on user input
     *  @param psnum: selected patient serial number
     *  @param featureList: truthy/falsy variables for each report segment (user selected)
     */
    $scope.fetchData = function(){
        $.ajax({
            type: "POST",
            url: "patient-reports/get/patient-data",
            data: {
                psnum: $scope.psnum,
                diagnosis: $scope.featureList.diagnosis,
                appointments: $scope.featureList.appointments,
                questionnaires: $scope.featureList.questionnaires,
                education: $scope.featureList.education,
                testresults: $scope.featureList.testresults,
                pattestresults: $scope.featureList.pattestresults,
                notes: $scope.featureList.notifications,
                treatplan: $scope.featureList.treatplan,
                clinicalnotes: $scope.featureList.clinicalnotes,
                treatingteam: $scope.featureList.treatingteam,
                general: $scope.featureList.general,
            },
            success: function(response){
                populateTables(JSON.parse(response));
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.DB_ERROR'));
            }
        });
    }
    /**
     * Populate tables for display in view
     * @param result: result of ajax query to db to retrieve selected patient records
     * @return none
     */
    function populateTables(result){
        if(result && (result !== null)){
            if(result.diagnosis){
                $scope.diagReport = result.diagnosis;
                strip($scope.diagReport);
            }
            if(result.questionnaires){
                $scope.qstReport = result.questionnaires;
                strip($scope.qstReport); //TODO replace null with not completed
            }
            if(result.education){
                $scope.educReport = result.education;
                strip($scope.educReport); //TODO replace 1/0 with read/not read
            }
            if(result.appointments){
                $scope.apptReport = result.appointments;
                strip($scope.apptReport);
            }
            if(result.testresults){
                $scope.testReport = result.testresults;
                strip($scope.testReport);
            }
            if(result.pattestresults){
                $scope.pattestReport = result.pattestresults;
                strip($scope.pattestReport);
            }
            if(result.notes){
                $scope.noteReport = result.notes;
                strip($scope.noteReport); //TODO replace1/0 with read/not read
            }
            if(result.clinicalnotes){
                $scope.clinnoteReport = result.clinicalnotes;
                strip($scope.clinnoteReport);
            }
            if(result.treatingteam){
                $scope.txteamReport = result.treatingteam;
                strip($scope.txplanReport); // TODO replace 1/0 with read/not read
            }
            if(result.general){
                $scope.generalReport = result.general;
                strip($scope.generalReport); // TODO replace 1/0 with read/ not read
            }
            if(result.treatplan){
                $scope.txplanReport = result.treatplan;
                strip($scope.txplanReport);
            }
        
        }else{ //something went wrong, no result recieved
            ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.SEARCH_FAIL'));
        }
    }


    //Remove whitespace from input
    function strip(inp){
        for(var i=0; i<inp.length; i++){
            for (var key in inp[i]){
                if(inp[i][key]){
                    inp[i][key] = inp[i][key].replace(/["']/g, "");
                }
            }
        }
    }

});

