//const e = require("express");

angular.module('opalAdmin.controllers.study.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('study.edit', function ($scope, $filter, $uibModal, $uibModalInstance, $locale, studyCollectionService, Session, ErrorHandler) {

	$scope.readyToSend = {
		ID: "",
		code: "",
		title_EN: "",
		title_FR: "",
		description_EN: "",
		description_FR: "",
		investigator: "",
		investigator_phone: "",
		investigator_email: "",
		investigator_phoneExt: "",
		start_date: "",
		end_date: "",
		patientConsents: [],
		patients: [],
		questionnaire: [],
		consent_form: "",
	};

	$scope.toSubmit = {
		ID: "",
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
		patientConsents:[],
		patients: [],
		questionnaire: [],
		consent_form: {
			id: ""
		}
	};

	// Default toolbar for wysiwyg
	$scope.toolbar = [
		['h1', 'h2', 'h3', 'p'],
		['bold', 'italics', 'underline', 'ul', 'ol'],
		['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
		['html', 'insertLink']
	];

	$scope.consentChoices = 
	[$filter('translate')('STUDY.EDIT.INVITED'), 
	$filter('translate')('STUDY.EDIT.OPAL_CONSENTED'),
	$filter('translate')('STUDY.EDIT.OTHER_CONSENTED'),
	$filter('translate')('STUDY.EDIT.DECLINED')];

	$scope.phoneVal = true;
	$scope.emVal = true;
	$scope.extVal = true;
	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;
	$scope.patientsList = [];
	$scope.patientConsentList = [];
	$scope.backupStudy = [];
	$scope.ready = [false, false, false, false];

	$scope.validator = {
		details: {
			completed: true,
			mandatory: true,
			valid: true,
		},
		title_desc: {
			completed: true,
			mandatory: true,
			valid: true,
		},
		investigator: {
			completed: true,
			mandatory: true,
			valid: true,
		},
		dates: {
			completed: false,
			mandatory: false,
			valid: true,
		},
		patientConsents: {
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
		},
		consent_form: {
			completed: true,
			mandatory: true,
			valid: true,
		},
	};

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
	$scope.questionnaireList = [];

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

	$scope.language = Session.retrieveObject('user').language;

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};
	// Show processing dialog
	$scope.showProcessingModal();

	/**
		 * Validate the investigator personal info fields before allowing user to continue
		 * phone regex checks for standard 10 digit number with options for deliniation by space, hyphen, or period
		 * 		User can optionally enter country code eg +1 or +44
		 * email regex checks for standard RFC2822 email format
		 * phoneExt regex checks for any number of digits 0-9 up to a maximum length of 6
		 */
	 $scope.validateInvestigatorInfo = function () {
		$scope.phoneVal = false;
		$scope.emVal = false;
		$scope.extVal = false;
		$scope.validator.investigator.completed = false;
		if($scope.toSubmit.investigator.phone){
			var phoneDigits = $scope.toSubmit.investigator.phone.replace(/[\s.,-]+/g, ""); //remove unwanted characters
			var phoneReg = new RegExp(/^(\+\d{0,2})?[ .-]?\(?(\d{3})\)?[ .-]?(\d{3})[ .-]?(\d{4})$/);
			$scope.phoneVal = phoneReg.test(phoneDigits);
		}
		if($scope.toSubmit.investigator.email){
			var emReg = new RegExp(/[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/);
			$scope.emVal = emReg.test($scope.toSubmit.investigator.email);
		}
		if($scope.toSubmit.investigator.phoneExt){
			var extDigits = $scope.toSubmit.investigator.phoneExt.replace(/[\s.,-]+/g, ""); //remove characters
			var phoneExtReg = new RegExp(/^\d{0,6}$/);
			$scope.extVal = phoneExtReg.test(extDigits);
		}else{ //empty phone extension is valid
			$scope.extVal = true;
		}
		if($scope.phoneVal && $scope.emVal && $scope.extVal){
			$scope.validator.investigator.completed = true;
		}
	};

	$scope.patientConsentChange = function(value){
		value.changed = true;
		if(value.consent_display === $filter('translate')('STUDY.EDIT.INVITED')){
			value.consent = 1;
		}else if(value.consent_display === $filter('translate')('STUDY.EDIT.OPAL_CONSENTED')){
			value.consent = 2;
		}else if(value.consent_display === $filter('translate')('STUDY.EDIT.OTHER_CONSENTED')){
			value.consent = 3;
		}else if(value.consent_display === $filter('translate')('STUDY.EDIT.DECLINED')){
			value.consent = 4;
		}
	}

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
	});

	studyCollectionService.getPatientsList().then(function (response) {
		$scope.patientsList = response.data;
		$scope.ready[0] = true;
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
	});

	studyCollectionService.getPatientConsentList($scope.currentStudy.ID).then(function(response){
		$scope.patientConsentList = response.data;
	
		angular.forEach($scope.patientConsentList, function(value){
			value.changed = null;
			switch (parseInt(value.consent)){
				default:
					value.consent_display = $filter('translate')('STUDY.EDIT.INVITED'); //default value in DB should always be invited
					break;
				case 1:
					value.consent_display = $filter('translate')('STUDY.EDIT.INVITED');
					break;
				case 2:
					value.consent_display = $filter('translate')('STUDY.EDIT.OPAL_CONSENTED');
					break;
				case 3:
					value.consent_display = $filter('translate')('STUDY.EDIT.OTHER_CONSENTED');
					break;
				case 4:
					value.consent_display = $filter('translate')('STUDY.EDIT.DECLINED');
			}
		});
		$scope.ready[1] = true;
	}).catch(function(err){
		ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
	});

	$scope.$watch('ready', function() {
		if( $scope.ready.every(function (rd) {return rd;}) )
		{
			var dateArray, year, month, date;
			if($scope.patientsList.length > 0)
				$scope.validator.patients.completed = true;
			if($scope.questionnaireList.length > 0)
				$scope.validator.questionnaire.completed = true;
		 
			angular.forEach($scope.patientsList, function(value) {
				value.added = $scope.backupStudy.patients.includes(value.id);
			});

			angular.forEach($scope.questionnaireList, function(value) {
				value.added = $scope.backupStudy.questionnaire.includes(value.ID);
			});
			
			if($scope.language === "FR"){
				$scope.consentTitle = $scope.backupStudy.consentQuestionnaireTitle[0].name_FR;
			}else{
				$scope.consentTitle = $scope.backupStudy.consentQuestionnaireTitle[0].name_EN;
			}
		

			$scope.toSubmit.ID = $scope.backupStudy.ID;
			$scope.toSubmit.details.code = $scope.backupStudy.code;
			$scope.toSubmit.title_desc.title_EN = $scope.backupStudy.title_EN;
			$scope.toSubmit.title_desc.title_FR = $scope.backupStudy.title_FR;
			$scope.toSubmit.title_desc.description_EN = $scope.backupStudy.description_EN;
			$scope.toSubmit.title_desc.description_FR = $scope.backupStudy.description_FR;
			$scope.toSubmit.investigator.name = $scope.backupStudy.investigator;
			$scope.toSubmit.investigator.email = $scope.backupStudy.email; 
			$scope.toSubmit.investigator.phone = $scope.backupStudy.phone;
			$scope.toSubmit.investigator.phoneExt = $scope.backupStudy.phoneExt;

			$scope.toSubmit.consent_form.id = $scope.backupStudy.consentQuestionnaireId;
			if($scope.backupStudy.startDate !== "" && $scope.backupStudy.startDate !== null) {
				dateArray = $scope.backupStudy.startDate.split("-");
				year = dateArray[0];
				month = parseInt(dateArray[1], 10) - 1;
				date = dateArray[2];
				$scope.toSubmit.dates.start_date = new Date(year, month, date);
				$scope.validator.dates.completed = true;
			}
			if($scope.backupStudy.endDate !== "" && $scope.backupStudy.endDate !== null) {
				dateArray = $scope.backupStudy.endDate.split("-");
				year = dateArray[0];
				month = parseInt(dateArray[1], 10) - 1;
				date = dateArray[2];
				$scope.toSubmit.dates.end_date = new Date(year, month, date);
				$scope.validator.dates.completed = true;
			}
			$scope.toSubmit.patients = $scope.backupStudy.patients;
			$scope.toSubmit.questionnaire = $scope.backupStudy.questionnaire;
			$scope.toSubmit.patientConsents = $scope.patientConsentList;
			$scope.oldData = JSON.parse(JSON.stringify($scope.toSubmit));
			$scope.oldData.dates.start_date = $scope.toSubmit.dates.start_date;
			$scope.oldData.dates.end_date = $scope.toSubmit.dates.end_date;

			$scope.changesDetected = false;
		}
	}, true);

	// Call our API service to get the current diagnosis translation details
	studyCollectionService.getStudiesDetails($scope.currentStudy.ID).then(function (response) {
		$scope.backupStudy = response.data;
		$scope.ready[2] = true;
		if($scope.backupStudy.consentQuestionnaireId){
			studyCollectionService.consentFormPublished($scope.backupStudy.consentQuestionnaireId).then(function(response){
				if(response.data.length == 1){
					$scope.formPublished = true;
				}else{
					$scope.formPublished = false;
				}
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
			});
		}
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
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
		$scope.ready[3] = true;
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
	}).finally(function() {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	});

	$scope.consentFormUpdate = function(form){
		$scope.toSubmit.consent_form.id = form.ID;
		$scope.consentTitle = form.name_display;
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

	$scope.$watch('toSubmit', function() {
		$scope.changesDetected = JSON.stringify($scope.toSubmit) !== JSON.stringify($scope.oldData);
	}, true);

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
		$scope.formReady = (completedSteps >= totalsteps) && (nonMandatoryCompleted >= nonMandatoryTotal) && $scope.validator.investigator.completed;
	}, true);

	$scope.$watch('toSubmit.details', function(){
		$scope.validator.details.completed = !!$scope.toSubmit.details.code;
	}, true);

	$scope.$watch('toSubmit.title_desc', function(){
		$scope.validator.title_desc.completed = $scope.toSubmit.title_desc.title_EN !== "" && $scope.toSubmit.title_desc.title_EN !== "" && $scope.toSubmit.title_desc.title_FR !== "" && $scope.toSubmit.title_desc.description_EN !== "" && $scope.toSubmit.title_desc.description_FR !== "";
	}, true);

	$scope.$watch('toSubmit.investigator', function(){
		$scope.validator.details.completed = !!($scope.toSubmit.investigator.name && $scope.toSubmit.investigator.phone && $scope.toSubmit.investigator.email);
	}, true);

	$scope.$watch('toSubmit.consent_form', function(){
		$scope.validator.consent_form.completed = !!$scope.toSubmit.consent_form.id;
	}, true);

	// Watch to restrict the end calendar to not choose an earlier date than the start date
	$scope.$watch('toSubmit.dates.start_date', function(startDate){
		if (startDate !== undefined && startDate !== "")
			$scope.dateOptionsEnd.minDate = startDate;
		else
			$scope.dateOptionsEnd.minDate = null;
	});

	// Watch to restrict the start calendar to not choose a start after the end date
	$scope.$watch('toSubmit.dates.end_date', function(endDate){
		if (endDate !== undefined && endDate !== "")
			$scope.dateOptionsStart.maxDate = endDate;
		else
			$scope.dateOptionsStart.maxDate = null;
	});

	$scope.$watch('patientsList', function (triggerList) {
		triggerList = angular.copy(triggerList);
		var pos = -1;
		var posC = -1;
		angular.forEach(triggerList, function (item) {
			pos = $scope.toSubmit.patients.findIndex(x => x === item.id);
			posC = $scope.patientConsentList.findIndex(x => x.id === item.id);
			if(item.added) {
				if (pos === -1) {
					$scope.toSubmit.patients.push(item.id);
				}
				if(posC === -1){
					$scope.patientConsentList.push({'id':item.id,'consent':'','name':item.name,'changed':null});
				}
			}
			else {
				if (pos !== -1) {
					$scope.toSubmit.patients.splice(pos, 1);
				}
				if (posC !== -1){
					$scope.patientConsentList.splice(posC, 1);
				}
			}
		});
		$scope.toSubmit.patients.sort(function(a, b) {
			return a - b;
		});
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
		$scope.toSubmit.questionnaire.sort(function(a, b) {
			return a - b;
		});
	}, true);

	// Submit changes
	$scope.updateCustomCode = function() {
		if($scope.formReady && $scope.changesDetected) {
			$scope.readyToSend.ID = $scope.toSubmit.ID
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
			$scope.readyToSend.questionnaire = $scope.toSubmit.questionnaire;
			$scope.readyToSend.consent_form = $scope.toSubmit.consent_form.id;
			$scope.readyToSend.patientConsents = $scope.patientConsentList;

			$.ajax({
				type: "POST",
				url: "study/update/study",
				data: $scope.readyToSend,
				success: function () {
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_UPDATE'), arrValidationInsert);
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		}
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});