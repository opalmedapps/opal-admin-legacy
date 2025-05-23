// SPDX-FileCopyrightText: Copyright (C) 2016 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.navigation', ['ui.bootstrap']).


	/******************************************************************************
	 * Controller for navigating the site
	 *******************************************************************************/
	controller('navigation', function ($scope, $location, $state, LogoutService, Session) {
		$scope.navMenu = Session.retrieveObject('menu');

		// Get the current page from url
		$scope.currentPage = $location.path().replace('/', ''); // and remove leading slash

		// Function to go to alias page
		$scope.goToAlias = function () {
			$state.go('alias');
		};
		// Function to go to post page
		$scope.goToPost = function () {
			$state.go('post');
		};
		// Function to go to home page
		$scope.goToHome = function () {
			$state.go('home');
		};
		// Function to go to educational material page
		$scope.goToEducationalMaterial = function () {
			$state.go('educational-material');
		};
		// Function to go to hospital map page
		$scope.goToHospitalMap = function () {
			$state.go('hospital-map');
		};
		// Function to go to notification page
		$scope.goToNotification = function () {
			$state.go('notification');
		};
		// Function to go to patient page
		$scope.goToPatient = function () {
			$state.go('patients');
		};
		// Function to go to patient page
		$scope.goToPatientMenu = function () {
			$state.go('patients/menu');
		};
		// Function to go to hospital settings page
		$scope.goToHospitalSettingsMenu = function () {
			$state.go('hospital-settings');
		};
		// Function to go to clinician page
		$scope.goToClinicianMenu = function () {
			$state.go('clinician');
		};
		// Function to go to patient registration page
		$scope.goToPatientRegistration = function () {
			$state.go('patients-register');
		};
		// Function to go to test results page
		$scope.goToTestResult = function () {
			$state.go('test-result');
		};
		// Function to logout
		$scope.goToLogout = function () {
			LogoutService.logout();
		};
		// Function to go to cron page
		$scope.goToCron = function () {
			$state.go('cron');
		};
		// Function to go to patient activity page
		$scope.goToPatientActivity = function () {
			$state.go('patient-activity');
		};
		// Function to go to account page
		$scope.goToAccount = function () {
			if ($scope.configs.login.activeDirectory.enabled === 1)
				$state.go('ad-account');
			else
				$state.go('account');
		};
		// Function to go to users page
		$scope.goToUsers = function () {
			$state.go('users');
		};
		// Function to go to email page
		$scope.goToEmail = function () {
			$state.go('email');
		};
		// Function to go to questionnaire main menu page
		$scope.goToQuestionnaireMainMenu = function () {
			$state.go('questionnaire/menu');
		};
		// Function to go to publications page
		$scope.goToPublication = function () {
			$state.go('publication');
		};
		// Function to go to questionnaire page
		$scope.goToQuestionnaire = function () {
			$state.go('questionnaire');
		};
		// Function to go to questionnaire question bank
		$scope.goToQuestionnaireQuestionBank = function () {
			$state.go('questionnaire/question');
		};
		// Function to go to completed questionnaires page
		$scope.goToQuestionnaireCompleted = function () {
			$state.go('questionnaire-completed');
		};
		// Function to go to question type page
		$scope.goToTemplateQuestion = function () {
			$state.go('questionnaire/template-question');
		};
		// Function to go to diagnosis translation page
		$scope.goToDiagnosisTranslation = function () {
			$state.go('diagnosis-translation');
		};
		// Function to go to custom code page
		$scope.goToCustomCodes = function () {
			$state.go('custom-code');
		};
		// Function to go to study page
		$scope.goToStudies = function () {
			$state.go('study');
		};
		// Function to go to study page
		$scope.goToRoles = function () {
			$state.go('role');
		};
		// Function to go to user activity page
		$scope.goToUserActivity = function () {
			$state.go('user-activity');
		};

		// Function to close the navbar on selection of a menu page
		$scope.closeNav = function () {
			$(".navbar-collapse").collapse('hide');
		};

		// Function to set dropdown active for publishing tools
		$scope.currentActivePublishingTool = function () {
			var publishingToolPages = ['alias','post','educational-material','hospital-map','notification',
				'test-result','questionnaire/menu','email','custom-code','study'];
			if (publishingToolPages.indexOf($state.current.name) !== -1) {
				return true;
			}
			else return false;
		};
		// Function to set dropdown active for administration menu
		$scope.currentActiveAdministration = function () {
			var administrationPages = ['diagnosis-translation','cron','patients','patients-register',
				'patient-activity','users','user-activity'];
			if (administrationPages.indexOf($state.current.name) !== -1) {
				return true;
			}
			else return false;
		};
		// Function to set dropdown active for profile menu
		$scope.currentActiveProfile = function () {
			var profilePages = ['account'];
			if (profilePages.indexOf($state.current.name) !== -1) {
				return true;
			}
			else return false;
		};

		// Function to go to report page
		$scope.goToReport = function () {
			$state.go('patients/report');
		};

		$scope.goToIndividual = function(){
			$state.go('patients/report/individual');
		};

		$scope.goToGroup = function(){
			$state.go('patient/report/group');
		};

		// Function to go to SMS page
		$scope.goToSMS = function(){
			$state.go('sms');
		};

		// Function to go to SMS message page
		$scope.goToSMSMessage = function(){
			$state.go('sms/message');
		};

		// Function to go to Patient Tools page
		$scope.goToPatientModificationTools = function () {
			$state.go('patients-administration');
		};

	});
