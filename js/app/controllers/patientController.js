angular.module('opalAdmin.controllers.patientController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Patient Page controller 
	*******************************************************************************/
	controller('patientController', function ($scope, $filter, $sce, $state, $uibModal, patientCollectionService) {

		// Function to go to register new patient
		$scope.goToAddPatient = function () {
			$state.go('patients-register');
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

		$scope.changesMade = false;

		// Templates for the patient table
		var checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkTransferFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updateTransferFlag(row.entity.transfer)" ng-model="row.entity.transfer"></div>';

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editPatient(row.entity)">Edit</a></strong>' +
			'- <strong><a href="" ng-click="grid.appScope.toggleBlock(row.entity)"><span ng-if="row.entity.disabled">Unblock</span>' + 
			'<span ng-if="!row.entity.disabled">Block</span></a></strong></div> '; 

		// patient table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name', 'patientid'].forEach(function (field) {
					if (row.entity[field].match(matcher)) {
						match = true;
					}
				});
				if (!match) {
					row.visible = false;
				}
			});

			return renderableRows;
		};


		$scope.filterPatient = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table options for patient
		$scope.gridOptions = {
			data: 'patientList',
			columnDefs: [
				{ field: 'patientid', displayName: 'Patient ID', width: '25%' },
				{ field: 'name', displayName: 'Name', width: '25%' },
				{ field: 'transfer', displayName: 'Publish Flag', width: '10%', cellTemplate: checkboxCellTemplate, enableFiltering: false },
				{ field: 'lasttransferred', displayName: 'Last Publish', width:'20%' },
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '20%'}

			],
			enableFiltering: true,
			//useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},

		};

		// Initialize list of existing patients
		$scope.patientList = [];
		$scope.patientTransfers = {
			transferList: []
		};

		// Call our API to get the list of existing patients
		patientCollectionService.getPatients().then(function (response) {
			// Assign value
			$scope.patientList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting patient list:', response.status, response.data);
		});

		// When this function is called, we set the "publish" field to checked 
		// or unchecked based on value in the argument
		$scope.updateTransferFlag = function (value) {
			value = parseInt(value);
			if (value == 1) {
				return 1;
			} else {
				return 0;
			}
		};


		// Function for when the patient checkbox has been modified
		$scope.checkTransferFlag = function (patient) {

			$scope.changesMade = true;
			patient.transfer = parseInt(patient.transfer);
			// If the "transfer" column has been checked
			if (patient.transfer) {
				patient.transfer = 0; // set transfer to "false"
			}

			// Else the "Transfer" column was unchecked
			else {
				patient.transfer = 1; // set transfer to "true"
			}
		};

		// Function to submit changes when transfer flags have been modified
		$scope.submitTransferFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.patientList, function (patient) {
					$scope.patientTransfers.transferList.push({
						serial: patient.serial,
						transfer: patient.transfer
					});
				});
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/patient/update.patient_publish_flags.php",
					data: $scope.patientTransfers,
					success: function () {
						// Call our API to get the list of existing patients
						patientCollectionService.getPatients().then(function (response) {
							// Assign value
							$scope.patientList = response.data;
						}).catch(function(response) {
							console.error('Error occurred getting patient list:', response.status, response.data);
						});
						$scope.bannerMessage = "Transfer Flags Saved!";
						$scope.showBanner();
						$scope.changesMade = false;
					}
				});
			}
		};

		// Function for when a user has been clicked for (un)blocking
		// Open a modal
		$scope.patientToToggleBlock = null;
		$scope.toggleBlock = function (currentPatient) {

			$scope.patientToToggleBlock = currentPatient;
			var modalInstance = $uibModal.open({
				templateUrl: 'toggleBlockModalContent.htm',
				windowClass: 'customModal',
				controller: toggleBlockModalInstanceCtrl,
				scope: $scope,
				backdrop: 'static'
			});

			// After toggle, refresh the patient list 
			modalInstance.result.then(function () {
				// Call our API to get the list of existing patients
				patientCollectionService.getPatients().then(function (response) {
					// Assign value
					$scope.patientList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting patient list:', response.status, response.data);
				});
			});
		};

		// Controller for the toggle block modal
		var toggleBlockModalInstanceCtrl = function ($scope, $uibModalInstance) {

			$scope.currentPatient = jQuery.extend(true, {}, $scope.patientToToggleBlock);

			console.log($scope.currentPatient);
			// toggle block immediately
			if ($scope.currentPatient.disabled == 0)
				$scope.currentPatient.disabled = 1;
			else
				$scope.currentPatient.disabled = 0;

			// Submit (un)block
			$scope.submitToggle = function () {

				if ($scope.currentPatient.reason) {

					// Database (un)block
					$.ajax({
						type: "POST",
						url: "php/patient/toggle_block.php",
						data: $scope.currentPatient,
						success: function (response) {
							response = JSON.parse(response);
							if (response.value) {
								var toggleText = "blocked";
								if (!$scope.currentPatient.disabled)
									toggleText = "unblocked";
								$scope.setBannerClass('success');
								$scope.$parent.bannerMessage = "Successfully " + toggleText + " \"" + $scope.currentPatient.name + "\"";
							}
							else {
								$scope.setBannerClass('danger');
								$scope.$parent.bannerMessage = response.message;
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

		};

		// Function for when the patient has been clicked for editing
		// Open a modal
		$scope.currentPatient = null;
		$scope.editPatient = function (patient) {

			$scope.currentPatient = patient;
			var modalInstance = $uibModal.open({
				templateUrl: 'editPatientModalContent.htm',
				controller: EditPatientModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
				keyboard: false,
			});

			// After update, refresh the patient list 
			modalInstance.result.then(function () {
				// Call our API to get the list of existing patients
				patientCollectionService.getPatients().then(function (response) {
					// Assign value
					$scope.patientList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting patient list:', response.status, response.data);
				});
			});
		};

		// Controller for the edit patient modal
		var EditPatientModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Default booleans
			$scope.changesMade = false;
			
			$scope.patient = {};

			/* Function for the "Processing" dialog */
			var processingModal;
			$scope.showProcessingModal = function () {

				processingModal = $uibModal.open({
					templateUrl: 'processingModal.htm',
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

					// Authenticate user using username and old password
					FB.auth().signInWithEmailAndPassword($scope.patient.email, $scope.patient.oldPassword)
						.then(function (firebaseUser) {
							// On successful login, update password in Firebase
							firebaseUser.updatePassword($scope.patient.password)
								.then(function (){
									// submit new password to database
									console.log("Successfully update firebase password!");

									$scope.patient.password = CryptoJS.SHA256($scope.patient.password).toString();

									$.ajax({
										type: "POST",
										url: "php/patient/update.patient.php",
										data: $scope.patient,
										success: function (response) {
											response = JSON.parse(response);
											if (response.value) {
												$scope.setBannerClass('success');
												$scope.$parent.bannerMessage = "Successfully update \"" + $scope.patient.name + "\"";
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

		};

	});


