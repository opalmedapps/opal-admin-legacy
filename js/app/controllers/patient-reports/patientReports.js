angular.module('opalAdmin.controllers.patientReports', ['ngAnimate', 'ui.bootstrap']).

controller('patientReports', function($scope, Session, ErrorHandler, MODULE){
    console.log("Control passed to patientReports.js");

    $scope.foundPatient = false; //only show the report once patient is found/selected
    $scope.selectPatient = false; //only show if multiple patients are found from search and user must choose one
    $scope.featureList = { // Which features will be added into the report
        diagnosis: false,
        appointments: false,
        questionnaires: false,
        education: false,
        testresults: false,
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
    $scope.noteReport = "";
    $scope.clinnoteReport = "";
    $scope.txteamReport = "";
    $scope.generalReport = "";
    $scope.txplanReport = "";

    $scope.nullPatient = "";
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
            console.log("Searching by name");
            $.ajax({
                type: "POST",
                url: "patient-reports/find/patient-name",
                data: $scope.searchName,
                success: function(response){
                    console.log(response);
                    displayName(response.data);
                },
                error: function(err){
                    console.log(err)
                    //ErrorHandler.onError() TODO
                }
            });
        }else if ($scope.searchMRN){ //find by MRN
            $.ajax({
                type: "POST",
                url: "patient-reports/find/patient-mrn",
                data: $scope.searchMRN,
                success: function(response){
                    console.log(response);
                    displayName(response.dta);
                },
                error: function(err){
                    console.log(err)
                    //ErrorHandler.onError() TODO
                }
            });
        }else if ($scope.searchRAMQ){ //find my RAMQ
            $.ajax({
                type: "POST",
                url: "patient-reports/find/patient-ramq",
                data: $scope.searchRAMQ,
                success: function(response){
                    console.log(response);
                    displayName(response.data);
                },
                error: function(err){
                    console.log(err)
                    //ErrorHandler.onError()
                }
            });
        }else{ //some error occured
            console.log("Something really wrong hapepend");
            // ErrorHandler TODO
        }
    }

    /**
     *  Process results of ajax patient search
     * 
     *  @param result: patient(s) info
     *  @return 
     */
    function displayName(result){
        if(!result){ //no match found for input parameter
            $scope.foundPatient = false;
            console.log("No patient found matching search");
            // ErrorHandler TODO
        }else if (result.patrecord.length > 1){ //found multiple patients matching search
            console.log("Multiple matches found");
            $scope.patOptions = [];
            var tmp = "";
            //load each result into patOptions array for selection
            for (var i = 0; i < result.patrecord.length; i++){
                tmp = tmp + i + " , " + result.patrecord[i].fname + " , " + result.patrecord[i].lname + " , " + result.patrecord[i].psnum + " , " + result.patrecord[i].sex + " , " + result.patrecord[i].email + " , " + result.patrecord[i].ssn + " , " + result.patrecord[i].pid + " , " + result.patrecord[i].language;
                $scope.patOptions.push(tmp);
                tmp = "";
            }
            $scope.selectPatient = true; //display dialog to select patient, result stored in scope.selectedName and displayPatient called 
        } else { //exactly one match
            $scope.foundPatient = true; //display patient table
            $scope.resetReportValues(); //set all report options to true by default

            // set selected patient identifiers
            $scope.pname = result.patrecord[0].lname.replace(/["']/g, "");
            $scope.psnum = result.patrecord[0].psnum.replace(/["']/g, "");
            $scope.pfname = result.patrecord[0].fname.replace(/["']/g, "");
            $scope.psex = result.patrecord[0].sex.replace(/["']/g, "");
            $scope.pemail = result.patrecord[0].email.replace(/["']/g, "");
            $scope.pramq = result.patrecord[0].ssn.replace(/["']/g, "");
            $scope.pmrn = result.patrecord[0].pid.replace(/["']/g, "");
            $scope.planguage = result.patrecord[0].language.replace(/["']/g, "");   
        }
    }

    // display the selected patient (this function is called by the template after selecting a patient from the list of options)
    $scope.displaySelection = function() {
        $scope.foundPatient = true; //display patient table
        $scope.resetReportValues(); //set report features all true

        //Set the chosen patient identifier variables
        $scope.pname = $scope.selectedName.split(" , ")[2].replace(/["']/g, "");
        $scope.psnum = $scope.selectedName.split(" , ")[3].replace(/["']/g, "");
        $scope.pfname = $scope.selectedName.split(" , ")[1].replace(/["']/g, "");
        $scope.psex = $scope.selectedName.split(" , ")[4].replace(/["']/g, "");
        $scope.pemail = $scope.selectedName.split(" , ")[5].replace(/["']/g, "");
        $scope.pramq = $scope.selectedName.split(" , ")[6].replace(/["']/g, "");
        $scope.pmrn = $scope.selectedName.split(" , ")[7].replace(/["']/g, "");
        $scope.planguage = $scope.selectedName.split(" , ")[8].replace(/["']/g, "");
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
      
        $scope.featureList.diagnosis = false;
        $scope.featureList.appointments = false;
        $scope.featureList.questionnaires = false;
        $scope.featureList.education = false;
        $scope.featureList.testresults = false;
        $scope.featureList.notifications = false;
        $scope.featureList.treatplan = false;
        $scope.featureList.clinicalnotes = false;
        $scope.featureList.treatingteam = false;
        $scope.featureList.general = false;

        // Reset the report values
        $scope.diagReport = "";
        $scope.qstReport = "";
        $scope.apptReport = "";
        $scope.educReport = "";
        $scope.testReport = "";
        $scope.noteReport = "";
        $scope.clinnoteReport = "";
        $scope.txteamReport = "";
        $scope.generalReport = "";
        $scope.txplanReport = "";  
    }
    /** TODO: choose url & assign rewrite rule
     *  Retrieve selected patient results from featureList
     */
    $scope.fetchData = function(){
        $.ajax({
            type: "POST",
            url: "",
            data: {
                psnum: $scope.psnum,
                diagnosis: $scope.featureList.diagnosis,
                appointments: $scope.featureList.appointments,
                questionnaires: $scope.featureList.questionnaires,
                education: $scope.featureList.education,
                testresults: $scope.featureList.testresults,
                notes: $scope.featureList.notifications,
                treatplan: $scope.featureList.treatplan,
                clinicalnotes: $scope.featureList.clinicalnotes,
                treatingteam: $scope.featureList.treatingteam,
                general: $scope.featureList.general,
            },
            success: function(response){
                console.log(response);
                populateTables(response.data);
            },
            error: function(err){
                console.log(err)
                //ErrorHandler.onError() TODO
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
            $scope.nullPatient = ""; //TODO do i need this variable
            if( typeof result.diagrecord !== 'undefined'){
                $scope.diagReport = result.diagrecord;
                strip($scope.diagReport);
            }
            if( typeof result.qstrecord !== 'undefined'){
                $scope.qstReport = result.qstrecord;
                strip($scope.qstReport); //TODO replace null with not completed
            }
            if( typeof result.edcrecord !== 'undefined'){
                $scope.educReport = result.edcrecord;
                strip($scope.educReport); //TODO replace 1/0 with read/not read
            }
            if( typeof result.apptrecord !== 'undefined'){
                $scope.apptReport = result.apptrecord;
                strip($scope.apptReport);
            }
            if( typeof result.resrecord !== 'undefined'){
                $scope.testReport = result.resrecord;
                strip($scope.testReport);
            }
            if( typeof result.noterecord !== 'undefined'){
                $scope.noteReport = result.noterecord;
                strip($scope.noteReport); //TODO replace1/0 with read/not read
            }
            if( typeof result.clinnoterecord !== 'undefined'){
                $scope.clinnoteReport = result.clinnoterecord;
                strip($scope.clinnoteReport);
            }
            if( typeof result.txteamrecord !== 'undefined'){
                $scope.txteamReport = result.txteamrecord;
                strip($scope.txplanReport); // TODO replace 1/0 with read/not read
            }
            if( typeof result.generalrecord !== 'undefined'){
                $scope.generalReport = result.generalrecord;
                strip($scope.generalReport); // TODO replace 1/0 with read/ not read
            }
            if( typeof result.treatplanrecord !== 'undefined'){
                $scope.txplanReport = result.treatplanrecord;
                strip($scope.txplanReport);
            }
        
        }else{ //something went wrong, no result recieved
            console.log("No result receieved from DB");
            //Error handler TODO

        }
    }


    //Remove whitespace from input
    function strip(inp){
        for(var i=0; i<inp.length; i++){
            for (var key in inp[i]){
                inp[i][key] = inp[i][key].replace(/["']/g, "");
            }
        }
    }
});

