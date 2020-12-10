angular.module('opalAdmin.controllers.groupReports', ['ngAnimate', 'ui.bootstrap']).

controller('groupReports', function($scope, Session, ErrorHandler, MODULE){
   
    // Three main categories of group reporting
    //  Ctrl+F to 'EDUCATIONAL', 'QUESTIONNAIRES', 'DEMOGRAPHICS'  to see relevant code
    $scope.category = {
		education : true,
		questionnaire : false,
		demographics : false,
    };
    console.log("Control passed to groupReports.js");

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

	$scope.updateColours = function(){
		var tab = document.getElementById("tab");
		var rep = document.getElementById("GroupReport");
		if($scope.db.prod){
			tab.style.backgroundColor = "palegreen";
			rep.style.border = "thick dotted palegreen";
		}else{
			tab.style.backgroundColor = "paleturquoise";
			rep.style.border = "thick dotted paleturquoise";
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
        $.ajax({
            type: "POST",
            url: "", //TODO url
            data: $scope.materialType,
            success: function(response){
                console.log(response);
                prepareEducList(response.data);
            },
            error: function(err){
                console.log(err)
                //ErrorHandler.onError() TODO
            }
        });
    }

    /**
     * Generate selected educational material report
     */
    $scope.genEducReport = function(){
        $.ajax({
            type: "POST",
            url: "", //TODO url
            data: {
                type: $scope.materialType, 
                name: $scope.selectedMaterial},
            success: function(response){
                console.log(response);
                prepareEducReport(response.data);
            },
            error: function(err){
                console.log(err)
                //ErrorHandler.onError() TODO
            }       
         });
    }

    // Helper function to prepare educational material list for selection
    function prepareEducList(inp){
        var tmp = "";
        if(inp && (inp !== null)){
            for(var i=0; i < inp.typeList.length; i++){
			tmp = tmp + inp.typeList[i].name.replace(/["']/g, "");
			$scope.educList.push(tmp);
			tmp = "";
            }
            $scope.displayMaterialList = true;
        }else{
            console.log("Something went wrong when preparing education material list");
            // ErrorHandler TODO
        }
		

    }
    
    // Helper function prepare educational material report for display
    function prepareEducReport(inp){
        if(inp && (inp !== null)){
            $scope.educReport = inp;
            for(var i = 0; i< $scope.educReport.length; i++){
                // TODO process educReport and strip whitespace and quotation marks
            }
            $scope.educReportLength = $scope.educReport.length;




        }else{ //error, no result returned
            console.log("No educational material found");
            // ErrorHandler TODO   
        }

    }

    // Helper function to prepare educational material statistics for display
    function prepareEducStats(){
        var totAge, ageDenom, femCount, malCount, totRead, daysToRead;
        var date_diff_days = function(d1, d2){
            return Math.floor((Date.UTC(d2.getFullYear(), d2.getMonth(), d2.getDate()) - Date.UTC(d1.getFullYear(), d1.getMonth(), d1.getDate()) ) / (1000*60*60*24));
        }
        for(var i = 0; i < $scope.educReportLength; i++){
            if($scope.educReport[i].page !== 'null'){ //TODO complete when educReport format established
                totAge += parseInt($scope.educReport[i].page);
                ageDenom++;
            }



        }

        //average age
        $scope.educAvgAge = (totAge/ageDenom).toFixed(2);
        //gender breakdown
        $scope.educMalPcnt = ((malCount/$scope.educReportLength)*100).toFixed(2);
        $scope.educFemPcnt = ((femCount/$scope.educReportLength)*100).toFixed(2);
        $scope.educUnkPcnt = (100-$scope.educMalPcnt-$scope.educFemPcnt).toFixed(2);
        // % materials read
        $scope.educReadPcnt = ((totRad/$scope.educReportLength)*100).toFixed(2);
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
        $.ajax({
            type:"POST",
            url:"",
            data: null,
            success: function(response){
                console.log(response);
                prepareQstList(response.data);
            },
            error: function(err){
                console.log(err)
                //ErrorHandler.onError() TODO
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
            url: "",
            data: $scope.selectedQuestionnaire,
            success: function(response){
                console.log(response);
                prepareQstReport(response.data);
            },
            error: function(err){
                console.log(err);
                // ErrorHandler TODO
            }
        });
    }

    // Helper function to generate list of retrieved questionnaires
    function prepareQstList(inp){
        var tmp = "";
        if(inp && (inp !== null)){
             for(var i=0; i < inp.report.length; i++){
            tmp = tmp + inp.report[i].name.replace(/["']/g, "");
            $scope.qstList.push(tmp);
            tmp ="";
            }
        }else{ // no questionnaires returned
            console.log("No questionnaires found in DB");
            // ErrorHandler TODO
        }
       
    }

    //Helper function to generate list of patient receiving the specified questionnaire
    function prepareQstReport(inp){
        if(inp && (inp !== null)){
            $scope.qstReport = inp.report;
            for(var i=0; i< $scope.qstReport.length; i++){
                $scope.qstReport[i].fname = $scope.qstReport[i].fname.replace(/["']/g, "");
				$scope.qstReport[i].lname = $scope.qstReport[i].lname.replace(/["']/g, "");
				$scope.qstReport[i].pdob = $scope.qstReport[i].pdob.replace(/["']/g, "");
				$scope.qstReport[i].psex = $scope.qstReport[i].psex.replace(/["' ]/g, "");
				$scope.qstReport[i].pser = $scope.qstReport[i].pser.replace(/["']/g, "");
				$scope.qstReport[i].datesent = $scope.qstReport[i].datesent.replace(/["']/g, "");
				$scope.qstReport[i].datecomplete = $scope.qstReport[i].datecomplete.replace(/["']/g, "");
            }
            $scope.qstReportLength = $scope.qstReport.length;
        }else{
            // ErrorHandler TODO
        }

        prepareQstStats();
    }

    // Helper function to generate patient questionnaire statistics
    function prepareQstStats(){
        var femCount, malCount, unkCount, totNulls;
        var tot = $scope.qstReportLength;
        var uniquePats = [];
        var uniquePatDobs = [];
        var completionTimes = [];
        var date_diff_days = function(d1, d2){
            return Math.floor((Date.UTC(d2.getFullYear(), d2.getMonth(), d2.getDate()) - Date.UTC(d1.getFullYear(), d1.getMonth(), d1.getDate()) ) / (1000*60*60*24));
        }

        for(var i = 0; i< tot; i++){
            // gender breakdown
            if($scope.qstReport[i].psex === "Female"){
                femCount++;
            }else if($scope.qstReport[i].psex === "Male"){
                malCount++;
            }else{
                unkCount++;
            }

            //unique patient count
            if(!uniquePats.includes($scope.qstReport[i].lname)){
                uniquePats.push($scope.qstReport[i].lname);
                uniquePatDobs.push(new Date($scope.qstReport[i].pdob));
            }

            // %completion and completion time
            if($scope.qstReport[i].datecomplete === "null"){
                totNulls ++;
            }else{
                completionTimes.push(date_diff_days(new Date($scope.qstReport[i].datesent), new Date($scope.qstReport[i].datecomplete)));
            }

            // gender statistics
            $scope.qstPcntFemale = ((femCount/tot)*100).toFixed(2);
			$scope.qstPcntMale = ((malCount/tot)*100).toFixed(2);
			$scope.qstPcntUnk = (100 - $scope.pcntMale - $scope.pcntFemale).toFixed(2);
			$scope.qstUniquePats = uniquePats.length;
            //age stats
            var sum, invalids, avg;
            var patAges = [];
            for(var i = 0; i < uniquePatDobs.length; i++){
                patAges.push(date_diff_days(uniquePatDobs[i],new Date()));
            }
            for(var i = 0; i<patAges.length; i++){
                if(isNaN(patAges[i])){
                    invalids++;
                }else{
                    sum += patAges[i];
                }
            }
            avg = sum/(patAges.length - invalids);
            $scope.qstAvgAge = (avg/365).toFixed(2);

            // $ completion stats

            $scope.qstPcntCompleted = (((tot-totNulls)/tot)*100).toFixed(2);
            sum = 0;
            for(var i = 0; i < completionTimes.length; i++){
                sum += completionTimes[i];
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
    $scope.genpatientReport = function(){
        $.ajax({
            type: "POST",
            url: "",
            data: null,
            success: function(response){
                console.log(response);
                preparePatientReport(response.data);
            },
            error: function(err){
                console.log("Error occured retrieving patient list ");
                // Erorhandler TODO
            }
        });
    }


    // Helper function to clean patient list
    function preparePatientReport(inp){
        if(inp && (inp !== null)){
            $scope.patientReport = inp.report;
			for(var i = 0; i < $scope.patientReport.length; i++){
				$scope.patientReport[i].fname = $scope.patientReport[i].fname.replace(/["']/g, "");
				$scope.patientReport[i].lname = $scope.patientReport[i].lname.replace(/["']/g, "");
				$scope.patientReport[i].pdob = $scope.patientReport[i].pdob.replace(/["']/g, "");
				$scope.patientReport[i].psex = $scope.patientReport[i].psex.replace(/["' ]/g, "");
				$scope.patientReport[i].pser = $scope.patientReport[i].pser.replace(/["']/g, "");
				$scope.patientReport[i].age = $scope.patientReport[i].age.replace(/["']/g, "");
				$scope.patientReport[i].consentexp = $scope.patientReport[i].consentexp.replace(/["']/g, "");
				$scope.patientReport[i].regdate = $scope.patientReport[i].regdate.replace(/["']/g, "");
				$scope.patientReport[i].lang = $scope.patientReport[i].lang.replace(/["']/g, "");
				$scope.patientReport[i].diagdate = $scope.patientReport[i].diagdate.replace(/["']/g, "");
				$scope.patientReport[i].diagdesc = $scope.patientReport[i].diagdesc.replace(/["']/g, "");
            }
            $scope.patientReportLength = $scope.patientReport.length;

            prepareDemoStats();


        }else{
            //ErrorHandler TODO
        }
        // Heper function to generate demographics statistics
        function prepareDemoStats(){
            var femCount, malCount, unkCount, totAge, nullCount, frCount;
            $scope.demoPcntFemale = 0;
            $scope.demoPcntMale = 0;
            var regDates = [];
            var totalRegistrations = [];
            var tmpC = 1;

            var diagDict = new Object(); //diagnosis tracking
            //consent form expiration TODO: is this still relevant?

            for(var i = 0; i< $scope.patientReportLength; i++){
                // male/female demgraphics
                if($scope.patientReport[i].psex === "Female"){
                    femCount++;
                }else if($scope.patientReport[i].psex === "Male"){
                    malCount++;
                }else{
                    unkCount++;
                }

                //avg patient age
                if($scope.patientReport[i].age){
                    totAge += parseInt($scope.patientReport[i].age);
                }else{
                    nullCount++;
                }

                // french/english
                if($scope.patientReport[i].lang === "FR"){
                    frCount++;
                }

                //registration date tracking (to be plotted)
                regDates.push(new Date($scope.patientReport[i].regdate));
                totalRegistrations.push(tmpC);
                tmpC++;

                // diagnosis breakdown
                if($scope.patientReport[i].diagdesc in diagDict){
                    diagDict[$scope.patientReport[i].diagdesc]++;
                }else{
                    diagDict[$scope.patientReport[i].diagdesc] = 1;
                }

                // TODO more consent form stuff appears here in Ctrl2

                $scope.demoPcntFemale = ((femCount/$scope.patientReportLength)*100).toFixed(2);
                $scope.demoPcntMale = ((malCount/$scope.patientReportLength)*100).toFixed(2);
                $scope.demoPcntUnk = ((unkCount/$scope.patientReportLength)*100).toFixed(2);

                $scope.demoAvgAge = (totAge/($scope.patientReportLength-nullCount)).toFixed(2);
                $scope.demoPcntFrench = ((frCount/$scope.patientReportLength)*100).toFixed(2);
                $scope.demoPcntEnglish = (100-$scope.demoPcntFrench).toFixed(2);
                
                //callback function provided to sort()
                var date_sort_asc = function(date1, date2){
                    if(date1 > date2) return 1;
                    if(date1 < date2) return -1;
                    return 0;
                }
                //sort dates
                regDates.sort(date_sort_asc);

                // store keys and values of diag dict for pie chart
                var diagCounts = [];
                var diagDescs = [];
                for(const [key, value] of Object.entries(diagDict)){
                    diagCounts.push(value);
                    diagDescs.push(key);
                }

                // TODO complete plotly functions for display using variables above;


            }

        }
    }







    




});
