angular.module('opalAdmin.controllers.loginModal', ['ngAnimate', 'ui.bootstrap']).


	/******************************************************************************
	* Login controller 
	*******************************************************************************/
	controller('loginModal', function ($scope, $rootScope, $state, $filter, AUTH_EVENTS, AuthService, $uibModalInstance, Encrypt, Session) {

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

		$scope.submitLogin = function (credentials) {
			if ($scope.loginFormComplete()) {
				var cypher = (moment().unix() % (Math.floor(Math.random() * 20))) + 103;
				var encrypted = JSON.stringify({username: credentials.username, password: credentials.password});
				encrypted = (Encrypt.encode(encrypted, cypher));

				AuthService.login(encrypted, cypher).then(function (response) {
					var accessLevel = [];
					angular.forEach(response.data.access, function (row) {
						accessLevel[row["ID"]] = row["access"];
					});
					response.data.access = accessLevel;

					Session.create(response.data);
					$rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
					$rootScope.currentUser = response.data.user;
					$rootScope.setSiteLanguage(response.data.user);
					$uibModalInstance.close();
				}).catch(function(err) {
					// $rootScope.$broadcast(AUTH_EVENTS.loginFailed);
					$scope.bannerMessage = $filter('translate')('LOGIN.ERROR_401');
					$scope.setBannerClass('danger');
					$scope.shakeForm();
					$scope.showBanner();
				});
			}
		};

	});

