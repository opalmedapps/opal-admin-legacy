angular.module('opalAdmin.controllers.account', ['ui.bootstrap']).


	/******************************************************************************
	 * Controller for the account page
	 *******************************************************************************/
	controller('account', function ($scope, $rootScope, $translate, $route, $filter, $templateCache, Session) {
		$scope.navMenu = Session.retrieveObject('menu');

		// Set current user
		$scope.currentUser = Session.retrieveObject('user');

		// Initialize a list of languages available
		$scope.languages = [{
			name: "English",
			id: 'EN'
		}, {
			name: "Français",
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

			var validationPassword = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?!.*\s).{12,}$/;

			if(!password.match(validationPassword)) {
				$scope.validPassword.status = 'invalid';
				$scope.validPassword.message = $filter('translate')('PROFILE.INVALID');
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

				var data = {
					password: $scope.account.password,
					oldPassword: $scope.account.oldPassword,
					confirmPassword: $scope.account.confirmPassword,
				};

				$.ajax({
					type: "POST",
					url: "user/update/password",
					data: data,
					success: function (response) {
						$scope.flushAccount();
						$scope.setBannerClass('success');
						$scope.bannerMessage = $filter('translate')('PROFILE.PASSWORD_SUCCESS');
					},
					error: function(err) {
						$scope.setBannerClass('danger');
						$scope.bannerMessage = $filter('translate')('PROFILE.SERVER_ERROR') + err.responseText;
					},
					complete: function() {
						$scope.showBanner();
						$scope.$apply();
					}
				});

			}
		};

		// Function when language changes
		$scope.updateLanguage = function (user) {
			var toSend = {
				language: user.language
			};

			// submit form
			$.ajax({
				type: "POST",
				url: "user/update/language",
				data: toSend,
				success: function (menu) {
					$templateCache.removeAll();
					Session.updateUser(user); // change language in cookies
					$translate.use($scope.currentUser.language.toLowerCase());
					location.reload();
				},
				error: function () {
					alert($filter('translate')('PROFILE.LANGUAGE_ERROR'));
				}
			});
		};


	});

