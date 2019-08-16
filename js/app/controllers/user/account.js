angular.module('opalAdmin.controllers.account', ['ui.bootstrap']).


/******************************************************************************
 * Controller for the account page
 *******************************************************************************/
controller('account', function ($scope, $rootScope, $translate, $route, $filter, Session, Encrypt) {

	// Set current user
	$scope.currentUser = Session.retrieveObject('user');

	// Initialize a list of languages available
	$scope.languages = [{
		name: $filter('translate')('PROFILE.ENGLISH'),
		id: 'EN'
	}, {
		name: $filter('translate')('PROFILE.FRENCH'),
		id: 'FR'
	}];

	$scope.bannerMessage = "";
	// Function to show page banner
	$scope.showBanner = function () {
		$(".bannerMessage").slideDown(function () {
			setTimeout(function () {
				$(".bannerMessage").slideUp();
			}, 5000);
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

	// Initialize account object
	$scope.defaultAccount = {
		user: $scope.currentUser,
		oldPassword: null,
		password: null,
		confirmPassword: null
	};
	$scope.account = jQuery.extend(true, {}, $scope.defaultAccount);

	// Function to reset the account object
	$scope.flushAccount = function () {
		$scope.account = jQuery.extend(true, {}, $scope.defaultAccount);
		$scope.validOldPassword.status = null;
		$scope.validPassword.status = null;
		$scope.validConfirmPassword.status = null;
	};

	$scope.validOldPassword = { status: null, message: null };
	$scope.validateOldPassword = function (oldPassword) {
		if (!oldPassword) {
			$scope.validOldPassword.status = null;
			return;
		} else {
			$scope.validOldPassword.status = 'valid';
		}
	};

	// Function to validate password
	$scope.validPassword = { status: null, message: null };
	$scope.validatePassword = function (password) {

		if (!password) {
			$scope.validPassword.status = null;
			return;
		}

		if (password.length < 6) {
			$scope.validPassword.status = 'invalid';
			$scope.validPassword.message = $filter('translate')('PROFILE.SHORT');
			return;
		} else if ($scope.account.oldPassword && $scope.account.oldPassword == password) {
			$scope.validPassword.status = 'warning';
			$scope.validPassword.message = $filter('translate')('PROFILE.OLD_NEW');
		}
		else {
			$scope.validPassword.status = 'valid';
			$scope.validPassword.message = null;
		}
	};

	// Function to validate confirm password
	$scope.validConfirmPassword = { status: null, message: null };
	$scope.validateConfirmPassword = function (confirmPassword) {

		if (!confirmPassword) {
			$scope.validConfirmPassword.status = null;
			return;
		}

		if ($scope.validPassword.status != 'valid' || $scope.account.password != $scope.account.confirmPassword) {
			$scope.validConfirmPassword.status = 'invalid';
			$scope.validConfirmPassword.message = $filter('translate')('PROFILE.SAME_VALID');
			return;
		} else {
			$scope.validConfirmPassword.status = 'valid';
			$scope.validConfirmPassword.message = null;
		}
	};

	// Function to check password reset form completion
	$scope.checkForm = function () {
		if ($scope.validOldPassword.status != 'valid' || $scope.validPassword.status != 'valid' ||
			$scope.validConfirmPassword.status != 'valid') {
			return false;
		} else {
			return true;
		}
	};

	// Function to update password
	$scope.updatePassword = function () {

		if ($scope.checkForm()) {

			// check if user is defined in session (they should...)
			if (!$scope.currentUser) {
				$scope.bannerMessage = $filter('translate')('PROFILE.INVALID_SESSION');
				$scope.setBannerClass('danger');
				$scope.showBanner();
				return;
			}

			// duplicate user
			var user = jQuery.extend(true, {}, $scope.account);
			// one-time pad using current time and rng
			var cypher = (moment().unix() % (Math.floor(Math.random() * 20))) + 103;
			// encode passwords before request
			user.password = Encrypt.encode(user.password, cypher);
			user.oldPassword = Encrypt.encode(user.oldPassword, cypher);
			user.confirmPassword = Encrypt.encode(user.confirmPassword, cypher);
			user.cypher = cypher;

			// submit form
			$.ajax({
				type: "POST",
				url: "user/update/password",
				data: user,
				success: function (response) {
					response = JSON.parse(response);
					if (response.value == 1) {
						$scope.flushAccount();
						$scope.setBannerClass('success');
						$scope.bannerMessage = $filter('translate')('PROFILE.PASSWORD_SUCCESS');
						$scope.showBanner();
						$scope.$apply();
					} else {
						var errorCode = response.error.code;
						var errorMessage = response.error.message;
						if (errorCode == 'old-password-incorrect') {
							$scope.validOldPassword.status = 'warning';
							$scope.validOldPassword.message = $filter('translate')('PROFILE.INVALID_PASSWORD');
							$scope.setBannerClass('warning');
							$scope.bannerMessage = $filter('translate')('PROFILE.INVALID_PASSWORD');
							$scope.$apply();
							$scope.showBanner();
						} else {
							$scope.setBannerClass('danger');
							$scope.bannerMessage = errorMessage;
							$scope.showBanner();
						}
					}
				}
			});

		}
	};

	// Function when language changes
	$scope.updateLanguage = function (user) {
		// submit form
		$.ajax({
			type: "POST",
			url: "user/update/language",
			data: user,
			success: function () {
				Session.update(user); // change language in cookies
				$translate.use($scope.currentUser.language.toLowerCase());
			},
			error: function () {
				alert($filter('translate')('PROFILE.LANGUAGE_ERROR'));
			},
			complete: function () {
				alert($filter('translate')('PROFILE.LANGUAGE_SUCCESS'));
				location.reload();
			}
		});
	};


});

