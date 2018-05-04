angular.module('opalAdmin.controllers.user.edit', ['ui.bootstrap', 'ui.grid']).

	controller('user.edit', function ($scope, $uibModal, $uibModalInstance, $filter, $sce, $state, userCollectionService, Encrypt) {

		// Default booleans
		$scope.changesMade = false;
		$scope.passwordChange = false;

		$scope.user = {};

		// Initialize a list of languages available
		$scope.languages = [{
			name: 'English',
			id: 'EN'
		}, {
			name: 'French',
			id: 'FR'
		}];

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

		// Call our API service to get the current user's details
		userCollectionService.getUserDetails($scope.currentUser.serial).then(function (response) {

			$scope.user = response.data;
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function(response) {
			console.error('Error occurred getting user details:', response.status, response.data);
		});

		// Call our API service to get the list of possible roles
		$scope.roles = [];
		userCollectionService.getRoles().then(function (response) {
			$scope.roles = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting roles:', response.status, response.data);
		});

		// Function that triggers when the password fields are updated
		$scope.passwordUpdate = function () {

			$scope.changesMade = true;
		};
		// Function to validate password 
		$scope.validPassword = { status: null, message: null };
		$scope.validatePassword = function (password) {

			$scope.passwordChange = true;
			$scope.validateConfirmPassword($scope.user.confirmPassword);

			if (!password) {
				$scope.validPassword.status = null;
				$scope.passwordUpdate();
				if (!$scope.validConfirmPassword)
					$scope.passwordChange = false;
				return;
			}

			if (password.length < 6) {
				$scope.validPassword.status = 'invalid';
				$scope.validPassword.message = 'Use greater than 6 characters';
				$scope.passwordUpdate();
				return;
			} else {
				$scope.validPassword.status = 'valid';
				$scope.validPassword.message = null;
				$scope.passwordUpdate();
				if ($scope.validConfirmPassword.status == 'valid')
					$scope.passwordChange = false;
			}
		};

		// Function to validate confirm password
		$scope.validConfirmPassword = { status: null, message: null };
		$scope.validateConfirmPassword = function (confirmPassword) {

			$scope.passwordChange = true;
			if (!confirmPassword) {
				$scope.validConfirmPassword.status = null;
				$scope.passwordUpdate();
				if (!$scope.validPassword)
					$scope.passwordChange = false;
				return;
			}

			if ($scope.validPassword.status != 'valid' || $scope.user.password != $scope.user.confirmPassword) {
				$scope.validConfirmPassword.status = 'invalid';
				$scope.validConfirmPassword.message = 'Enter same valid password';
				$scope.passwordUpdate();
				return;
			} else {
				$scope.validConfirmPassword.status = 'valid';
				$scope.validConfirmPassword.message = null;
				$scope.passwordUpdate();
				if ($scope.validPassword.status == 'valid')
					$scope.passwordChange = false;
			}
		};

		// Function that triggers when the role field is updated
		$scope.roleUpdate = function () {

			$scope.changesMade = true;
		};

		// Function that triggers when the language field is updated
		$scope.languageUpdate = function () {

			$scope.changesMade = true;
		};

		// Function to check for form completion
		$scope.checkForm = function () {
			if (($scope.changesMade && !$scope.passwordChange) ||
				($scope.validPassword.status == 'valid' && $scope.validConfirmPassword.status == 'valid'))
				return true;
			else
				return false;
		};

		// Submit changes
		$scope.updateUser = function () {
			if ($scope.checkForm()) {

				// duplicate user
				var user = jQuery.extend(true, {}, $scope.user);
				if (user.password && user.confirmPassword) {
					// one-time pad using current time and rng
					var cypher = (moment().unix() % (Math.floor(Math.random() * 20))) + 103; 
					// encode passwords before request
					user.password = Encrypt.encode(user.password, cypher);
					user.confirmPassword = Encrypt.encode(user.confirmPassword, cypher);
					user.cypher = cypher;
				}

				// submit 
				$.ajax({
					type: "POST",
					url: "php/user/update.user.php",
					data: user,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.user.username + "\"";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.error.message;
						}

						$scope.showBanner();
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