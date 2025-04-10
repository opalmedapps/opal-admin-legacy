// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

var patApp = angular.module('opalAdmin.controllers.groupReports', ['ngAnimate', 'ui.bootstrap',  'ui.grid', 'ui.grid.resizeColumns']).

controller('groupReports', function($scope, $rootScope, Session, ErrorHandler, MODULE, $filter, $timeout){
    // navigation
    $scope.navMenu = Session.retrieveObject('menu');
    $scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.patient];
    angular.forEach($scope.navSubMenu, function(menu) {
        menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
        menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
    });

    $scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 0)) !== 0);
    $scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 1)) !== 0);
    $scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 2)) !== 0);



    // Three main categories of group reporting
    //  Ctrl+F to 'EDUCATIONAL', 'QUESTIONNAIRES', 'DEMOGRAPHICS'  to see relevant code
    $scope.category = {
		education : false,
		questionnaire : false,
		demographics : false,
    };

    $scope.switchView = function(target){
        if(target === 'education'){
            $scope.category.questionnaire = false;
            $scope.category.demographics = false;
            $scope.category.education = true;
        }else if (target === 'questionnaire'){
            $scope.category.demographics = false;
            $scope.category.education = false;
            $scope.category.questionnaire = true;
        }else if (target === 'demographics'){
            $scope.category.education = false;
            $scope.category.questionnaire = false;
            $scope.category.demographics = true;
        }
    };


    //Display variables for educational materials branch
	$scope.displayMaterialList = false;
    $scope.siteLanguage = Session.retrieveObject('user').language;
    $scope.materialTypesEN = ["Booklet", "Factsheet", "Package", "Video", "Treatment Guidelines"];
    $scope.materialTypesFR = ["Brochure", "Fiche d'information", "Paquet", "Vidéo", "Directives de traitement"];
    $scope.materialType = "";
    $scope.materialTypeFR = "";
	$scope.selectedMaterial = ""; //the selection of the user

    // div width and height for ui-grid
    $scope.width = '900px';
    $scope.height = '600px';



	//Display variables
	$scope.selectedQuestionnaire = ""; //user selected questionnaire from qstList
	$scope.showQstReport = false;
    $scope.showEducReport = false;

    // Storage variables for results of DB calls
    $scope.educList = [];
    $scope.educReport = [];
    $scope.qstList = [];
    $scope.qstReport = [];
    $scope.patientReport = [];

    // Statistics variables
    $scope.educReportLength = "";
    $scope.educAvgAge = "";
    $scope.educFemPcnt = "";
    $scope.educMalPcnt = "";
    $scope.educUnkPcnt = "";
    $scope.educReadPcnt = "";
    $scope.educAvgDaysToRead = "";

    $scope.qstPcntFemale = "";
    $scope.qstPcntMale = "";
    $scope.qstPcntUnk = "";
    $scope.qstUniquePats = "";
    $scope.qstAvgAge = "";
    $scope.qstPcntCompleted = "";
    $scope.qstAvgCompletTime = "";

    $scope.patientReportLength = "";
    $scope.regPlotData = [];
    $scope.malePlotData = [];
    $scope.femalePlotData = [];

    // UI grid objects for returned information
    $scope.educGridOptions = {
        data: 'educReport',
        columnDefs: [
            { field: 'pname', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.NAME'), width: '15%', enableColumnMenu: false },
            { field: 'plname', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.NAME_FAMILY'), width: '15%', enableColumnMenu: false },
            { field: 'pser', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.SERIAL'), width: '10%', enableColumnMenu: false },
            { field: 'page', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.AGE'), width: '10%', enableColumnMenu: false },
            { field: 'pdob', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.BIRTH'), width:'10%', enableColumnMenu: false },
            { field: 'psex', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.SEX'), width:'10%', enableColumnMenu: false },
            { field: 'edate', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.DATE_SENT'), width:'15%', enableColumnMenu: false },
            { field: 'eupdate', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.DATE_READ'), width:'15%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true,
    };

    $scope.qstGridOptions = {
        data: 'qstReport',
        columnDefs: [
            { field: 'pname', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.NAME'), width: '15%', enableColumnMenu: false },
            { field: 'plname', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.NAME_FAMILY'), width: '15%', enableColumnMenu: false },
            { field: 'pser', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.SERIAL'), width: '15%', enableColumnMenu: false },
            { field: 'psex', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.SEX'), width: '10%', enableColumnMenu: false },
            { field: 'pdob', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.BIRTH'), width:'15%', enableColumnMenu: false },
            { field: 'qdate', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.DATE_SENT'), width:'15%', enableColumnMenu: false },
            { field: 'qcomplete', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.DATE_COMPLETE'), width:'15%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true,

    };

    $scope.demoGridOptions = {
        data: 'patientReport',
        columnDefs: [
            { field: 'pname', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.NAME'), width: '10%', enableColumnMenu: false },
            { field: 'plname', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.NAME_FAMILY'), width: '10%', enableColumnMenu: false },
            { field: 'pser', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.SERIAL'), width: '8%', enableColumnMenu: false },
            { field: 'page', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.AGE'), width: '5%', enableColumnMenu: false },
            { field: 'pdob', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.BIRTH'), width:'10%', enableColumnMenu: false },
            { field: 'psex', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.SEX'), width:'5%', enableColumnMenu: false },
            { field: 'pemail', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.EMAIL'), width:'10%', enableColumnMenu: false },
            { field: 'plang', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.LANG'), width:'10%', enableColumnMenu: false },
            { field: 'preg', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.REG'), width:'13%', enableColumnMenu: false },
            { field: 'diagdesc', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.DIAGNOSIS'), width:'9%', enableColumnMenu: false },
            { field: 'diagdate', displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.GROUP_REPORTS.DATE_DIAG'), width:'10%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true,
    };
    // Manually refresh the view of the ui-grids after reloading data
    $scope.refresh = false;
    var refresh = function(){
        $scope.refresh = true;
        $timeout(function(){
            $scope.refresh = false;
        }, 20);
    };

    // Safe apply function prevents potential errors with calling $apply twice at once
    $scope.safeApply = function(fn){
        var phase = this.$root.$$phase;
        if(phase == '$apply' || phase == '$digest'){ //currently using an apply or digest already
            if(fn && (typeof(fn) === 'function')){
                fn(); //just call the function, no apply can be used right now
            }
        }
        else{ //wrap function with $apply variable to manually trigger a digest cycle
            this.$apply(fn);
        }
    }

    //
    // EDUCATIONAL MATERIAL SECTION
    // All db call functions & helper functions pertaining to educational material here
    //

    /**
     * Generate list of available educational materials from DB
     */
    $scope.genEducationMaterialOptions = function(){
        $scope.safeApply(function(){
            if($scope.siteLanguage === 'FR'){
                if($scope.materialTypeFR === "Brochure"){
                    $scope.materialType = "Booklet";
                }else if($scope.materialTypeFR === "Fiche d'information"){
                    $scope.materialType = "Factsheet";
                }else if($scope.materialTypeFR === "Paquet"){
                    $scope.materialType = "Package";
                }else if($scope.materialTypeFR === "Vidéo"){
                    $scope.materialType = "Video";
                }else if($scope.materialTypeFR === "Directives de traitement"){
                    $scope.materialType = "Treatment Guidelines";
                }
            }
        });
        //need to clear selected material here to prevent 422 error from data validation
        $scope.selectedMaterial = "";
        $.ajax({
            type: "POST",
            url: "patient/get/education-options",
            data: {matType: $scope.materialType},
            dataType: "json",
            success: function(response){
                prepareEducList(response);
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.DB_ERROR'));
            }
        });

    }

    /**
     * Generate selected educational material report
     */
    $scope.genEducReport = function(){
        $.ajax({
            type: "POST",
            url: "patient/get/educ-report",
            data: {
                type: $scope.materialType,
                name: $scope.selectedMaterial},
            dataType: "json",
            success: function(response){
                prepareEducReport(response);
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.DB_ERROR'));
            }
         });
    }

    // Helper function to prepare educational material list for selection
    function prepareEducList(inp){

        $scope.safeApply(function() {

            $scope.educList = [];
            var tmp = "";
            if(inp && (inp !== null)){
                for(var i=0; i < inp.length; i++){
                tmp = tmp + inp[i].name.replace(/["']/g, "");
                $scope.educList.push(tmp);
                tmp = "";
                }
                $scope.displayMaterialList = true;
            }else{
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.SEARCH_FAIL'));
            }

        });


    }

    // Helper function prepare educational material report for display
    function prepareEducReport(inp){

        $scope.safeApply(function() {
            if(inp && (inp !== null)){
                $scope.educReport = inp;
                for(var i = 0; i< $scope.educReport.length; i++){
                    if(!$scope.educReport[i].pname){
                        $scope.educReport[i].pname = "N/A";
                    }
                    if(!$scope.educReport[i].plname){
                        $scope.educReport[i].plname = "N/A";
                    }
                    if(!$scope.educReport[i].pser){
                        $scope.educReport[i].pser = "N/A";
                    }
                    if(!$scope.educReport[i].psex){
                        $scope.educReport[i].psex = "N/A";
                    }
                }
                $scope.educReportLength = $scope.educReport.length;
                $scope.showEducReport = true;
                refresh();
                prepareEducStats();



            }else{ //error, no result returned
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.SEARCH_FAIL'));
            }

        });


    }


    // Helper function to prepare educational material statistics for display
    function prepareEducStats(){
        var totAge = 0;
        var ageDenom = 0;
        var femCount = 0;
        var malCount = 0;
        var totRead = 0;
        var daysToRead = 0;
        var date_diff_days = function(d1, d2){
            return Math.floor((Date.UTC(d2.getFullYear(), d2.getMonth(), d2.getDate()) - Date.UTC(d1.getFullYear(), d1.getMonth(), d1.getDate()) ) / (1000*60*60*24));
        }
        $scope.safeApply(function(){
            for(var i = 0; i < $scope.educReportLength; i++){
                if($scope.educReport[i].page != null){
                    totAge += parseInt($scope.educReport[i].page);
                    ageDenom++;
                }
                if($scope.educReport[i].psex === "Female"){
                    femCount++;
                }else if($scope.educReport[i].psex === "Male"){
                    malCount++;
                }

                if($scope.educReport[i].eread === "1"){
                    totRead++;
                    daysToRead += date_diff_days(new Date($scope.educReport[i].edate),new Date($scope.educReport[i].eupdate));
                }
            }

            //average age
            $scope.educAvgAge = (totAge/ageDenom).toFixed(2);
            //gender breakdown
            $scope.educMalPcnt = ((malCount/$scope.educReportLength)*100).toFixed(2);
            $scope.educFemPcnt = ((femCount/$scope.educReportLength)*100).toFixed(2);
            $scope.educUnkPcnt = (100-$scope.educMalPcnt-$scope.educFemPcnt).toFixed(2);
            // % materials read
            $scope.educReadPcnt = ((totRead/$scope.educReportLength)*100).toFixed(2);
            // average time to read among read materials
            $scope.educAvgDaysToRead = (daysToRead/totRead).toFixed(2);
        });
    }

    //
    // QUESTIONNAIRES SECTION
    // All questionnaire db calls and helper functions here
    //

    /**
     * Generate list of questionnaires found in DB
     */
    $scope.genQuestionnaireOptions = function(){
        //$scope.qstReport = "";
        $.ajax({
            type:"POST",
            url:"patient/get/questionnaire-options",
            data: null,
            dataType: "json",
            success: function(response){
                prepareQstList(response);
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.DB_ERROR'));
            }

        });
    }

    /**
     * Generate questionnaire report from selected questionnaire
     *
     */
    $scope.genQstReport = function(){
        $.ajax({
            type: "POST",
            url: "patient/get/questionnaire-report",
            data: {qstName: $scope.selectedQuestionnaire},
            dataType: "json",
            success: function(response){
                prepareQstReport(response);
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.SEARCH_FAIL'));
            }
        });
    }

    // Helper function to generate list of retrieved questionnaires
    function prepareQstList(inp){

        $scope.safeApply(function() {
            var tmp = "";
            if(inp && (inp !== null)){
                for(var i=0; i < inp.length; i++){
                tmp = tmp + inp[i].name.replace(/["']/g, "");
                $scope.qstList.push(tmp);
                tmp ="";
                }
            }else{ // no questionnaires returned
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.SEARCH_FAIL'));
            }

        });

    }

    //Helper function to generate list of patient receiving the specified questionnaire
    function prepareQstReport(inp){

        $scope.safeApply(function() {
            if(inp && (inp !== null)){
                $scope.qstReport = inp;
                for(var i=0; i< $scope.qstReport.length; i++){
                    if(!$scope.qstReport[i].pname){
                        $scope.qstReport[i].pname = "N/A";
                    }
                    if(!$scope.qstReport[i].plname){
                        $scope.qstReport[i].plname = "N/A";
                    }
                    if(!$scope.qstReport[i].pdob){
                        $scope.qstReport[i].pdob = "N/A";
                    }
                    if(!$scope.qstReport[i].qcomplete){
                        $scope.qstReport[i].qcomplete = "N/A";
                    }
                }
                $scope.showQstReport = true;
                $scope.qstReportLength = $scope.qstReport.length;
                refresh();
            }else{
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.SEARCH_FAIL'));
            }

            prepareQstStats();
        });

    }

    // Helper function to generate patient questionnaire statistics
    function prepareQstStats(){
        $scope.safeApply(function(){
            var femCount = 0;
            var malCount = 0;
            var totNulls = 0;
            var unkCount = 0;
            var tot = $scope.qstReportLength;
            var uniquePats = [];
            // Initialize uniquePats only if $scope.qstReport[0] exists
            if ($scope.qstReport[0]) {
                uniquePats.push($scope.qstReport[0].pname + " , " + $scope.qstReport[0].plname);}
            var curPat = "";
            var uniquePatDobs = [];
            var completionTimes = [];
            var date_diff_days = function(d1, d2){
                return Math.floor((Date.UTC(d2.getFullYear(), d2.getMonth(), d2.getDate()) - Date.UTC(d1.getFullYear(), d1.getMonth(), d1.getDate()) ) / (1000*60*60*24));
            }

            for(var i = 0; i < tot; i++){
                // gender breakdown
                if($scope.qstReport[i].psex === "Female"){
                    femCount++;
                }else if($scope.qstReport[i].psex === "Male"){
                    malCount++;
                }else{
                    unkCount++;
                }

                //unique patient count
                curPat = $scope.qstReport[i].pname + " , " + $scope.qstReport[i].plname;
                if(!uniquePats.includes(curPat)){
                    uniquePats.push(curPat);
                    uniquePatDobs.push(new Date($scope.qstReport[i].pdob));
                }

                // %completion and completion time
                if(($scope.qstReport[i].qcomplete === "N/A") || ($scope.qstReport[i].qcomplete === "0000-00-00 00:00:00")){
                    totNulls ++;
                }else{
                    completionTimes.push(date_diff_days(new Date($scope.qstReport[i].qdate), new Date($scope.qstReport[i].qcomplete)));
                }
            }
            // gender statistics
            $scope.qstPcntFemale = ((femCount/tot)*100).toFixed(2);
            $scope.qstPcntMale = ((malCount/tot)*100).toFixed(2);
            $scope.qstPcntUnk = (100 - $scope.qstPcntMale - $scope.qstPcntFemale).toFixed(2);
            $scope.qstUniquePats = uniquePats.length;
            //age stats
            var sum = 0;
            var invalids = 0;
            var avg = 0;
            var patAges = [];
            for(var j = 0; j < uniquePatDobs.length; j++){
                patAges.push(date_diff_days(uniquePatDobs[j],new Date()));
            }
            for(var k = 0; k<patAges.length; k++){
                if(isNaN(patAges[k])){
                    invalids++;
                }else{
                    sum += patAges[k];
                }
            }
            avg = sum/(patAges.length - invalids);
            $scope.qstAvgAge = (avg/365).toFixed(2);

            // $ completion stats

            $scope.qstPcntCompleted = (((tot-totNulls)/tot)*100).toFixed(2);
            sum = 0;
            for(var n = 0; n < completionTimes.length; n++){
                sum += completionTimes[n];
            }
            $scope.qstAvgCompletTime = (sum/completionTimes.length).toFixed(2);

        });

    }

    //
    // DEMOGRAPHICS SECTIONS
    // All demographics DB calls and helper functions here
    //

    /**
     * Generate full patient list for demographics information
     *
     */
    $scope.genPatientReport = function(){
        $.ajax({
            type: "POST",
            url: "patient/get/patient-report",
            data: null,
            dataType: "json",
            success: function(response){
                preparePatientReport(response);
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.DB_ERROR'));
            }
        });
    }


    // Helper function to clean patient list and generate diagnosis breakdown chart.
    function preparePatientReport(inp){

        $scope.safeApply( function() {
            if(inp && (inp !== null)){
                $scope.patientReport = inp;
                $scope.diagnosisdata = [];
                for(let i = 0; i < $scope.patientReport.length; i++){
                    if(!$scope.patientReport[i].pname){
                        $scope.patientReport[i].pname = "N/A";
                    }
                    if(!$scope.patientReport[i].plname){
                        $scope.patientReport[i].plname = "N/A";
                    }
                    if(!$scope.patientReport[i].psex){
                        $scope.patientReport[i].psex = "N/A";
                    }
                    if(!$scope.patientReport[i].pser){
                        $scope.patientReport[i].pser = "N/A";
                    }
                    if(!$scope.patientReport[i].pdob){
                        $scope.patientReport[i].pdob = "N/A";
                    }
                    if(!$scope.patientReport[i].page){
                        $scope.patientReport[i].page = "N/A";
                    }
                    if(!$scope.patientReport[i].pemail){
                        $scope.patientReport[i].pemail = "N/A";
                    }
                    if(!$scope.patientReport[i].plang){
                        $scope.patientReport[i].plang = "N/A";
                    }
                    if(!$scope.patientReport[i].preg){
                        $scope.patientReport[i].preg = "N/A";
                    }
                    if($scope.patientReport[i].diagdesc){
                        if($scope.diagnosisdata.some(x => (x.name && x.name == $scope.patientReport[i].diagdesc)))
                            $scope.diagnosisdata.find(x => (x.name && x.name == $scope.patientReport[i].diagdesc)).y += 1;
                        else
                            $scope.diagnosisdata.push({name: $scope.patientReport[i].diagdesc, y: 1.0});
                    }else{
                        $scope.patientReport[i].diagdesc = "N/A";
                    }
                    if(!$scope.patientReport[i].diagdate){
                        $scope.patientReport[i].diagdate = "N/A";
                    }
                 }
                $scope.patientReportLength = $scope.patientReport.length;
                $scope.diagnosisdata.forEach(x => x.y = x.y/$scope.patientReportLength*100);
                $scope.diagnosisdata.sort((a,b) => {return a.name.localeCompare(b.name)});
                refresh();
                prepareDemoStats();
            }else{
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.SEARCH_FAIL'));
            }
        });



    }

    // Helper function to generate demographics statistics
    function prepareDemoStats(){
        $scope.safeApply(function(){


            var femCount = 0;
            var malCount = 0;
            var unkCount = 0;
            var totAge = 0;
            var nullCount = 0;
            var frCount = 0;
            $scope.demoPcntFemale = 0;
            $scope.demoPcntMale = 0;

            var diagDict = new Object(); //diagnosis tracking
            $scope.femalePlotData = [];
            $scope.malePlotData = [];
            $scope.regPlotData = [];

            for(var i = 0; i< $scope.patientReportLength; i++){
                // male/female demgraphics
                if($scope.patientReport[i].psex === "Female"){
                    femCount++;
                    $scope.femalePlotData.push([new Date($scope.patientReport[i].preg).getTime(), femCount]);
                }else if($scope.patientReport[i].psex === "Male"){
                    malCount++;
                    $scope.malePlotData.push([new Date($scope.patientReport[i].preg).getTime(), malCount]);
                }else{
                    unkCount++;
                }

                //avg patient age
                if($scope.patientReport[i].page && $scope.patientReport[i].page > 1){
                    totAge += parseInt($scope.patientReport[i].page);
                }else{
                    nullCount++;
                }

                // french/english
                if($scope.patientReport[i].plang === "FR"){
                    frCount++;
                }

                //registration date tracking (to be plotted)
                $scope.regPlotData.push([new Date($scope.patientReport[i].preg).getTime(), i]);


                // diagnosis breakdown
                if($scope.patientReport[i].diagdesc in diagDict){
                    diagDict[$scope.patientReport[i].diagdesc]++;
                }else{
                    diagDict[$scope.patientReport[i].diagdesc] = 1;
                }

                $scope.demoPcntFemale = ((femCount/$scope.patientReportLength)*100).toFixed(2);
                $scope.demoPcntMale = ((malCount/$scope.patientReportLength)*100).toFixed(2);
                $scope.demoPcntUnk = ((unkCount/$scope.patientReportLength)*100).toFixed(2);

                $scope.demoAvgAge = (totAge/($scope.patientReportLength-nullCount)).toFixed(2);
                $scope.demoPcntFrench = ((frCount/$scope.patientReportLength)*100).toFixed(2);
                $scope.demoPcntEnglish = (100-$scope.demoPcntFrench).toFixed(2);


            }
        });


    }

});


patApp.directive('resize', ['$window', function($window){
    return  {
        link: link,
        restrict: 'A'
    };

    function link(scope, element, attrs){
        scope.width = $window.innerWidth*0.8;
        function onResize(){
            if(scope.width !== $window.innerWidth){
                scope.width = $window.innerWidth*0.8;
                scope.$digest();
            }
        };

        function cleanUp() {
            angular.element($window).off('resize', onResize);
        }

        angular.element($window).on('resize', onResize);
        scope.$on('$destroy', cleanUp);
    }
}]);
