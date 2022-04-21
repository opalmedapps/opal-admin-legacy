angular.module('opalAdmin.controllers.patientReports', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('patientReports', function ($scope, $rootScope, Session, ErrorHandler, MODULE, $uibModal, $filter) {

	$scope.navMenu = Session.retrieveObject('menu');
	$scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.patient];
	angular.forEach($scope.navSubMenu, function (menu) {
		menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
		menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
	});

	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 1)) !== 0);
	$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 2)) !== 0);


	$scope.foundPatient = false; //only show the report once patient is found/selected
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
	$scope.noPatientFound = false;
	$scope.generateFinished = false; //hide rpeort segments

	$scope.psnum = ""; //the selected patient identifiers for our report
	$scope.pname = "";
	$scope.pfname = "";
	$scope.psex = "";
	$scope.pemail = "";
	$scope.pramq = "";
	$scope.pmrn = "";
	$scope.plang = "";


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
			{
				field: 'description',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.DIAGNOSIS.DESC'),
				width: '40%',
				enableColumnMenu: false
			},
			{
				field: 'creationdate',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.DIAGNOSIS.DATE'),
				width: '40%',
				enableColumnMenu: false
			},
			{
				field: 'sernum',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.DIAGNOSIS.SERIAL'),
				width: '20%',
				enableColumnMenu: false
			},
		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.qstGridOptions = {
		data: 'qstReport',
		columnDefs: [
			{
				field: 'name',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.QUESTIONNAIRE.NAME'),
				width: '40%',
				enableColumnMenu: false
			},
			{
				field: 'dateadded',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.QUESTIONNAIRE.DATE'),
				width: '40%',
				enableColumnMenu: false
			},
			{
				field: 'datecompleted',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.QUESTIONNAIRE.COMPLETE'),
				width: '20%',
				enableColumnMenu: false
			},
		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.apptGridOptions = {
		data: 'apptReport',
		columnDefs: [
			{
				field: 'starttime',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.APPOINTMENT.SCHEDULED'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'dateadded',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.APPOINTMENT.DATE'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'status',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.APPOINTMENT.STATUS'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'aliasname',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.APPOINTMENT.NAME'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'aliastype',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.APPOINTMENT.TYPE'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'resourcename',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.APPOINTMENT.RESOURCE'),
				width: '20%',
				enableColumnMenu: false
			},

		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.educGridOptions = {
		data: 'educReport',
		columnDefs: [
			{
				field: 'name',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.EDUCATIONAL_MATERIAL.NAME'),
				width: '40%',
				enableColumnMenu: false
			},
			{
				field: 'materialtype',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.EDUCATIONAL_MATERIAL.TYPE'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'dateadded',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.EDUCATIONAL_MATERIAL.DATE'),
				width: '30%',
				enableColumnMenu: false
			},
			{
				field: 'readstatus',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.EDUCATIONAL_MATERIAL.STATUS'),
				width: '10%',
				enableColumnMenu: false
			},

		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.testGridOptions = {
		data: 'testReport',
		columnDefs: [
			{
				field: 'componentname',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.NAME'),
				width: '15%',
				enableColumnMenu: false
			},
			{
				field: 'unitdescription',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.UNIT'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'testdate',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.DATE'),
				width: '15%',
				enableColumnMenu: false
			},
			{
				field: 'dateadded',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.OPAL_DATE'),
				width: '15%',
				enableColumnMenu: false
			},
			{
				field: 'minnorm',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.MIN'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'testvalue',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.RESULT'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'maxnorm',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.MAX'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'abnormalflag',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.ABNORMAL'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'readstatus',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LEGACY_LAB_TESTS.STATUS'),
				width: '5%',
				enableColumnMenu: false
			},
		],
		enableFiltering: true,
		enableColumnResizing: true,
	};
	$scope.pattestGridOptions = {
		data: 'pattestReport',
		columnDefs: [
			{
				field: 'groupname',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.GROUP'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'readstatus',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.STATUS'),
				width: '5%',
				enableColumnMenu: false
			},
			{
				field: 'testname',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.NAME'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'description',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.DESC'),
				width: '12%',
				enableColumnMenu: false
			},
			{
				field: 'abnormalflag',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.ABNORMAL'),
				width: '5%',
				enableColumnMenu: false
			},
			{
				field: 'normalrange',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.RANGE'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'testvalue',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.RESULT'),
				width: '12%',
				enableColumnMenu: false
			},
			{
				field: 'datecollected',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.DATE_TEST'),
				width: '12%',
				enableColumnMenu: false
			},
			{
				field: 'resultdate',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.DATE_RESULT'),
				width: '12%',
				enableColumnMenu: false
			},
			{
				field: 'dateadded',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.LAB_TESTS.DATE_OPAL'),
				width: '12%',
				enableColumnMenu: false
			},
		],
		enableFiltering: true,
		enableColumnResizing: true,
		rowHeight: 30,
	};

	$scope.noteGridOptions = {
		data: 'noteReport',
		columnDefs: [
			{
				field: 'name',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.NOTES.TYPE'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'tablerowtitle',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.NOTES.NAME'),
				width: '40%',
				enableColumnMenu: false
			},
			{
				field: 'dateadded',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.NOTES.DATE'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'lastupdated',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.NOTES.DATE_READ'),
				width: '20%',
				enableColumnMenu: false
			},

		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.clinnoteGridOptions = {
		data: 'clinnoteReport',
		columnDefs: [
			{
				field: 'aliasexpressionname',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.CLINICAL_NOTES.TYPE'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'originalname',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.CLINICAL_NOTES.NAME_ORIG'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'finalname',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.CLINICAL_NOTES.NAME_FINAL'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'created',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.CLINICAL_NOTES.DATE_CREATED'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'approved',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.CLINICAL_NOTES.DATE_APPROVED'),
				width: '20%',
				enableColumnMenu: false
			},
		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.txteamGridOptions = {
		data: 'txteamReport',
		columnDefs: [
			{
				field: 'name',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_TEAM.NAME'),
				width: '30%',
				enableColumnMenu: false
			},
			{
				field: 'body',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_TEAM.DESC'),
				width: '45%',
				enableColumnMenu: false
			},
			{
				field: 'dateadded',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_TEAM.DATE'),
				width: '15%',
				enableColumnMenu: false
			},
			{
				field: 'readstatus',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_TEAM.STATUS'),
				width: '10%',
				enableColumnMenu: false
			},
		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.generalGridOptions = {
		data: 'generalReport',
		columnDefs: [
			{
				field: 'name',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_TEAM.NAME'),
				width: '30%',
				enableColumnMenu: false
			},
			{
				field: 'body',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_TEAM.DESC'),
				width: '45%',
				enableColumnMenu: false
			},
			{
				field: 'dateadded',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_TEAM.DATE'),
				width: '15%',
				enableColumnMenu: false
			},
			{
				field: 'readstatus',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_TEAM.STATUS'),
				width: '10%',
				enableColumnMenu: false
			},
		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.txplanGridOptions = {
		data: 'txplanReport',
		columnDefs: [
			{
				field: 'diagnosisdescription',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.DESC'),
				width: '15%',
				enableColumnMenu: false
			},
			{
				field: 'aliastype',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.TYPE'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'aliasexpressiondescription',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.DESC_EXP'),
				width: '15%',
				enableColumnMenu: false
			},
			{
				field: 'aliasname',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.NAME'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'aliasdescription',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.DESC_ALI'),
				width: '20%',
				enableColumnMenu: false
			},
			{
				field: 'taskstatus',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.TASK_STATUS'),
				width: '5%',
				enableColumnMenu: false
			},
			{
				field: 'taskstate',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.TASK_STATE'),
				width: '5%',
				enableColumnMenu: false
			},
			{
				field: 'taskdue',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.TASK_DUE'),
				width: '10%',
				enableColumnMenu: false
			},
			{
				field: 'taskcompletiondate',
				displayName: $filter('translate')('PATIENTS.REPORT.COLUMNS.TREATMENT_PLAN.TASK_COMPLETE'),
				width: '10%',
				enableColumnMenu: false
			},

		],
		enableFiltering: true,
		enableColumnResizing: true,
	};

	$scope.selectedName = "";

	// Safe apply function prevents potential '$apply already in progress' errors during execution
	$scope.safeApply = function (fn) {
		var phase = this.$root.$$phase;
		if (phase == '$apply' || phase == '$digest') {
			if (fn && (typeof (fn) === 'function')) {
				fn();
			}
		} else {
			this.$apply(fn);
		}
	};


	/**
	 *  Main search function for finding desired patient
	 *  -- Uses scope variables instead of explicit parameters
	 *  -- All ajax calls get rerouted through the main .htaccess Rewrite rules
	 */
	$scope.findPat = function () {
		if ($scope.searchName == "" && $scope.searchMRN == "" && $scope.searchRAMQ == "") {
			$scope.foundPatient = false;
		} else if ($scope.searchName) { //find by name
			$.ajax({
				type: "POST",
				url: "patient/get/patient-name",
				data: {pname: $scope.searchName},
				dataType: "json",
				success: function (response) {
					displayName(response);
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.DB_ERROR'));
				}
			});
		} else if ($scope.searchMRN) { //find by MRN
			$.ajax({
				type: "POST",
				url: "patient/get/patient-mrn",
				data: {pmrn: $scope.searchMRN},
				dataType: "json",
				success: function (response) {
					displayName(response);
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.DB_ERROR'));
				}
			});
		} else  { //find my RAMQ
			$.ajax({
				type: "POST",
				url: "patient/get/patient-ramq",
				data: {pramq: $scope.searchRAMQ},
				dataType: "json",
				success: function (response) {
					displayName(response);
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.DB_ERROR'));
				}
			});

		}
	};

	/**
	 *  Process results of ajax patient search
	 *
	 *  @param result: patient(s) info
	 *  @return
	 */
	function displayName(result) {

		$scope.safeApply(function () {
			$scope.searchResult = result;
			if ($scope.searchResult.length == 0) { //no match found for input parameter
				$scope.foundPatient = false;
				$scope.noPatientFound = true;
			} else if ($scope.searchResult.length > 1) { //found multiple patients matching search
				$scope.noPatientFound = false;
				$scope.patOptions = [];
				var tmp = "";
				var mrnList = "";
				//load each result into patOptions array for selection
				for (var i = 0; i < $scope.searchResult.length; i++) {
					if ($scope.searchResult[i].MRN.length > 0) {
						mrnList = "(MRN: ";
						for (var j = 0; j < $scope.searchResult[i].MRN.length; j++) {
							mrnList += $scope.searchResult[i].MRN[j].MRN + " (" + $scope.searchResult[i].MRN[j].hospital + "), ";
						}
						mrnList = mrnList.slice(0, -2) + ")";
					} else
						mrnList = "("+$filter('translate')('PATIENTS.REPORT.INDIVIDUAL.NO_MRN')+")";

					tmp = $scope.searchResult[i].plname + " " + $scope.searchResult[i].pname + " (" +
						($scope.searchResult[i].pramq ? $scope.searchResult[i].pramq : $filter('translate')('PATIENTS.REPORT.INDIVIDUAL.NO_RAMQ'))
						+ ") " + mrnList;
					$scope.patOptions.push(tmp);$scope.searchResult[i].name_display = tmp;
					tmp = "";
				}
			} else { //exactly one match
				$scope.noPatientFound = false;
				$scope.foundPatient = true; //display patient table
				// set selected patient identifiers
				if ($scope.searchResult[0].pname) {
					$scope.pname = $scope.searchResult[0].pname.replace(/["']/g, "");
				}
				if ($scope.searchResult[0].plname) {
					$scope.plname = $scope.searchResult[0].plname.replace(/["']/g, "");
				}
				if ($scope.searchResult[0].psnum) {
					$scope.psnum = $scope.searchResult[0].psnum.replace(/["']/g, "");
				}
				if ($scope.searchResult[0].MRN) {
					$scope.MRN = $scope.searchResult[0].MRN;
				}
				if ($scope.searchResult[0].pramq) {
					$scope.pramq = $scope.searchResult[0].pramq.replace(/["']/g, "");
				}
				if ($scope.searchResult[0].psex) {
					$scope.psex = $scope.searchResult[0].psex.replace(/["' ]/g, "");
				}
				if ($scope.searchResult[0].plang) {
					$scope.plang = $scope.searchResult[0].plang.replace(/["']/g, "");
				}
				if ($scope.searchResult[0].pemail) {
					$scope.pemail = $scope.searchResult[0].pemail.replace(/["']/g, "");
				}

				//prepare to generate full report by default
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

			}

		});

	}

	// display the selected patient (this function is called by the template after selecting a patient from the list of options)
	$scope.displaySelection = function () {

		$scope.safeApply(function () {
			$scope.noPatientFound = false;
			$scope.foundPatient = !!($scope.selectedName);
			if($scope.selectedName) {
				if ($scope.selectedName.pname) {
					$scope.pname = $scope.selectedName.pname.replace(/["']/g, "");
				}
				if ($scope.selectedName.plname) {
					$scope.plname = $scope.selectedName.plname.replace(/["']/g, "");
				}
				if ($scope.selectedName.psnum) {
					$scope.psnum = $scope.selectedName.psnum.replace(/["']/g, "");
				}
				if ($scope.selectedName.MRN) {
					$scope.MRN = $scope.selectedName.MRN;
				}
				if ($scope.selectedName.pramq) {
					$scope.pramq = $scope.selectedName.pramq.replace(/["']/g, "");
				}
				if ($scope.selectedName.psex) {
					$scope.psex = $scope.selectedName.psex.replace(/["' ]/g, "");
				}
				if ($scope.selectedName.pemail) {
					$scope.pemail = $scope.selectedName.pemail.replace(/["']/g, "");
				}
				if ($scope.selectedName.plang) {
					$scope.plang = $scope.selectedName.plang.replace(/["']/g, "");
				}
				//prepare to generate full report by default
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
			}

		});

	};

	//Reset field values and hide duplicate patient dropdown
	$scope.resetFieldValues = function () {

		$scope.safeApply(function () {
			$scope.searchName = "";
			$scope.searchMRN = "";
			$scope.searchRAMQ = "";

			$scope.noPatientFound = false;
			$scope.generateFinished = false;

			$scope.searchResult = "";
			$scope.pname = "";
			$scope.plname = "";
			$scope.psnum = "";
			$scope.MRN = "";
			$scope.pramq = "";
			$scope.psex = "";
			$scope.pemail = "";
			$scope.plang = "";

			//reset featureList
			$scope.featureList.diagnosis = false;
			$scope.featureList.appointments = false;
			$scope.featureList.questionnaires = false;
			$scope.featureList.education = false;
			$scope.featureList.testresults = false;
			$scope.featureList.pattestresults = false;
			$scope.featureList.notifications = false;
			$scope.featureList.treatplan = false;
			$scope.featureList.clinicalnotes = false;
			$scope.featureList.treatingteam = false;
			$scope.featureList.general = false;

			$scope.foundPatient = false;

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

		});
	};

	/**
	 *  Generate the desired report based on user input
	 *  @param psnum: selected patient serial number
	 *  @param featureList: truthy/falsy variables for each report segment (user selected)
	 */
	$scope.fetchData = function () {
		$.ajax({
			type: "POST",
			url: "patient/get/patient-data",
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
			dataType: "json",
			success: function (response) {
				populateTables(response);
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.DB_ERROR'));
			}
		});
	};

	/**
	 * Populate tables for display in view
	 * @param result: result of ajax query to db to retrieve selected patient records
	 * @return none
	 */
	function populateTables(result) {

		$scope.safeApply(function () {
			if (result && (result !== null)) {
				if (result.diagnosis) {
					$scope.diagReport = result.diagnosis;
					strip($scope.diagReport);
				}
				if (result.questionnaires) {
					$scope.qstReport = result.questionnaires;
					strip($scope.qstReport);
				}
				if (result.education) {
					$scope.educReport = result.education;
					strip($scope.educReport);
				}
				if (result.appointments) {
					$scope.apptReport = result.appointments;
					strip($scope.apptReport);
				}
				if (result.testresults) {
					$scope.testReport = result.testresults;
					strip($scope.testReport);
				}
				if (result.pattestresults) {
					$scope.pattestReport = result.pattestresults;
					strip($scope.pattestReport);
				}
				if (result.notes) {
					$scope.noteReport = result.notes;
					strip($scope.noteReport);
				}
				if (result.clinicalnotes) {
					$scope.clinnoteReport = result.clinicalnotes;
					strip($scope.clinnoteReport);
				}
				if (result.treatingteam) {
					$scope.txteamReport = result.treatingteam;
					for (var i = 0; i < $scope.txteamReport.length; i++) {
						$scope.txteamReport[i].body = strip($scope.txteamReport[i].body);
					}
				}
				if (result.general) {
					$scope.generalReport = result.general;
					for (var i = 0; i < $scope.generalReport.length; i++) {
						$scope.generalReport[i].body = strip($scope.generalReport[i].body);
					}
				}
				if (result.treatplan) {
					$scope.txplanReport = result.treatplan;
					strip($scope.txplanReport);
				}
				$scope.generateFinished = true; //finally we can show report segments

			} else { //something went wrong, no result recieved
				ErrorHandler.onError(err, $filter('translate')('PATIENTS.REPORT.SEARCH.SEARCH_FAIL'));
			}

		});
	}


	//Remove whitespace from input
	function strip(text) {
		return text ? String(text).replace(/<[^>]+>/gm, '') : '';
	}

});

