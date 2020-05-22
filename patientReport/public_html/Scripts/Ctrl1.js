//------------------------------------------------------------------------
// K.Agnew Feb 2019
//------------------------------------------------------------------------

//
// Controller for the Patient Reports tab within Opal Reporting
//
app.controller('Ctrl1', function ($scope, $http) {
    $scope.testArray = [{ prop1: "caaa", prop2: "2016-09-28 00:00:00", prop3: "" }, { prop1: "aaab", prop2: "2014-03-12 00:00:00", prop3: "duck" }, { prop1: "baaa", prop2: "2021-06-12 00:00:00", prop3: "" }, { prop1: "zzza", prop2: "2014-03-13 00:00:00", prop3: "sheep" }];

    $scope.db = {
        prod: true,
        preprod: false
    };

    $scope.updateColours = function () {
        var tab = document.getElementById("tab");
        var rep = document.getElementById("PatientReport");
        if ($scope.db.prod) {
            tab.style.backgroundColor = "palegreen";
            rep.style.border = "thick dotted palegreen";
        } else {
            tab.style.backgroundColor = "paleturquoise";
            rep.style.border = "thick dotted paleturquoise";
        }
    }

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
    $scope.searchSerial = ""; //search parameters for a patient
    $scope.searchName = "";
    $scope.patientMRN = "";
    $scope.patientEmail = "";
    $scope.patientRAMQ = "";

    $scope.psnum = ""; //the selected patient identifiers for our report
    $scope.pname = "";
    $scope.pfname = "";
    $scope.psex = "";
    $scope.pemail = "";
    $scope.pramq = "";
    $scope.pmrn = "";
    $scope.planguage = "";


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

    $scope.nullPatient = "";
    $scope.selectedName = "";

    $scope.findPat = function () {
        if ($scope.searchName == "" && $scope.searchSerial == "" && $scope.patientMRN == "" && $scope.patientEmail == "" && $scope.patientRAMQ == "") {
            $scope.foundPatient = false;
        }
        else if ($scope.searchName) {


            //find by name
            //http get function, returns whatever matches it finds
            $http.get("cgi-bin/findPatientByName.pl", { params: { pname: $scope.searchName, db: $scope.db.prod } })
                .then(function (response) {

                    //console.log(response.data);
                    displayName(response.data);
                }, function (error) {
                    console.log(error);
                });
        }
        else if ($scope.searchSerial) {

            //find by serial
            $http.get("cgi-bin/findPatientByNum.pl", { params: { snum: $scope.searchSerial, db: $scope.db.prod } })
                .then(function (response) {

                    //console.log(response.data);
                    displayName(response.data);
                }, function (error) {
                    console.log(error);
                });
        }
        else if ($scope.patientMRN) {

            //find by serial
            $http.get("cgi-bin/findPatientByMRN.pl", { params: { pmrn: $scope.patientMRN, db: $scope.db.prod } })
                .then(function (response) {

                    //console.log(response.data);
                    displayName(response.data);
                }, function (error) {
                    console.log(error);
                });
        }
        else if ($scope.patientEmail) {

            //find by serial
            $http.get("cgi-bin/findPatientByEmail.pl", { params: { pemail: $scope.patientEmail, db: $scope.db.prod } })
                .then(function (response) {

                    //console.log(response.data);
                    displayName(response.data);
                }, function (error) {
                    console.log(error);
                });
        }
        else if ($scope.patientRAMQ) {

            //find by serial
            $http.get("cgi-bin/findPatientByRAMQ.pl", { params: { pramq: $scope.patientRAMQ, db: $scope.db.prod } })
                .then(function (response) {

                    //console.log(response.data);
                    displayName(response.data);
                }, function (error) {
                    console.log(error);
                });
        }
    }
    function displayName(inp) {

        if (!inp) { //could not find a match
            alert("Could not find a patient matching these criteria");
            $scope.foundPatient = false;
        } else if (inp.patrecord.length > 1) { //found multiple matches
            alert("Found multiple patients matching these criteria, please specify");
            $scope.patoptions = [];
            var tmp = "";
            for (var i = 0; i < inp.patrecord.length; i++) {
                tmp = tmp + i + " , " + inp.patrecord[i].fname + " , " + inp.patrecord[i].lname + " , " + inp.patrecord[i].psnum + " , " + inp.patrecord[i].sex + " , " + inp.patrecord[i].email + " , " + inp.patrecord[i].ssn + " , " + inp.patrecord[i].pid + " , " + inp.patrecord[i].language;
                $scope.patoptions.push(tmp);
                tmp = "";
            }
            $scope.selectPatient = true;
            //After selection, we have result in $scope.selectedName
        } else { //found exactly 1 match
            //Set foundPatient to true to display the report
            $scope.foundPatient = true;

            //Set all of the reporting variables to true by default
            $scope.resetReportValues();

            //Set the chosen patient identifier variables
            $scope.pname = inp.patrecord[0].lname.replace(/["']/g, "");
            $scope.psnum = inp.patrecord[0].psnum.replace(/["']/g, "");
            $scope.pfname = inp.patrecord[0].fname.replace(/["']/g, "");
            $scope.psex = inp.patrecord[0].sex.replace(/["']/g, "");
            $scope.pemail = inp.patrecord[0].email.replace(/["']/g, "");
            $scope.pramq = inp.patrecord[0].ssn.replace(/["']/g, "");
            $scope.pmrn = inp.patrecord[0].pid.replace(/["']/g, "");
            $scope.planguage = inp.patrecord[0].language.replace(/["']/g, "");
        }

    }

    $scope.displaySelection = function () {

        //Set foundPatient to true to display the report
        $scope.foundPatient = true;

        //Set all of the reporting variables to true by default
        $scope.resetReportValues();

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

    // Function to reset the field falues and hide the dulplicate patient dropdown
    $scope.resetFieldValues = function () {

        // Reset the field values
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

    // Function to reset the report values and hide the list
    $scope.resetReportValues = function () {

        // Hide the report section
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
    
    //fetchData function makes the call to the backend (perl script) to retrieve data
    $scope.fetchData = function () {

        //console.log("sending request");
        //Call the perl script to get JSON objects of the SQL records
        $http.get("cgi-bin/genReport.pl", {
            params: //list of true false variables passed to the perl file 
            {
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
                db: $scope.db.prod
            }
        })
            .then(function (response) {

                //store the JSON object returned in the allData variable (Promise handling)
                populateTables(response.data);
            }, function (error) {
                console.log(error);
            });

    }
    //Register the input records to scope variables (one for each report section)
    // 	and strip away unnecessary white space / quotations
    function populateTables(input) {

        if (input === null) { //First check that we got something back
            //If input is null, we couldn't find any info for that patient
            $scope.nullPatient = "Warning: Patient not found in database";
        }
        else {
            $scope.nullPatient = "";
            if (typeof input.diagrecord !== 'undefined') {
                $scope.diagReport = input.diagrecord;
                strip($scope.diagReport);
            }
            if (typeof input.qstrecord !== 'undefined') {
                $scope.qstReport = input.qstrecord;
                strip($scope.qstReport[0]);
                for (var i = 0; i < $scope.qstReport[0].length; i++) {
                    if ($scope.qstReport[0][i].dateComplete === "null") {
                        $scope.qstReport[0][i].dateComplete = "Not Completed";
                    }
                }
            }
            if (typeof input.edcrecord !== 'undefined') {
                $scope.educReport = input.edcrecord;
                strip($scope.educReport[0]);
                for (var i = 0; i < $scope.educReport[0].length; i++) {
                    if ($scope.educReport[0][i].readStatus === "1") {
                        $scope.educReport[0][i].readStatus = "Read";
                    } else {
                        $scope.educReport[0][i].readStatus = "Not Read";
                    }
                }
            }
            if (typeof input.apptrecord !== 'undefined') {
                $scope.apptReport = input.apptrecord;
                strip($scope.apptReport[0]);
            }
            if (typeof input.resrecord !== 'undefined') {
                $scope.testReport = input.resrecord;
                console.log("length of test results" + $scope.testReport[0].length);
                strip($scope.testReport[0]);
            }
            if (typeof input.noterecord !== 'undefined') {
                $scope.noteReport = input.noterecord;
                strip($scope.noteReport[0]);
                for (var i = 0; i < $scope.noteReport[0].length; i++) {
                    if ($scope.noteReport[0][i].readStatus === "1") {
                        $scope.noteReport[0][i].readStatus = "Read";
                    } else {
                        $scope.noteReport[0][i].readStatus = "Not Read";
                    }
                }
            }
            if (typeof input.clinnoterecord !== 'undefined') {
                $scope.clinnoteReport = input.clinnoterecord;
                strip($scope.clinnoteReport[0]);

            }
            if (typeof input.txteamrecord !== 'undefined') {
                $scope.txteamReport = input.txteamrecord;
                strip($scope.txteamReport[0]);
                for (var i = 0; i < $scope.txteamReport[0].length; i++) {
                    if ($scope.txteamReport[0][i].readstatus === "1") {
                        $scope.txteamReport[0][i].readstatus = "Read";
                    } else {
                        $scope.txteamReport[0][i].readstatus = "Not Read";
                    }
                }
            }
            if (typeof input.generalrecord !== 'undefined') {
                $scope.generalReport = input.generalrecord;
                strip($scope.generalReport[0]);
                for (var i = 0; i < $scope.generalReport[0].length; i++) {
                    if ($scope.generalReport[0][i].readstatus === "1") {
                        $scope.generalReport[0][i].readstatus = "Read";
                    } else {
                        $scope.generalReport[0][i].readstatus = "Not Read";
                    }
                }
            }
            if (typeof input.treatplanrecord !== 'undefined') {
                $scope.txplanReport = input.treatplanrecord;
                strip($scope.txplanReport[0]);
            }
        }
    }

    function strip(inp) {
        for (var i = 0; i < inp.length; i++) {
            for (var key in inp[i]) {
                inp[i][key] = inp[i][key].replace(/["']/g, "");
            }
        }
    }
});




