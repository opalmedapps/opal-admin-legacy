angular.module('opalAdmin.controllers.patient.administration', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('patient.administration', function ($scope, $rootScope, Session, ErrorHandler, MODULE, $uibModal, $filter) {

	$scope.navMenu = Session.retrieveObject('menu');
	$scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.patient_administration];
	angular.forEach($scope.navSubMenu, function (menu) {
		menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
		menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
	});

	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient_administration]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient_administration]) & (1 << 1)) !== 0);
	$scope.foundPatient = false; //only show the report once patient is found/selected

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
	$scope.paccess = "";
	$scope.pramq = "";
	$scope.pmrn = "";
	$scope.plang = "";
	$scope.puid = "";

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

	$scope.bannerMessage = "";
	// Function to show page banner
	$scope.showBanner = function () {
		$(".bannerMessage").slideDown(function () {
			setTimeout(function () {
				$(".bannerMessage").slideUp();
			}, 3000);
		});
	};

	// Function to set banner class
	$scope.setBannerClass = function (classname) {
		// Remove any classes starting with "alert-"
		$(".bannerMessage").removeClass(function (index, css) {
			return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
		});
		// Add class
		$(".bannerMessage").addClass('alert-' + classname);
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
				url: "patient-administration/get/patient-name",
				data: {pname: $scope.searchName, language: $scope.currentUser.language},
				dataType: "json",
				success: function (response) {
					displayName(response);
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.MENU.DB_ERROR'));
				}
			});
		} else if ($scope.searchMRN) { //find by MRN
			$.ajax({
				type: "POST",
				url: "patient-administration/get/patient-mrn",
				data: {pmrn: $scope.searchMRN, language: $scope.currentUser.language},
				dataType: "json",
				success: function (response) {
					displayName(response);
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.MENU.DB_ERROR'));
				}
			});
		} else  { //find my RAMQ
			$.ajax({
				type: "POST",
				url: "patient-administration/get/patient-ramq",
				data: {pramq: $scope.searchRAMQ, language: $scope.currentUser.language},
				dataType: "json",
				success: function (response) {
					displayName(response);
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.MENU.DB_ERROR'));
				}
			});

		}
	};

	/**
	 *  Function for refresh the current patient information in the right panel.
	 */
	$scope.refreshPat = function () {
		$.ajax({
			type: "POST",
			url: "patient-administration/get/patient-ramq",
			data: {pramq: $scope.pramq, language: $scope.currentUser.language},
			dataType: "json",
			success: function (response) {
				refreshDisplay(response);
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('PATIENT_ADMINISTRATION.MENU.DB_ERROR'));
			}
		});
	}

	/**
	 *  Process results of ajax patient updated data.
	 *
	 *  @param result: patient(s) info
	 *  @return
	 */
	function refreshDisplay(result) {

		$scope.safeApply(function () {
			$scope.searchResult.forEach(function(patient){
				if(patient.pramq == result[0].pramq) {
					if (result[0].pemail) {
						patient.pemail = result[0].pemail.replace(/["']/g, "");
					}
					if (result[0].paccess) {
						patient.paccess = result[0].paccess.replace(/["']/g, "");
					}
				}
			});
			if($scope.searchResult.length > 1) {
				$scope.selectedName = result[0];
				$scope.displaySelection();
			}
			else {
				if (result[0].pemail) {
					$scope.pemail = result[0].pemail.replace(/["']/g, "");
				}
				if (result[0].paccess) {
					$scope.paccess = result[0].paccess.replace(/["']/g, "");
				}
			}
		});
	}

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
						mrnList = "("+$filter('translate')('PATIENT_ADMINISTRATION.MENU.NO_MRN')+")";

					tmp = $scope.searchResult[i].plname + " " + $scope.searchResult[i].pname + " (" +
						($scope.searchResult[i].pramq ? $scope.searchResult[i].pramq : $filter('translate')('PATIENT_ADMINISTRATION.MENU.NO_RAMQ'))
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
				if ($scope.searchResult[0].paccess) {
					$scope.paccess = $scope.searchResult[0].paccess.replace(/["']/g, "");
				}
				if ($scope.searchResult[0].puid) {
					$scope.puid = $scope.searchResult[0].puid.replace(/["']/g, "");
				}

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
				if ($scope.selectedName.paccess) {
					$scope.paccess = $scope.selectedName.paccess.replace(/["']/g, "");
				}
				if ($scope.selectedName.plang) {
					$scope.plang = $scope.selectedName.plang.replace(/["']/g, "");
				}
				if ($scope.selectedName.puid) {
					$scope.puid = $scope.selectedName.puid.replace(/["']/g, "");
				}
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
			$scope.paccess = "";
			$scope.plang = "";
			$scope.puid = "";

			$scope.foundPatient = false;

		});
	};

	//Function to update email
	$scope.updateEmail = function(appointment) {
		$scope.currentAppointment = appointment;
		var modalInstance = $uibModal.open({
			templateUrl: 'templates/patient-administration/update.email.html',
			controller: 'update.email',
			scope: $scope,
			windowClass: 'customModal',
			backdrop: 'static',
		});

		modalInstance.result.then(function () {
			$scope.refreshPat();
		});
	};

	//Function to update password
	$scope.updatePassword = function(appointment) {
		$scope.currentAppointment = appointment;
		var modalInstance = $uibModal.open({
			templateUrl: 'templates/patient-administration/update.password.html',
			controller: 'update.password',
			scope: $scope,
			windowClass: 'customModal',
			backdrop: 'static',
		});

		modalInstance.result.then(function () {
			$scope.refreshPat();
		});
	};

	//Function to update access level
	$scope.updateAccessLevel = function(appointment) {
		$scope.currentAppointment = appointment;
		var modalInstance = $uibModal.open({
			templateUrl: "templates/patient-administration/update.accessLevel.html",
			controller: 'update.accessLevel',
			scope: $scope,
			windowClass: 'customModal',
			backdrop: 'static',
		});

		modalInstance.result.then(function () {
			$scope.refreshPat();
		});
	};

	//Function to update security questions
	$scope.updateSecurityQuestion = function(appointment) {
		$scope.currentAppointment = appointment;
		var modalInstance = $uibModal.open({
			templateUrl: "templates/patient-administration/update.securityAnswer.html",
			controller: 'update.securityAnswer',
			scope: $scope,
			windowClass: 'customModal',
			backdrop: 'static',
		});

		modalInstance.result.then(function () {
			$scope.refreshPat();
		});
	};

});
