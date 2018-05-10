angular.module('opalAdmin.controllers.patientRegistration.confirm', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'pascalprecht.translate']).

	controller('patientRegistration.confirm', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, patientCollectionService, $translate, $rootScope, AuthService, Encrypt) {


		// Initialize login object
		$scope.credentials = {
			username: "",
			password: ""
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

		// Function to return boolean on completed login form
		$scope.loginFormComplete = function () {
			if (($scope.credentials.username && $scope.credentials.password))
				return true;
			else
				return false;
		};

		// Function to "shake" form container if fields are incorrect
		$scope.shakeForm = function () {
			$scope.formLoaded = true;
			$('.form-box').addClass('shake');
			setTimeout(function () {
				$('.form-box').removeClass('shake');
			}, 1000);
		};

		$scope.confirmRegistration = function (credentials) {
			if ($scope.loginFormComplete()) {
				// one-time pad using current time and rng
				var cypher = (moment().unix() % (Math.floor(Math.random() * 20))) + 103; 
				var loginCreds = jQuery.extend(true, {}, credentials);
				// encode password before request
				loginCreds.password = Encrypt.encode(credentials.password, cypher);
				loginCreds.cypher = cypher;

				AuthService.confirm(loginCreds).then(function () {
					$uibModalInstance.close();
				}, function () {
					$scope.bannerMessage = $filter('translate')('STATUS_USERNAME_PASSWORD_INCORRECT');
					$scope.setBannerClass('danger');
					$scope.shakeForm();
					$scope.showBanner();
				});
			}
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});