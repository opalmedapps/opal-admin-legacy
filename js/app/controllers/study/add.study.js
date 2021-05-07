angular.module('opalAdmin.controllers.study.add', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	 * Add Diagnosis Translation Page controller
	 *******************************************************************************/
	controller('study.add', function ($scope, $filter, $uibModal, $state, $locale, studyCollectionService, Session, ErrorHandler) {
		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		$scope.showAssigned = false;
		$scope.hideAssigned = false;
		$scope.language = Session.retrieveObject('user').language;
		$scope.patientsList = [];
		$scope.questionnaireList = [];
		$scope.consentFormList = [];
		$scope.filter = $filter('filter');

		$scope.readyToSend = {
			code: "",
			title_EN: "",
			title_FR: "",
			description_EN: "",
			description_FR: "",
			investigator: "",
			investigator_email: "",
			investigator_phone: "",
			investigator_phoneExt: "",
			start_date: "",
			end_date: "",
			patients: [],
			questionnaire: [],
			consent_form: "",
		};

		$scope.toSubmit = {
			details: {
				code: "",
			},
			title_desc: {
				title_EN: "",
				title_FR: "",
				description_EN: "",
				description_FR: "",
			},
			investigator: {
				name: "",
				email: "",
				phone: "",
				phoneExt: "",
			},
			dates: {
				start_date: "",
				end_date: "",
			},
			patients: [],
			questionnaire: [],
			consent_form: {
				id: ""
			},
		};

		$scope.validator = {
			details: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			title_desc: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			investigator: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			consent_form: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			dates: {
				completed: false,
				mandatory: false,
				valid: true,
			},
			patients: {
				completed: false,
				mandatory: false,
				valid: true,
			},
			questionnaire: {
				completed: false,
				mandatory: false,
				valid: true,
			}
		};

		$scope.leftMenu = {
			details: {
				display: false,
				open: false,
				preview: false,
			},
			title_desc: {
				display: false,
				open: false,
				preview: false,
			},
			investigator: {
				display: false,
				open: false,
				preview: false,
			},
			dates: {
				display: false,
				open: false,
				preview: false,
			},
			patients: {
				display: false,
				open: false,
				preview: false,
			},
			questionnaire: {
				display: false,
				open: false,
				preview: false,
			},
			consent_form: {
				display: false,
				open: false,
				preview: false,
			},
		};

		var arrValidationInsert = [
			$filter('translate')('STUDY.VALIDATION.CODE'),
			$filter('translate')('STUDY.VALIDATION.TITLE_EN'),
			$filter('translate')('STUDY.VALIDATION.TITLE_FR'),
			$filter('translate')('STUDY.VALIDATION.DESCRIPTION_EN'),
			$filter('translate')('STUDY.VALIDATION.DESCRPIPTION_FR'),
			$filter('translate')('STUDY.VALIDATION.INVESTIGATOR'),
			$filter('translate')('STUDY.VALIDATION.INVESTIGATOR_PHONE'),
			$filter('translate')('STUDY.VALIDATION.INVESTIGATOR_EMAIL'),
			$filter('translate')('STUDY.VALIDATION.START_DATE'),
			$filter('translate')('STUDY.VALIDATION.END_DATE'),
			$filter('translate')('STUDY.VALIDATION.DATE_RANGE'),
			$filter('translate')('STUDY.VALIDATION.PATIENTS'),
			$filter('translate')('STUDY.VALIDATION.QUESTIONNAIRE'),
			$filter('translate')('STUDY.VALIDATION.CONSENT'),
			$filter('translate')('STUDY.VALIDATION.PATIENT_CONSENT'),
			$filter('translate')('STUDY.VALIDATION.INVESTIGATOR_PHONE_EXT'),
			$filter('translate')('STUDY.VALIDATION.ID'),
		];

		$scope.toolbar = [
			['h1', 'h2', 'h3', 'p'],
			['bold', 'italics', 'underline', 'ul', 'ol'],
			['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
			['html', 'insertLink']
		];

		// Date format for start and end frequency dates
		$scope.format = 'yyyy-MM-dd';
		$scope.dateOptionsStart = {
			formatYear: "'yy'",
			startingDay: 0,
			minDate: null,
			maxDate: null
		};
		$scope.dateOptionsEnd = {
			formatYear: "'yy'",
			startingDay: 0,
			minDate: null,
			maxDate: null
		};

		$locale["DATETIME_FORMATS"]["SHORTDAY"] = [
			$filter('translate')('DATEPICKER.SUNDAY_S'),
			$filter('translate')('DATEPICKER.MONDAY_S'),
			$filter('translate')('DATEPICKER.TUESDAY_S'),
			$filter('translate')('DATEPICKER.WEDNESDAY_S'),
			$filter('translate')('DATEPICKER.THURSDAY_S'),
			$filter('translate')('DATEPICKER.FRIDAY_S'),
			$filter('translate')('DATEPICKER.SATURDAY_S')
		];

		$locale["DATETIME_FORMATS"]["MONTH"] = [
			$filter('translate')('DATEPICKER.JANUARY'),
			$filter('translate')('DATEPICKER.FEBRUARY'),
			$filter('translate')('DATEPICKER.MARCH'),
			$filter('translate')('DATEPICKER.APRIL'),
			$filter('translate')('DATEPICKER.MAY'),
			$filter('translate')('DATEPICKER.JUNE'),
			$filter('translate')('DATEPICKER.JULY'),
			$filter('translate')('DATEPICKER.AUGUST'),
			$filter('translate')('DATEPICKER.SEPTEMBER'),
			$filter('translate')('DATEPICKER.OCTOBER'),
			$filter('translate')('DATEPICKER.NOVEMBER'),
			$filter('translate')('DATEPICKER.DECEMBER')
		];

		$scope.totalSteps = 0;
		$scope.completedSteps = 0;
		$scope.formReady = false;

		/* Function for the "Processing..." dialog */
		var processingModal;
		$scope.showProcessingModal = function () {
			processingModal = $uibModal.open({
				templateUrl: 'templates/processingModal.html',
				backdrop: 'static',
				keyboard: false,
			});
		};

		// Call our API service to get the current diagnosis translation details
		studyCollectionService.getPatientsList().then(function (response) {
			$scope.patientsList = response.data;
			angular.forEach($scope.patientsList, function(item) {item.added = false;});
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
		}).finally(function() {
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		});

		// Call our API service to get the current diagnosis translation details
		studyCollectionService.getResearchPatient().then(function (response) {
			$scope.questionnaireList = response.data;
			angular.forEach($scope.questionnaireList, function(item) {
				item.added = false;
				if($scope.language === "FR")
					item.name_display = item.name_FR;
				else
					item.name_display = item.name_EN;
			});
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
		}).finally(function() {
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		});

		// Call our API to get current consent form options
		studyCollectionService.getConsentForms().then(function (response) {
			response.data.forEach(function(entry){
				if($scope.language.toUpperCase() === "FR"){
					entry.name_display = entry.name_FR;
				}else{
					entry.name_display = entry.name_EN;
				}	
			});
			$scope.consentFormList = response.data;
		}).catch(function(err){
			ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
		}).finally(function(){
			processingModal.close();
			processingModal = null;
		});
		$scope.formLoaded = false;
		// Function to load form as animations
		$scope.loadForm = function () {
			$('.form-box-left').addClass('fadeInDown');
			$('.form-box-right').addClass('fadeInRight');
		};

	
		$scope.consentFormUpdate = function(form){
			$scope.toSubmit.consent_form.id = form.ID;
			$scope.selectedName = form.name_display;
			$scope.leftMenu.consent_form.open = $scope.toSubmit.consent_form;
			$scope.leftMenu.consent_form.display = $scope.leftMenu.consent_form.open;
			$scope.leftMenu.consent_form.preview = $scope.leftMenu.consent_form.open;
			$scope.validator.consent_form.completed = $scope.leftMenu.consent_form.open;
		}
	

		$scope.popupStart = {};
		$scope.popupEnd = {};
		$scope.openStart = function ($event) {
			$event.preventDefault();
			$event.stopPropagation();
			$scope.popupStart['opened'] = true;
			$scope.popupEnd['opened'] = false;
		};
		$scope.openEnd = function ($event) {
			$event.preventDefault();
			$event.stopPropagation();
			$scope.popupStart['opened'] = false;
			$scope.popupEnd['opened'] = true;
		};

		$scope.detailsUpdate = function () {
			$scope.validator.details.completed = ($scope.toSubmit.details.code !== "");
			$scope.leftMenu.details.open = $scope.validator.details.completed;
			$scope.leftMenu.details.display = $scope.validator.details.completed;
		};

		/**
		 * Validate the investigator personal info fields before allowing user to continue
		 * phone regex checks for standard 10 digit number with options for deliniation by space, hyphen, or period
		 * 		User can optionally enter country code eg +1 or +44
		 * email regex checks for standard RFC2822 email format
		 * phoneExt regex checks for any number of digits 0-9 up to a maximum length of 6
		 */
		$scope.validateInvestigatorInfo = function () {
			$scope.leftMenu.investigator.open = $scope.validator.details.completed;
			$scope.leftMenu.investigator.display = $scope.validator.details.completed;
			$scope.phoneVal = false;
			$scope.emVal = false;
			$scope.extVal = false;
			$scope.validator.investigator.completed = false;
			if($scope.toSubmit.investigator.phone){
				var phoneDigits = $scope.toSubmit.investigator.phone.replace(/[\s.,-]+/g, ""); //remove unwanted characters
				var phoneReg = new RegExp(/^(\+\d{0,2})?[ .-]?\(?(\d{3})\)?[ .-]?(\d{3})[ .-]?(\d{4})$/);
				$scope.phoneVal = phoneReg.test(phoneDigits);
				$scope.phoneDisplay = "";
				if(phoneDigits.length === 10){ // 438 389 9312
					$scope.phoneDisplay = phoneDigits.substr(0,3) + "-" + phoneDigits.substr(3,3) + "-" + phoneDigits.substr(6,4);
				}else if(phoneDigits.length === 12){ // +1 438 389 5678
					$scope.phoneDisplay = phoneDigits.substr(0,2) + " " + phoneDigits.substr(2,3) + "-" + phoneDigits.substr(5,3) + "-" + phoneDigits.substr(8,4);
				}else if(phoneDigits.length === 13){ // +44 438 389 4356
					$scope.phoneDisplay = phoneDigits.substr(0,3) + " " + phoneDigits.substr(3,3) + "-" + phoneDigits.substr(6,3) + "-" + phoneDigits.substr(9,4);
				}else{
					$scope.phoneDisplay = phoneDigits; // any other length is invalid number, just show the raw input in the preview until we get a valid number
				}
			}
			if($scope.toSubmit.investigator.email){
				var emReg = new RegExp(/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/);
				$scope.emVal = emReg.test($scope.toSubmit.investigator.email);
			}
			if($scope.toSubmit.investigator.phoneExt){
				var extDigits = $scope.toSubmit.investigator.phoneExt.replace(/[\s.,-]+/g, ""); //remove characters
				var phoneExtReg = new RegExp(/^\d{0,6}$/);
				$scope.extVal = phoneExtReg.test(extDigits);

				if($scope.extVal){
					$scope.phoneDisplay = $scope.phoneDisplay + " ext. " + extDigits;
				}
			}else{ //empty phone extension is valid
				$scope.extVal = true;
			}


			if($scope.phoneVal && $scope.emVal && $scope.extVal){
				$scope.validator.investigator.completed = true;
			}
		};

		// Watch to restrict the end calendar to not choose an earlier date than the start date
		$scope.$watch('toSubmit.dates.start_date', function(startDate){
			if (startDate !== undefined && startDate !== "")
				$scope.dateOptionsEnd.minDate = startDate;
			else
				$scope.dateOptionsEnd.minDate = Date.now();
			checkOpenDates();
		});

		function checkOpenDates() {
			if($scope.toSubmit.dates.start_date || $scope.toSubmit.dates.end_date) {
				$scope.leftMenu.dates.display = true;
				$scope.leftMenu.dates.open = true;
				$scope.leftMenu.dates.preview = true;
			} else {
				$scope.leftMenu.dates.display = false;
				$scope.leftMenu.dates.open = false;
				$scope.leftMenu.dates.preview = false;
			}
		}

		// Watch to restrict the start calendar to not choose a start after the end date
		$scope.$watch('toSubmit.dates.end_date', function(endDate){
			if (endDate !== undefined && endDate !== "")
				$scope.dateOptionsStart.maxDate = endDate;
			else
				$scope.dateOptionsStart.maxDate = null;
			checkOpenDates();
		});


		$scope.$watch('validator', function() {
			var totalsteps = 0;
			var completedSteps = 0;
			var nonMandatoryTotal = 0;
			var nonMandatoryCompleted = 0;
			angular.forEach($scope.validator, function(value) {
				if(value.mandatory)
					totalsteps++;
				else
					nonMandatoryTotal++;
				if(value.mandatory && value.completed)
					completedSteps++;
				else if(!value.mandatory) {
					if(value.completed) {
						if (value.valid)
							nonMandatoryCompleted++;
					}
					else
						nonMandatoryCompleted++;
				}
			});

			$scope.totalSteps = totalsteps;
			$scope.completedSteps = completedSteps;
			$scope.stepProgress = $scope.totalSteps > 0 ? ($scope.completedSteps / $scope.totalSteps * 100) : 0;
			$scope.formReady = ($scope.completedSteps >= $scope.totalSteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
		}, true);

		$scope.$watch('patientsList', function (triggerList) {
			triggerList = angular.copy(triggerList);
			var pos = -1;
			angular.forEach(triggerList, function (item) {
				pos = $scope.toSubmit.patients.findIndex(x => x === item.id);
				if(item.added) {
					if (pos === -1) {
						$scope.toSubmit.patients.push(item.id);
					}
				}
				else {
					if (pos !== -1) {
						$scope.toSubmit.patients.splice(pos, 1);
					}
				}
			});
			$scope.leftMenu.patients.open = ($scope.toSubmit.patients.length > 0);
			$scope.leftMenu.patients.display = $scope.leftMenu.patients.open;
			$scope.leftMenu.patients.preview = $scope.leftMenu.patients.open;
			$scope.validator.patients.completed = $scope.leftMenu.patients.open;
		}, true);

		$scope.$watch('questionnaireList', function (triggerList) {
			triggerList = angular.copy(triggerList);
			var pos = -1;
			angular.forEach(triggerList, function (item) {
				pos = $scope.toSubmit.questionnaire.findIndex(x => x === item.ID);
				if(item.added) {
					if (pos === -1) {
						$scope.toSubmit.questionnaire.push(item.ID);
					}
				}
				else {
					if (pos !== -1) {
						$scope.toSubmit.questionnaire.splice(pos, 1);
					}
				}
			});
			$scope.leftMenu.questionnaire.open = ($scope.toSubmit.questionnaire.length > 0);
			$scope.leftMenu.questionnaire.display = $scope.leftMenu.questionnaire.open;
			$scope.leftMenu.questionnaire.preview = $scope.leftMenu.questionnaire.open;
			$scope.validator.questionnaire.completed = $scope.leftMenu.questionnaire.open;
		}, true);

		$scope.$watch('toSubmit.title_desc', function () {
			$scope.validator.title_desc.completed = ($scope.toSubmit.title_desc.title_EN && $scope.toSubmit.title_desc.title_FR && $scope.toSubmit.title_desc.description_EN && $scope.toSubmit.title_desc.description_FR);
			$scope.leftMenu.title_desc.open = ($scope.toSubmit.title_desc.title_EN || $scope.toSubmit.title_desc.title_FR || $scope.toSubmit.title_desc.description_EN || $scope.toSubmit.title_desc.description_FR);
			$scope.leftMenu.title_desc.display = $scope.leftMenu.title_desc.open;
			$scope.leftMenu.title_desc.preview = $scope.leftMenu.title_desc.open;
		}, true);
	
		// Function to submit the new diagnosis translation
		$scope.submitStudy = function () {
			$scope.readyToSend.code = $scope.toSubmit.details.code;
			$scope.readyToSend.title_EN = $scope.toSubmit.title_desc.title_EN;
			$scope.readyToSend.title_FR = $scope.toSubmit.title_desc.title_FR;
			$scope.readyToSend.description_EN = $scope.toSubmit.title_desc.description_EN;
			$scope.readyToSend.description_FR = $scope.toSubmit.title_desc.description_FR;
			$scope.readyToSend.investigator = $scope.toSubmit.investigator.name;
			$scope.readyToSend.investigator_email = $scope.toSubmit.investigator.email;
			$scope.readyToSend.investigator_phone = ($scope.toSubmit.investigator.phone).replace(/[\s.,\-\(\)]+/g, ""); //strip away dot, hyphen, spaces, commas, brackets before sending to DB
			$scope.readyToSend.investigator_phoneExt = $scope.toSubmit.investigator.phoneExt;
			$scope.readyToSend.start_date = (($scope.toSubmit.dates.start_date) ? moment($scope.toSubmit.dates.start_date).format('X') : "");
			$scope.readyToSend.end_date = (($scope.toSubmit.dates.end_date) ? moment($scope.toSubmit.dates.end_date).format('X') : "");
			$scope.readyToSend.patients = $scope.toSubmit.patients;
			$scope.readyToSend.questionnaire = $scope.toSubmit.questionnaire
			$scope.readyToSend.consent_form = $scope.toSubmit.consent_form.id;
			
			$.ajax({
				type: 'POST',
				url: 'study/insert/study',
				data: $scope.readyToSend,
				success: function () {},
				error: function (err) {
					err.responseText = JSON.parse(err.responseText);
					ErrorHandler.onError(err, $filter('translate')('STUDY.ADD.ERROR_ADD'), arrValidationInsert);
				},
				complete: function () {
					$state.go('study');
				}
			});
		};

		var fixmeTop = $('.summary-fix').offset().top;
		$(window).scroll(function() {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixmeTop) {
				$('.summary-fix').css({
					position: 'fixed',
					top: '0',
					width: '15%'
				});
			} else {
				$('.summary-fix').css({
					position: 'static',
					width: ''
				});
			}
		});

		var fixMeMobile = $('.mobile-side-panel-menu').offset().top;
		$(window).scroll(function() {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixMeMobile) {
				$('.mobile-side-panel-menu').css({
					position: 'fixed',
					top: '50px',
					width: '100%',
					zIndex: '100',
					background: '#6f5499',
					boxShadow: 'rgba(93, 93, 93, 0.6) 0px 3px 8px -3px'

				});
				$('.mobile-summary .summary-title').css({
					color: 'white'
				});
			} else {
				$('.mobile-side-panel-menu').css({
					position: 'static',
					width: '',
					background: '',
					boxShadow: ''
				});
				$('.mobile-summary .summary-title').css({
					color: '#6f5499'
				});
			}
		});
	});
