angular.module('opalAdmin.controllers.installation', ['ui.bootstrap']).


	/******************************************************************************
	* Controller for the installation process
	*******************************************************************************/
	controller('installation', function ($scope, installCollectionService, $state) {

		var pathname = location.pathname;
		var urlpath = pathname.replace('main.html', '');

		// completed registration steps in object notation
		var steps = {
			requirements: { completed: false },
			opal_setup: { completed: false },
			clinical_setup: { completed: false },
			config_files: { completed: false },
			site_account: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 5;

		// Progress bar based on default completed steps and total
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		// Function to calculate / return step progress
		function trackProgress(value, total) {
			return Math.round(100 * value / total);
		}

		// Function to return number of steps completed
		function stepsCompleted(steps) {

			var numberOfTrues = 0;
			for (var step in steps) {
				if (steps[step].completed === true) {
					numberOfTrues++;
				}
			}

			return numberOfTrues;
		}

		$scope.installation = {
			requirements: false,
			opal_setup: false,
			clinical_setup: false,
			config_files: false,
			site_account: false
		};

		// call our API service to verify requirements
		$scope.verifyRequirements = null;
		installCollectionService.verifyRequirements(urlpath).then(function (response) {
			// Handle logic here
			$scope.verifyRequirements = response.data;
			if ($scope.verifyRequirements.config_file.php &&
				$scope.verifyRequirements.config_file.js &&
				$scope.verifyRequirements.config_file.perl) {

				$scope.installation.requirements = true;

				steps.requirements.completed = true;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}
		}).catch(function (response) {
			console.error('Error occurred verifying requirements:', response.status, response.data);
		});

		$scope.opal_setup = {
			host: null,
			port: null,
			name: null,
			username: null,
			password: null,
			message: null
		};

		$scope.checkOpalForm = function () {
			if ($scope.opal_setup.host && $scope.opal_setup.port && $scope.opal_setup.name
				&& $scope.opal_setup.username && $scope.opal_setup.password) {
				return true;
			}
			else return false;
		};

		$scope.testOpalConnection = function () {

			if ($scope.checkOpalForm()) {
				// Ajax call
				$.ajax({
					type: "POST",
					url: 'php/install/check_opal_connection.php',
					data: $scope.opal_setup,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							$scope.installation.opal_setup = true;
							$scope.opal_setup.message = "Connection OK!";
							steps.opal_setup.completed = true;

						}
						else {
							$scope.installation.opal_setup = false;
							$scope.opal_setup.message = response.error;
							steps.opal_setup.completed = false;
						}

						$scope.numOfCompletedSteps = stepsCompleted(steps);
						$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

						$scope.$apply();

					}
				});



			}
		};

		$scope.sourceDBList = [
			{ serial: 1, name: 'Aria', selected: false },
			{ serial: 2, name: 'MediVisit', selected: false },
			{ serial: 3, name: 'Mosaiq', selected: false }
		];


		$scope.clinical_setup = {
			aria: null,
			medivisit: null,
			mosaiq: null
		};

		$scope.clinical_setup.aria = {
			status: null,
			host: null,
			port: null,
			username: null,
			password: null,
			document_path: null,
			message: null
		};

		$scope.checkAriaForm = function () {
			if ($scope.clinical_setup.aria.host && $scope.clinical_setup.aria.username
				&& $scope.clinical_setup.aria.password && $scope.clinical_setup.aria.port) {
				return true;
			}
			else return false;

		};

		$scope.testAriaConnection = function () {

			if ($scope.checkAriaForm()) {

				// Ajax call
				$.ajax({
					type: "POST",
					url: 'php/install/check_aria_connection.php',
					data: $scope.clinical_setup.aria,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							$scope.clinical_setup.aria.status = true;
							$scope.clinical_setup.aria.message = "Connection OK!";
							if ($scope.checkClinicalSetup()) {
								steps.clinical_setup.completed = true;
								$scope.installation.clinical_setup = true;
							}

						}
						else {
							$scope.clinical_setup.aria.status = false;
							$scope.clinical_setup.aria.message = response.error;
							steps.clinical_setup.completed = false;
							$scope.installation.clinical_setup = false;
						}

						$scope.numOfCompletedSteps = stepsCompleted(steps);
						$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

						$scope.$apply();

					}
				});

			}
		};

		$scope.clinical_setup.medivisit = {
			status: null,
			host: null,
			port: null,
			name: null,
			username: null,
			password: null,
			message: null
		};

		$scope.checkMediVisitForm = function () {
			if ($scope.clinical_setup.medivisit.host && $scope.clinical_setup.medivisit.name
				&& $scope.clinical_setup.medivisit.username && $scope.clinical_setup.medivisit.password) {
				return true;
			}
			else return false;
		};

		$scope.testMediVisitConnection = function () {
			if ($scope.checkMediVisitForm()) {

				// Ajax call
				$.ajax({
					type: "POST",
					url: 'php/install/check_medivisit_connection.php',
					data: $scope.clinical_setup.medivisit,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							$scope.clinical_setup.medivisit.status = true;
							$scope.clinical_setup.medivisit.message = "Connection OK!";
							if ($scope.checkClinicalSetup()) {
								steps.clinical_setup.completed = true;
								$scope.installation.clinical_setup = true;
							}

						}
						else {
							$scope.clinical_setup.medivisit.status = false;
							$scope.clinical_setup.medivisit.message = response.error;
							steps.clinical_setup.completed = false;
							$scope.installation.clinical_setup = false;
						}

						$scope.numOfCompletedSteps = stepsCompleted(steps);
						$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

						$scope.$apply();

					}
				});

			}
		};

		$scope.clinical_setup.mosaiq = {
			status: null,
			host: null,
			port: null,
			username: null,
			password: null,
			document_path: null,
			message: null
		};

		$scope.checkMosaiqForm = function () {
			if ($scope.clinical_setup.mosaiq.host && $scope.clinical_setup.mosaiq.username
				&& $scope.clinical_setup.mosaiq.password) {
				return true;
			}
			else return false;
		};

		$scope.testMosaiqConnection = function () {
			if ($scope.checkMosaiqForm()) {

				// Ajax call
				$.ajax({
					type: "POST",
					url: 'php/install/check_mosaiq_connection.php',
					data: $scope.clinical_setup.mosaiq,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							$scope.clinical_setup.mosaiq.status = true;
							$scope.clinical_setup.mosaiq.message = "Connection OK!";
							if ($scope.checkClinicalSetup()) {
								steps.clinical_setup.completed = true;
								$scope.installation.clinical_setup = true;
							}

						}
						else {
							$scope.clinical_setup.mosaiq.status = false;
							$scope.clinical_setup.mosaiq.message = response.error;
							steps.clinical_setup.completed = false;
							$scope.installation.clinical_setup = false;
						}

						$scope.numOfCompletedSteps = stepsCompleted(steps);
						$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

						$scope.$apply();

					}
				});
			}
		};

		$scope.checkClinicalSetup = function () {
			if ($scope.sourceDBList[0].selected) { // Aria
				if (!$scope.clinical_setup.aria.status)
					return false;
			}
			if ($scope.sourceDBList[1].selected) { // mediVisit
				if (!$scope.clinical_setup.medivisit.status)
					return false;
			}
			if ($scope.sourceDBList[2].selected) { // Mosaiq
				if (!$scope.clinical_setup.mosaiq.status)
					return false;
			}

			return true;
		};

		$scope.configurations = {
			message: null
		};
		$scope.submitConfigurations = function () {

			$scope.configs = {
				opal: $scope.opal_setup,
				clinical: $scope.clinical_setup,
				urlpath: urlpath
			};

			// Ajax call
			$.ajax({
				type: "POST",
				url: 'php/install/write_configurations.php',
				data: $scope.configs,
				success: function (response) {
					response = JSON.parse(response);
					if (response.value) {
						steps.config_files.completed = true;
						$scope.installation.config_files = true;
						$scope.configurations.message = null;

					}
					else {
						steps.config_files.completed = false;
						$scope.installation.config_files = false;
						$scope.configurations.message = response.error;
					}

					$scope.numOfCompletedSteps = stepsCompleted(steps);
					$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

					$scope.$apply();

				}
			});

		};

		// Initialize admin user object
		$scope.adminUser = {
			username: null,
			password: null,
			confirmPassword: null,
			status: null,
			message: null
		};

		// Function to validate username 
		$scope.validUsername = { status: null, message: null };
		$scope.validateUsername = function (username) {

			if (!username) {
				$scope.validUsername.status = null;
				return;
			}
			else {
				$scope.validUsername.status = 'valid';
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
				$scope.validPassword.message = 'Use greater than 6 characters';
				return;
			} else {
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

			if ($scope.validPassword.status != 'valid' || $scope.adminUser.password != $scope.adminUser.confirmPassword) {
				$scope.validConfirmPassword.status = 'invalid';
				$scope.validConfirmPassword.message = 'Enter same valid password';
				return;
			} else {
				$scope.validConfirmPassword.status = 'valid';
				$scope.validConfirmPassword.message = null;
			}
		};

		$scope.checkAdminForm = function () {
			if ($scope.validUsername.status == 'valid' && $scope.validPassword.status == 'valid'
				&& $scope.validConfirmPassword.status == 'valid') {
				return true;
			}
			else return false;
		};

		$scope.addAdminUser = function () {
			if ($scope.checkAdminForm()) {

				// Ajax call
				$.ajax({
					type: "POST",
					url: 'php/install/register_admin.php',
					data: $scope.adminUser,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							$scope.installation.site_account = true;
							steps.site_account.completed = true;
							$scope.adminUser.message = "Added " + $scope.adminUser.username + "!";
							$scope.adminUser.status = true;

						}
						else {
							$scope.installation.site_account = false;
							steps.site_account.completed = false;
							$scope.adminUser.message = response.error;
							$scope.adminUser.status = false;
						}

						$scope.numOfCompletedSteps = stepsCompleted(steps);
						$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

						$scope.$apply();
					}
				});
			}
		};

		$scope.checkInstallationForm = function () {
			if ($scope.stepProgress == 100)
				return true;
			else
				return false;
		};

		$scope.installSite = function () {
			if ($scope.checkInstallationForm()) {
				$state.go('login');
			}
		};


	});

