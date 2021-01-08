angular.module('opalAdmin.controllers.groupReports', ['ngAnimate', 'ui.bootstrap',  'ui.grid', 'ui.grid.resizeColumns']).

controller('groupReports', function($scope, Session, ErrorHandler, MODULE, $uibModal, $filter){
   
    // Three main categories of group reporting
    //  Ctrl+F to 'EDUCATIONAL', 'QUESTIONNAIRES', 'DEMOGRAPHICS'  to see relevant code
    $scope.category = {
		education : true,
		questionnaire : false,
		demographics : false,
    };


    //Display variables for educational materials branch
	$scope.displayMaterialList = false;
	$scope.materialTypes = ["Booklet","Factsheet","Package","Video","Treatment Guidelines"];
	$scope.materialType = "";
	$scope.selectedMaterial = ""; //the selection of the user
    $scope.showEducReport = false;
    
	//Display variables for questionnares branch
	$scope.selectedQuestionnaire = ""; //user selected questionnaire from qstList
	$scope.showQstReport = false;

	//Display variables for demographics branch
    $scope.genConsentTable = true;
    
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


    $scope.educGridOptions = {
        data: 'educReport',
        columnDefs: [
            { field: 'pname', displayName: 'First Name', width: '15%', enableColumnMenu: false },
            { field: 'plname', displayName: 'Last Name', width: '15%', enableColumnMenu: false },
            { field: 'pser', displayName: 'Serial', width: '10%', enableColumnMenu: false },
            { field: 'page', displayName: 'Age', width: '10%', enableColumnMenu: false },
            { field: 'pdob', displayName: 'Date of Birth', width:'10%', enableColumnMenu: false },
            { field: 'psex', displayName: 'Sex', width:'10%', enableColumnMenu: false },
            { field: 'edate', displayName: 'Date Sent', width:'15%', enableColumnMenu: false },
            { field: 'eupdate', displayName: 'Date Read', width:'15%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        enableColumnResizing: true,
    };

    $scope.qstGridOptions = {
        data: 'qstReport',
        columnDefs: [
            { field: 'pname', displayName: 'First Name', width: '15%', enableColumnMenu: false },
            { field: 'plname', displayName: 'Last Name', width: '15%', enableColumnMenu: false },
            { field: 'pser', displayName: 'Opal Serial', width: '10%', enableColumnMenu: false },
            { field: 'psex', displayName: 'Sex', width: '10%', enableColumnMenu: false },
            { field: 'pdob', displayName: 'Date of Birth', width:'15%', enableColumnMenu: false },
            { field: 'qdate', displayName: 'Date Sent', width:'15%', enableColumnMenu: false },
            { field: 'qcomplete', displayName: 'Date Complete', width:'15%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        //useExternalFiltering: true,
        enableColumnResizing: true,

    };

    $scope.demoGridOptions = {
        data: 'patientReport',
        columnDefs: [
            { field: 'pname', displayName: 'First Name', width: '10%', enableColumnMenu: false },
            { field: 'plname', displayName: 'Last Name', width: '10%', enableColumnMenu: false },
            { field: 'pser', displayName: 'Serial', width: '8%', enableColumnMenu: false },
            { field: 'page', displayName: 'Age', width: '5%', enableColumnMenu: false },
            { field: 'pdob', displayName: 'Date of Birth', width:'10%', enableColumnMenu: false },
            { field: 'psex', displayName: 'Sex', width:'5%', enableColumnMenu: false },
            { field: 'pemail', displayName: 'Email', width:'10%', enableColumnMenu: false },
            { field: 'plang', displayName: 'Language', width:'10%', enableColumnMenu: false },
            { field: 'preg', displayName: 'Opal Registration', width:'13%', enableColumnMenu: false },
            { field: 'diagdesc', displayName: 'Diagnosis', width:'9%', enableColumnMenu: false },
            { field: 'diagdate', displayName: 'Diagnosis Date', width:'10%', enableColumnMenu: false },
        ],
        enableFiltering: true,
        //useExternalFiltering: true,
        enableColumnResizing: true,
    };

    //
    // EDUCATIONAL MATERIAL SECTION
    // All db call functions & helper functions pertaining to educational material here
    //

    /**
     * Generate list of available educational materials from DB
     */
    $scope.genEducationMaterialOptions = function(){
        $.ajax({
            type: "POST",
            url: "patient-reports/find/education-options",
            data: {matType: $scope.materialType},
            success: function(response){
                prepareEducList(JSON.parse(response));
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.DB_ERROR'));
            }
        });
    }

    /**
     * Generate selected educational material report
     */
    $scope.genEducReport = function(){
        $.ajax({
            type: "POST",
            url: "patient-reports/get/educ-report",
            data: {
                type: $scope.materialType, 
                name: $scope.selectedMaterial},
            success: function(response){
                prepareEducReport(JSON.parse(response));
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.DB_ERROR'));
            }       
         });
    }

    // Helper function to prepare educational material list for selection
    function prepareEducList(inp){
        var tmp = "";
        if(inp && (inp !== null)){
            for(var i=0; i < inp.length; i++){
			tmp = tmp + inp[i].name.replace(/["']/g, "");
			$scope.educList.push(tmp);
			tmp = "";
            }
            $scope.displayMaterialList = true;
        }else{
            ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.SEARCH_FAIL'));
        }
		

    }
    
    // Helper function prepare educational material report for display
    function prepareEducReport(inp){
        if(inp && (inp !== null)){
            $scope.educReport = inp;
            for(var i = 0; i< $scope.educReport.length; i++){
                if($scope.educReport[i].pname){
                    $scope.educReport[i].pname = $scope.educReport[i].pname.replace(/["']/g, "");
                }else{
                    $scope.educReport[i].pname = "N/A";
                }
                if($scope.educReport[i].plname){
                    $scope.educReport[i].plname = $scope.educReport[i].plname.replace(/["']/g, "");
                }else{
                    $scope.educReport[i].plname = "N/A";
                }
                if($scope.educReport[i].pser){
                    $scope.educReport[i].pser = $scope.educReport[i].pser.replace(/["']/g, "");
                }else{
                    $scope.educReport[i].pser = "N/A";
                }
                if($scope.educReport[i].page){
                    $scope.educReport[i].page = $scope.educReport[i].page.replace(/["']/g, "");
                }
                if($scope.educReport[i].pdob){
                    $scope.educReport[i].pdob = $scope.educReport[i].pdob.replace(/["']/g, "");
                }
                if($scope.educReport[i].psex){
                    $scope.educReport[i].psex = $scope.educReport[i].psex.replace(/["' ]/g, "");
                }else{
                    $scope.educReport[i].psex = "N/A";
                }
                if($scope.educReport[i].edate){
                    $scope.educReport[i].edate = $scope.educReport[i].edate.replace(/["']/g, "");
                }
                if($scope.educReport[i].eread){
                    $scope.educReport[i].eread = $scope.educReport[i].eread.replace(/["']/g, "");
                }
                if($scope.educReport[i].eupdate){
                    $scope.educReport[i].eupdate = $scope.educReport[i].eupdate.replace(/["']/g, "");
                }
                
            }
            $scope.educReportLength = $scope.educReport.length;
            prepareEducStats();



        }else{ //error, no result returned
            ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.SEARCH_FAIL'));
        }

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

    }


    //
    // QUESTIONNAIRES SECTION
    // All questionnaire db calls and helper functions here
    //

    /**
     * Generate list of questionnaires found in DB
     */
    $scope.genQuestionnaireOptions = function(){
        $scope.qstReport = "";
        $.ajax({
            type:"POST",
            url:"patient-reports/find/questionnaire-options",
            data: null,
            success: function(response){
                prepareQstList(JSON.parse(response));
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.DB_ERROR'));
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
            url: "patient-reports/get/qst-report",
            data: {qstName: $scope.selectedQuestionnaire},
            success: function(response){
                prepareQstReport(JSON.parse(response));
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.SEARCH_FAIL'));
            }
        });
    }

    // Helper function to generate list of retrieved questionnaires
    function prepareQstList(inp){
        var tmp = "";
        if(inp && (inp !== null)){
            for(var i=0; i < inp.length; i++){
            tmp = tmp + inp[i].name.replace(/["']/g, "");
            $scope.qstList.push(tmp);
            tmp ="";
            }
        }else{ // no questionnaires returned
            ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.SEARCH_FAIL'));
        }
       
    }

    //Helper function to generate list of patient receiving the specified questionnaire
    function prepareQstReport(inp){
        if(inp && (inp !== null)){
            $scope.qstReport = inp;
            for(var i=0; i< $scope.qstReport.length; i++){
                if($scope.qstReport[i].pname){
                    $scope.qstReport[i].pname = $scope.qstReport[i].pname.replace(/["']/g, "");
                }else{
                    $scope.qstReport[i].pname = "N/A";
                }
                if($scope.qstReport[i].plname){
                    $scope.qstReport[i].plname = $scope.qstReport[i].plname.replace(/["']/g, "");
                }else{
                    $scope.qstReport[i].plname = "N/A";
                }
                if($scope.qstReport[i].pdob){
                    $scope.qstReport[i].pdob = $scope.qstReport[i].pdob.replace(/["']/g, "");
                }else{
                    $scope.qstReport[i].pdob = "N/A";
                }
                if($scope.qstReport[i].psex){
                    $scope.qstReport[i].psex = $scope.qstReport[i].psex.replace(/["' ]/g, "");
                }
                if($scope.qstReport[i].pser){
                    $scope.qstReport[i].pser = $scope.qstReport[i].pser.replace(/["']/g, "");
                }
                if($scope.qstReport[i].qdate){
                    $scope.qstReport[i].qdate = $scope.qstReport[i].qdate.replace(/["']/g, "");
                }
                if($scope.qstReport[i].qcomplete){
                    $scope.qstReport[i].qcomplete = $scope.qstReport[i].qcomplete.replace(/["']/g, "");
                }else{
                    $scope.qstReport[i].qcomplete = "N/A";
                }
			}
            $scope.qstReportLength = $scope.qstReport.length;
        }else{
            ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.SEARCH_FAIL'));
        }

        prepareQstStats();
    }

    // Helper function to generate patient questionnaire statistics
    function prepareQstStats(){
        var femCount = 0;
        var malCount = 0;
        var unkCount = 0;
        var totNulls = 0;
        var tot = $scope.qstReportLength;
        var uniquePats = [];
        //initialize unique pats with first patient name
        uniquePats.push($scope.qstReport[0].pname + " , " + $scope.qstReport[0].plname);
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
            if($scope.qstReport[i].qcomplete === "N/A"){
                totNulls ++;
            }else{
                completionTimes.push(date_diff_days(new Date($scope.qstReport[i].qdate), new Date($scope.qstReport[i].qcomplete)));
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
                if(patAges[k] === "N/A"){
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
        }

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
            url: "patient-reports/get/pat-report",
            data: null,
            success: function(response){
                preparePatientReport(JSON.parse(response));
            },
            error: function(err){
                ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.DB_ERROR'));
            }
        });
    }


    // Helper function to clean patient list
    function preparePatientReport(inp){
        if(inp && (inp !== null)){
            $scope.patientReport = inp;
			for(var i = 0; i < $scope.patientReport.length; i++){
                if($scope.patientReport[i].pname){
				    $scope.patientReport[i].pname = $scope.patientReport[i].pname.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].pname = "N/A";
                }
                if($scope.patientReport[i].plname){
                    $scope.patientReport[i].plname = $scope.patientReport[i].plname.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].plname = "N/A";
                }
                if($scope.patientReport[i].psex){
                    $scope.patientReport[i].psex = $scope.patientReport[i].psex.replace(/["' ]/g, "");
                }else{
                    $scope.patientReport[i].psex = "N/A";
                }
                if($scope.patientReport[i].pser){
                    $scope.patientReport[i].pser = $scope.patientReport[i].pser.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].pser = "N/A";
                }
                if($scope.patientReport[i].pdob){
                    $scope.patientReport[i].pdob = $scope.patientReport[i].pdob.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].pdob = "N/A";
                }
                if($scope.patientReport[i].page){
                    $scope.patientReport[i].page = $scope.patientReport[i].page.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].page = "N/A";
                }
                if($scope.patientReport[i].pemail){
                    $scope.patientReport[i].pemail = $scope.patientReport[i].pemail.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].pemail = "N/A";
                }
                if($scope.patientReport[i].plang){
                    $scope.patientReport[i].plang = $scope.patientReport[i].plang.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].plang = "N/A";
                }
                if($scope.patientReport[i].preg){
                    $scope.patientReport[i].preg = $scope.patientReport[i].preg.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].preg = "N/A";
                }
                if($scope.patientReport[i].diagdesc){
                    $scope.patientReport[i].diagdesc = $scope.patientReport[i].diagdesc.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].diagdesc = "N/A";
                }
                if($scope.patientReport[i].diagdate){
                    $scope.patientReport[i].diagdate = $scope.patientReport[i].diagdate.replace(/["']/g, "");
                }else{
                    $scope.patientReport[i].diagdate = "N/A";
                }
			 }
            $scope.patientReportLength = $scope.patientReport.length;
            prepareDemoStats();


        }else{
            ErrorHandler.onError(err, $filter('translate')('PATIENTREPORT.SEARCH.SEARCH_FAIL'));
        }
        // Heper function to generate demographics statistics
        function prepareDemoStats(){
            var femCount = 0;
            var malCount = 0;
            var unkCount = 0;
            var totAge = 0;
            var nullCount = 0;
            var frCount = 0;
            $scope.demoPcntFemale = 0;
            $scope.demoPcntMale = 0;

            var diagDict = new Object(); //diagnosis tracking

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
                

                // store keys and values of diag dict for pie chart
                var diagCounts = [];
                var diagDescs = [];
                for(const [key, value] of Object.entries(diagDict)){
                    diagCounts.push(value);
                    diagDescs.push(key);
                }


            }

            Highcharts.chart('plot1', {
                chart: {
                    type: 'spline'
                },
                title:{
                    text: 'Opal Registrations Over Time'
                },
                yAxis: {
                    title: {
                        text: 'Total Registrations'
                    }
                },
                xAxis: {
                    type: 'datetime',
                    title: {
                        text: 'Time'
                    },
                },
                legend: {
                    layout: 'vertical',
                    align:'left',
                    verticalAlign: 'top',
                    x: 100,
                    y: 70,
                    floating: true,
                    borderWidth: 1
                },
                plotOptions: {
                    scatter: {
                        marker: {
                            radius: 4,
                            states: {
                                hover: {
                                    enabled: true,
                                    lineColor: 'rgb(100,100,100)'
                                }
                            }
                        }
                    }
                },
                series: [{
                    name: 'All Patients',
                    data: $scope.regPlotData
                },{
                    name: 'Female Patients',
                    data: $scope.femalePlotData
                },{
                    name: 'Male Patients',
                    data: $scope.malePlotData
                }]
            });

        }
    }

});
