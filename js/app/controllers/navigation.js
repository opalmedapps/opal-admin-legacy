angular.module('opalAdmin.controllers.navigation', ['ui.bootstrap']).


	/******************************************************************************
	* Controller for navigating the site
	*******************************************************************************/
	controller('navigation', function ($scope, $location, $state, LogoutService) {

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
			$state.go('questionnaire-menu');
		};
		// Function to go to questionnaire page
		$scope.goToQuestionnaire = function () {
			$state.go('questionnaire');
		};
		// Function to go to questionnaire question bank
		$scope.goToQuestionnaireQuestionBank = function () {
			$state.go('questionnaire-question');
		};
		// Function to go to completed questionnaires page
		$scope.goToQuestionnaireCompleted = function () {
			$state.go('questionnaire-completed');
		};
		// Function to go to legacy questionnaires page
		$scope.goToLegacyQuestionnaire = function () {
			$state.go('legacy-questionnaire');
		};
		// Function to go to diagnosis translation page
		$scope.goToDiagnosisTranslation = function () {
			$state.go('diagnosis-translation');
		};
		// Function to go to user activity page
		$scope.goToUserActivity = function () {
			$state.go('user-activity');
		};

		// Function to close the navbar on selection of a menu page
		$scope.closeNav = function () {
			$(".navbar-collapse").collapse('hide');
		}

		// Function to set dropdown active for publishing tools
		$scope.currentActivePublishingTool = function () {
			var publishingToolPages = ['alias','post','educational-material','hospital-map','notification',
			'test-result','questionnaire-menu','email','legacy-questionnaire'];
			if (publishingToolPages.indexOf($state.current.name) !== -1) {
				return true;
			}
			else return false;
		}
		// Function to set dropdown active for administration menu
		$scope.currentActiveAdministration = function () {
			var adminstrationPages = ['diagnosis-translation','cron','patients','patients-register',
			'patient-activity','users','user-activity'];
			if (adminstrationPages.indexOf($state.current.name) !== -1) {
				return true;
			}
			else return false;
		}
		// Function to set dropdown active for profile menu
		$scope.currentActiveProfile = function () {
			var profilePages = ['account'];
			if (profilePages.indexOf($state.current.name) !== -1) {
				return true;
			}
			else return false;
		}

		

	});

