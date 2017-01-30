angular.module('opalAdmin.controllers.sidePanelMenuController', ['ui.bootstrap', 'ui.grid']).


	/******************************************************************************
	* Controller for the side panel on main pages
	*******************************************************************************/
	controller('sidePanelMenuController', function($scope, $location, $state, LogoutService) {

        // Get the current page from url
        $scope.currentPage = $location.path().replace('/',''); // and remove leading slash

        // Function to go to alias page
        $scope.goToAlias = function () {
            $state.go('alias');
        }
        // Function to go to post page
        $scope.goToPost = function () {
            $state.go('post');
        }
        // Function to go to home page
        $scope.goToHome= function () {
            $state.go('home');
        }
        // Function to go to educational material page
        $scope.goToEducationalMaterial = function () {
            $state.go('educational-material');
        }
        // Function to go to hospital map page
        $scope.goToHospitalMap= function () {
            $state.go('hospital-map');
        }
        // Function to go to notification page
        $scope.goToNotification= function () {
            $state.go('notification');
        }
        // Function to go to patient page
        $scope.goToPatient= function () {
            $state.go('patients');
        }
		// Function to go to test results page
        $scope.goToTestResult= function () {
            $state.go('test-result');
        }
		// Function to logout
        $scope.goToLogout= function () {
            LogoutService.logout();
        }
        // Function to go to cron page
        $scope.goToCron= function () {
            $state.go('cron');
        }
        // Function to go to patient activity page
        $scope.goToPatientActivity = function () {
            $state.go('patient-activity')
        }
        // Function to go to account page
        $scope.goToAccount = function () {
            $state.go('account');
        }
        // Function to go to users page
        $scope.goToUsers = function () {
            $state.go('users');
        }
			
	});

