
//------------------------------------------------------------------------
// K.Agnew Feb 2019
//------------------------------------------------------------------------

//
// Controller for the Group Reports tab within Opal Reporting
//
app.controller('Ctrl2',function($scope, $http)
{
	$scope.db = {
		prod: true,
		preprod : false
	};

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

	$scope.qstOptions = []; //list of questionnaires found in opalDB
	$scope.selectedQuestionnaire = ""; //user selected questionnaire from qstOptions
	$scope.showQstReport = false;

	//Display variables for demographics branch
	$scope.genConsentTable = true;

	// Scope functions for educational materials branch
	$scope.getEducationalMaterialOptions = function(){

		$http.get("cgi-bin/getEducMaterials.pl", {params: {type: $scope.materialType, db:$scope.db.prod}})
		.then(function(response) {
			//store the JSON object returned in the allData variable (Promise handling)
			prepareList(response.data);
	    }, function (error){
	    	console.log(error);
	    });
	}

	function prepareList(inp){
		$scope.educList = [];
		var tmp = "";
		for(var i=0; i < inp.typeList.length; i++){
			tmp = tmp + inp.typeList[i].name.replace(/["']/g, "");
			$scope.educList.push(tmp);
			tmp = "";
		}
		$scope.displayMaterialList = true;

	}

	$scope.genEducReport = function(){
		$http.get("cgi-bin/genEducReport.pl", {params: {type: $scope.materialType, name: $scope.selectedMaterial, db:$scope.db.prod}})
		.then(function(response) {
			//store the JSON object returned in the allData variable (Promise handling)
			prepareEducReport(response.data);
	    }, function (error){
	    	console.log(error);
	    });

	}
	/**
	* 	Generate the report, prepare the raw data for presentation. Prepare all of 
	*		the report statistics
	*/
	function prepareEducReport(inp){
		if(inp === null){ //First check that we got something back
			//If input is null, we couldn't find any info for that query
			alert("No results found");
		}else{
			$scope.educReport = inp.report;
			for(var i = 0; i < $scope.educReport.length; i++){
				$scope.educReport[i].fname = $scope.educReport[i].fname.replace(/["']/g, "");
				$scope.educReport[i].lname = $scope.educReport[i].lname.replace(/["']/g, "");
				$scope.educReport[i].pdob = $scope.educReport[i].pdob.replace(/["']/g, "");
				//For some reason, the patient sex was being returned with extra spaces in the string
				//Therefore a space is added to this regex to strip away these extra characters
				$scope.educReport[i].psex = $scope.educReport[i].psex.replace(/["' ]/g, "");
				$scope.educReport[i].pser = $scope.educReport[i].pser.replace(/["']/g, "");
				$scope.educReport[i].datesent = $scope.educReport[i].datesent.replace(/["']/g, "");
				$scope.educReport[i].readflag = $scope.educReport[i].readflag.replace(/["']/g, "");
				$scope.educReport[i].page = $scope.educReport[i].page.replace(/["']/g, "");
				$scope.educReport[i].lastupdate = $scope.educReport[i].lastupdate.replace(/["']/g, "");
			}
			$scope.educReportLength = $scope.educReport.length;
			//Begin stats functions
			var totAge = 0;
			var ageDenom = 0;
			var femCount = 0;
			var malCount = 0;
			var totRead = 0;
			var daysToRead = 0;
			//Callback function returns difference in days between two Date objects
			var date_diff_indays = function(d1, d2){
				return Math.floor((Date.UTC(d2.getFullYear(), d2.getMonth(), d2.getDate()) - Date.UTC(d1.getFullYear(), d1.getMonth(), d1.getDate()) ) / (1000*60*60*24));
			}
			for(var j=0; j<$scope.educReport.length;j++){
				if($scope.educReport[j].page !== 'null'){
					totAge += parseInt($scope.educReport[j].page);
					ageDenom++;
				}

				if($scope.educReport[j].psex === "Female"){
					femCount++;
				}else if($scope.educReport[j].psex === "Male"){
					malCount++;
				}

				if($scope.educReport[j].readflag === "1"){
					totRead++;
					daysToRead += date_diff_indays(new Date($scope.educReport[j].datesent),new Date($scope.educReport[j].lastupdate));
				}
			}
			// average age stat
			$scope.educAvgAge = (totAge/ageDenom).toFixed(2);
			// gender breakdown
			$scope.educFemPcnt = ((femCount/$scope.educReportLength)*100).toFixed(2);
			$scope.educMalPcnt = ((malCount/$scope.educReportLength)*100).toFixed(2);
			$scope.educUnkPcnt = (100 - $scope.educMalPcnt - $scope.educFemPcnt).toFixed(2);
			// % materials read
			$scope.educReadPcnt = ((totRead/$scope.educReportLength)*100).toFixed(2);
			// average time to read among read materials
			$scope.educAvgDaysToRead = (daysToRead/totRead).toFixed(2);
		}
	}
	//Scope functions for questionnaires branch
	//function to return a list of questionnaires found in opal DB
	$scope.getQuestionnaireOptions = function(){
		$http.get("cgi-bin/getQuestionnaires.pl", {params: {db:$scope.db.prod}})
		.then(function(response) {
			//store the JSON object returned in the allData variable (Promise handling)
			prepareQstList(response.data);
	    }, function (error){
	    	console.log(error);
	    });
	}
	//strip excess quotation marks and generate list of questionnaire options for user selection
	function prepareQstList(inp){
		var tmp = "";
		for(var i =0; i < inp.report.length; i++){
			tmp = tmp + inp.report[i].name.replace(/["']/g, "");
			$scope.qstOptions.push(tmp);
			tmp = "";
		}
	}
	//use user selected questionnaire to  generate the report
	$scope.genQstReport = function(){
		$http.get("cgi-bin/genQstReport.pl", {params: {name: $scope.selectedQuestionnaire, db:$scope.db.prod}})
		.then(function(response) {
			//store the JSON object returned in the allData variable (Promise handling)
			preparePatientQst(response.data);
	    }, function (error){
	    	console.log(error);
	    });
	}
	//build list of patients receiving the specified questionnaire, strip quotation marks 
	function preparePatientQst(inp){
		$scope.qstReport = [];
		if(inp === null){ //First check that we got something back
			//If input is null, we couldn't find any info for that query
			alert("No results found");
		}else{
			$scope.qstReport = inp.report;
			for(var i = 0; i < $scope.qstReport.length; i++){
				$scope.qstReport[i].fname = $scope.qstReport[i].fname.replace(/["']/g, "");
				$scope.qstReport[i].lname = $scope.qstReport[i].lname.replace(/["']/g, "");
				$scope.qstReport[i].pdob = $scope.qstReport[i].pdob.replace(/["']/g, "");
				$scope.qstReport[i].psex = $scope.qstReport[i].psex.replace(/["' ]/g, "");
				$scope.qstReport[i].pser = $scope.qstReport[i].pser.replace(/["']/g, "");
				$scope.qstReport[i].datesent = $scope.qstReport[i].datesent.replace(/["']/g, "");
				$scope.qstReport[i].datecomplete = $scope.qstReport[i].datecomplete.replace(/["']/g, "");
			}
			//generate statistics
			$scope.qstReportLength = $scope.qstReport.length;
			var tot = $scope.qstReportLength;
			//temp variables for patient counting / gender
			var femCount = 0;
			var malCount = 0;
			var uniquePats = [];
			//temp variables for patient age stats
			var uniquePatDobs = [];
			var curDate = new Date();
			var date_diff_indays = function(d1, d2){
				return Math.floor((Date.UTC(d2.getFullYear(), d2.getMonth(), d2.getDate()) - Date.UTC(d1.getFullYear(), d1.getMonth(), d1.getDate()) ) / (1000*60*60*24));
			}
			//temp variable for % completion stat
			var totNulls = 0;
			//temp variable for avg completion time stat
			var completionTimes = [];
			for(i=0; i<$scope.qstReport.length; i++){
				//gender breakdown
				if($scope.qstReport[i].psex === "Female"){
					femCount += 1;
				}else if($scope.qstReport[i].psex === "Male"){
					malCount += 1;
				}
				//unique patient counter and update uniquePatient Age while we have the correct spot
				if(!uniquePats.includes($scope.qstReport[i].lname)){
					uniquePats.push($scope.qstReport[i].lname);
					uniquePatDobs.push(new Date($scope.qstReport[i].pdob));
				}
				//% completion and completion time
				if($scope.qstReport[i].datecomplete === "null"){
					totNulls += 1;
				}else{
					completionTimes.push(date_diff_indays(new Date($scope.qstReport[i].datesent), new Date($scope.qstReport[i].datecomplete)));
				}
			}
			//gender stats
			$scope.pcntFemale = ((femCount/tot)*100).toFixed(2);
			$scope.pcntMale = ((malCount/tot)*100).toFixed(2);
			$scope.pcntUnk = (100 - $scope.pcntMale - $scope.pcntFemale).toFixed(2);
			$scope.numUniquePats = uniquePats.length;
			//age stats
			var patAges = [];//holds the age, in days, of each unique patient in the report
			for(i=0; i<uniquePatDobs.length; i++){
				patAges.push(date_diff_indays(uniquePatDobs[i],curDate));
			}
			var sum= 0;
			var invalids = 0;
			for(i=0; i<patAges.length; i++){
				if(isNaN(patAges[i])){
					//skip this one
					invalids += 1;
				}else{
					sum+= patAges[i];
				}
			}
			var avg = sum/(patAges.length-invalids);
			$scope.avgPatAge = (avg/365).toFixed(2);

			//% completion stats
			$scope.pcntCompleted = (((tot-totNulls)/tot)*100).toFixed(2);
			sum = 0;
			for(i=0; i<completionTimes.length; i++){
				sum += completionTimes[i];
			}
			$scope.avgCompletionTime = (sum/completionTimes.length).toFixed(2);
		}
	}
	//Scope functions for demographics branch
	$scope.getPatientList = function(){
		$http.get("cgi-bin/getPatientList.pl", {params: {db:$scope.db.prod}})
		.then(function(response) {
			//store the JSON object returned in the allData variable (Promise handling)
			preparePatientList(response.data);
	    }, function (error){
	    	console.log(error);
	    });

	}

	function preparePatientList(inp){
		if(inp === null){ //First check that we got something back
			//If input is null, we couldn't find any info for that query
			alert("No results found");
		}else{
			$scope.patientList = inp.report;
			for(var i = 0; i < $scope.patientList.length; i++){
				$scope.patientList[i].fname = $scope.patientList[i].fname.replace(/["']/g, "");
				$scope.patientList[i].lname = $scope.patientList[i].lname.replace(/["']/g, "");
				$scope.patientList[i].pdob = $scope.patientList[i].pdob.replace(/["']/g, "");
				$scope.patientList[i].psex = $scope.patientList[i].psex.replace(/["' ]/g, "");
				$scope.patientList[i].pser = $scope.patientList[i].pser.replace(/["']/g, "");
				$scope.patientList[i].age = $scope.patientList[i].age.replace(/["']/g, "");
				$scope.patientList[i].consentexp = $scope.patientList[i].consentexp.replace(/["']/g, "");
				$scope.patientList[i].regdate = $scope.patientList[i].regdate.replace(/["']/g, "");
				$scope.patientList[i].lang = $scope.patientList[i].lang.replace(/["']/g, "");
				$scope.patientList[i].diagdate = $scope.patientList[i].diagdate.replace(/["']/g, "");
				$scope.patientList[i].diagdesc = $scope.patientList[i].diagdesc.replace(/["']/g, "");
			}
			$scope.patientListLength = $scope.patientList.length;
			//Begin stats section
			$scope.demopcntFemale = 0;
			var dfemCount = 0;
			var dmalCount = 0;

			var totAge = 0;
			var nullCount = 0;

			var frCount = 0;

			var regDates = [];
			var totalRegistrations = [];
			var tmpC = 1;

			//consent form expiration list
			var today = new Date();
			var threemonthes = new Date(); //3 monthes from today
			threemonthes.setMonth(today.getMonth()+3);
			var tempDate = "";
			var table = document.getElementById("consentTable"); //get table in document
			//we will go ahead and clear the table before re-populating
				//this only comes up if you reload the Diagnosis tab multiple times,
				//in which case you would get a bigger and bigger table of repeated names
			

			//While JS doesnt explicity have dictionary datatypes we can make something similar easily
			var dict = new Object(); //use this to track diagnoses

			for(var i = 0; i < $scope.patientListLength; i++){
				//male vs female stats
				if($scope.patientList[i].psex === "Female"){
					dfemCount += 1;
				}else if ($scope.patientList[i].psex === "Male"){
					dmalCount += 1;
				}
				//avg patient age stats
				if(($scope.patientList[i].age > 0) && ($scope.patientList[i].age !== "null")){
					totAge += parseInt($scope.patientList[i].age);
				}else{
					nullCount++;
				}
				//french english stats
				if($scope.patientList[i].lang === "FR"){
					frCount++;
				}
				//registration date stats
				regDates.push(new Date($scope.patientList[i].regdate));
				totalRegistrations.push(tmpC);
				tmpC++;

				//Diagnosis breakdown stats
				if($scope.patientList[i].diagdesc in dict){
					dict[$scope.patientList[i].diagdesc]++;
				}else{
					dict[$scope.patientList[i].diagdesc] = 1;
				}

				tempDate = new Date($scope.patientList[i].consentexp);
				if($scope.genConsentTable){
					if(tempDate < threemonthes){
						var tr = table.insertRow(-1); //append to document table
						if(tempDate < today){
							tr.style.color = "red";
						}
						tr.insertCell(-1).appendChild(document.createTextNode($scope.patientList[i].fname));
						tr.insertCell(-1).appendChild(document.createTextNode($scope.patientList[i].lname));
						tr.insertCell(-1).appendChild(document.createTextNode($scope.patientList[i].consentexp));
					}
				}
			}
			$scope.genConsentTable = false;
			
			$scope.demopcntFemale = ((dfemCount/$scope.patientListLength)*100).toFixed(2);
			$scope.demopcntMale = ((dmalCount/$scope.patientListLength)*100).toFixed(2);
			$scope.demopcntUnk = (100 - $scope.demopcntFemale - $scope.demopcntMale).toFixed(2);

			$scope.demoavgPatAge = (totAge/($scope.patientListLength-nullCount)).toFixed(2);

			$scope.demopcntFrench = ((frCount/$scope.patientListLength)*100).toFixed(2);
			$scope.demopcntEnglish = (100 - $scope.demopcntFrench).toFixed(2);

			//Since the dates arent in order for some reason, we need to sort them
			//this is a callback to be provided to the array sort method
			var date_sort_asc = function(date1,date2){
				if(date1 > date2) return 1;
				if(date1 < date2) return -1;
				return 0;
			};
			regDates.sort(date_sort_asc);



			//iterate over the diagnosis dictionary to put in pie chart
			var diagCounts = [];
			var diagDescs = [];
			for(const [key, value] of Object.entries(dict)){
				diagCounts.push(value);
				diagDescs.push(key);
			}

			//Plotly variables for plotting registrations dates
			var trace1 = {
					x: regDates,
					y: totalRegistrations,
					type: 'scatter'
				};
			var data = [trace1];
			//pie chart data
			var data2 = [{
				values: diagCounts,
				labels: diagDescs,
				type: 'pie'
			}];
			//registrations plot
			var layout = {
				title: 'Opal registrations over time',
				plot_bgcolor: 'linen',
				paper_bgcolor: '#FFF3',
				xaxis:{
					title: 'Time'
				},
				yaxis:{
					title: 'Total Registrations'
				}
			};
			//pie chart 
			var layout2 = {
				title: 'Diagnosis Breakdown',
				plot_bgcolor: 'linen',
				paper_bgcolor: '#FFF3',
				legend:{
					x: 1,
					y: 0
				},
			};

			Plotly.newPlot('plotDiv1',data, layout);

			Plotly.newPlot('plotDiv2',data2, layout2);
			
		}
	}

});