angular.module('opalAdmin.controllers.patient.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('patient.edit', function ($rootScope, $scope, $filter, $sce, $state, $uibModal, $uibModalInstance, patientCollectionService) {

		// Default booleans
		$scope.changesMade = false;
		
		$scope.patient = {};

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

		$scope.patientIsDisabled = false;

		// Call our API service to get the current patient details
		patientCollectionService.getPatientDetails($scope.currentPatient.serial).then(function (response){

			$scope.patient = response.data;

			// if the patient is block, revoke ability to change password
			if ($scope.patient.disabled) {

				$scope.patientIsDisabled = true;

				$scope.validOldPassword.status = 'invalid';
				$scope.validOldPassword.message = 'Account is blocked. Unblock first to change password';

			}

			processingModal.close(); // hide processing modal
			processingModal = null; // revoke reference
		}).catch(function(response) {
			console.error('Error occurred getting patient details:', response.status, response.data);
		});

		// Function to validate old password
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

			$scope.passwordChange = true;
			$scope.validateConfirmPassword($scope.patient.confirmPassword);

			if (!password) {
				$scope.validPassword.status = null;
				if (!$scope.validConfirmPassword)
					$scope.passwordChange = false;
				return;
			}

			if (password.length < 6) {
				$scope.validPassword.status = 'invalid';
				$scope.validPassword.message = 'Use greater than 6 characters';
				return;
			} else {
				$scope.validPassword.status = 'valid';
				$scope.validPassword.message = null;
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
				if (!$scope.validPassword)
					$scope.passwordChange = false;
				return;
			}

			if ($scope.validPassword.status != 'valid' || $scope.patient.password != $scope.patient.confirmPassword) {
				$scope.validConfirmPassword.status = 'invalid';
				$scope.validConfirmPassword.message = 'Enter same valid password';
				return;
			} else {
				$scope.validConfirmPassword.status = 'valid';
				$scope.validConfirmPassword.message = null;
				if ($scope.validPassword.status == 'valid')
					$scope.passwordChange = false;
			}
		};

		// Function to check for form completion
		$scope.checkForm = function() {
			if ( $scope.validOldPassword.status == 'valid' && $scope.validPassword.status == 'valid' && $scope.validConfirmPassword.status == 'valid' )
				return true;
			else
				return false;
		};

		// Submit changes
		$scope.updatePatient = function () {

			if ($scope.checkForm()) {

				// deep copy patient object
				var patient = jQuery.extend(true, {}, $scope.patient);

				// Authenticate user using username and old password
				firebase.auth().signInWithEmailAndPassword(patient.email, patient.oldPassword)
					.then(function (firebaseUser) {

						// On successful login, update password in Firebase
						firebaseUser.updatePassword(patient.password)
							.then(function (){
								// submit new password to database
								// patient.password = CryptoJS.SHA256(patient.password).toString();
								patient.password = CryptoJS.SHA512(patient.password).toString();

								// encrypting other password fields before post to avoid readable password
								patient.oldPassword = CryptoJS.SHA512(patient.oldPassword).toString();
								patient.confirmPassword = CryptoJS.SHA512(patient.confirmPassword).toString();


								$.ajax({
									type: "POST",
									url: "php/patient/update.patient.php",
									data: patient,
									success: function (response) {
										response = JSON.parse(response);
										if (response.value) {
											$scope.setBannerClass('success');
											$scope.$parent.bannerMessage = "Successfully update \"" + patient.name + "\"";
										}
										else {
											$scope.setBannerClass('danger');
											$scope.$parent.bannerMessage = response.error.message;
										}

										$scope.showBanner();
										$uibModalInstance.close();
									}
								});

							})
							.catch(function (error) {
								console.log(error);
							});
					})
					.catch (function (error){

						console.log(error);
						// On failed login, handle errors
						var errorCode = error.code;
						if (errorCode == 'auth/user-disabled') {
							// set user disabled message.. Should never happen
							console.log("Patient is still disabled");
						} 
						else if (errorCode == 'auth/wrong-password') {
							// set password is incorrect message
							$scope.validOldPassword.status = 'invalid';
							$scope.validOldPassword.message = 'Old password is incorrect';
							$scope.$apply();

						}
					});
				
			}
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};

	});