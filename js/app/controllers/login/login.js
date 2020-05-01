angular.module('opalAdmin.controllers.login', ['ngAnimate', 'ui.bootstrap']).


/******************************************************************************
 * Login controller
 *******************************************************************************/
controller('login', function ($scope, $rootScope, $state, $filter, $translate, AUTH_EVENTS, AuthService, Idle, Encrypt, Session) {

	// Initialize login object
	$scope.credentials = {
		username: "",
		password: ""
	};

	$scope.loginPage = true;

	$scope.language = {
		main: "fr",
		alt: "en",
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

	$scope.changeLanguage = function () {
		var temp = $scope.language.main;
		$translate.use($scope.language.alt);
		$scope.language.main = $scope.language.alt;
		$scope.language.alt = temp;
	};

	// Function to "shake" form container if fields are incorrect
	$scope.shakeForm = function () {
		$scope.formLoaded = true;
		$('.form-box-shake').addClass('shake');
		setTimeout(function () {
			$('.form-box-shake').removeClass('shake');
		}, 1000);
	};

	$scope.submitLogin = function (credentials) {
		if ($scope.loginFormComplete()) {

			var cypher = (moment().unix() % (Math.floor(Math.random() * 20))) + 103;

			var encrypted = JSON.stringify({username: credentials.username, password: credentials.password});
			encrypted = (Encrypt.encode(encrypted, cypher));

			AuthService.login(encrypted, cypher).then(function (response) {

				Session.create(response.data);
				$rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
				$rootScope.currentUser = response.data;
				$rootScope.setSiteLanguage(response.data);
				$state.go('home');
				Idle.watch();
			}).catch(function(err) {
				$rootScope.$broadcast(AUTH_EVENTS.loginFailed);
				$scope.bannerMessage = $filter('translate')('LOGIN.WRONG');
				$scope.setBannerClass('danger');
				$scope.shakeForm();
				$scope.showBanner();
			});
		}
	};
});

