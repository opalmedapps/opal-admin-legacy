angular.module('opalAdmin.controllers.userController', ['ui.bootstrap', 'ui.grid']).


	/******************************************************************************
	* Controller for the users page
	*******************************************************************************/
	controller('userController', function ($scope, $uibModal, $filter, $sce, $state, userCollectionService, Encrypt) {

		// Function to go to register new user
		$scope.goToAddUser = function () {
			$state.go('user-register');
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

		// Templates for the users table
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editUser(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteUser(row.entity)">Delete</a></strong></div>';

		// user table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['username'].forEach(function (field) {
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

		$scope.filterUser = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table options for user
		$scope.gridOptions = {
			data: 'userList',
			columnDefs: [
				{ field: 'username', displayName: 'Username', width: '45%' },
				{ field: 'role', displayName: 'Role', width: '25%' },
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '30%' }
			],
			enableColumnResizing: true,
			enableFiltering: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list of existing users
		$scope.userList = [];

		// Call out API service to get the list of existing users
		userCollectionService.getUsers().then(function (response) {
			$scope.userList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting user list:', response.status, response.data);
		});

		// Function for when a user has been clicked for deletion
		// Open a modal
		$scope.userToDelete = null;
		$scope.deleteUser = function (currentUser) {

			$scope.userToDelete = currentUser;
			var modalInstance = $uibModal.open({
				templateUrl: 'deleteUserModalContent.htm',
				windowClass: 'deleteModal',
				controller: DeleteUserModalInstanceCtrl,
				scope: $scope,
				backdrop: 'static'
			});

			// After delete, refresh the user list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing users
				userCollectionService.getUsers().then(function (response) {
					$scope.userList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting user list:', response.status, response.data);
				});
			});
		};

		// Controller for the delete user modal
		var DeleteUserModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Submit delete
			$scope.deleteUser = function () {
				$.ajax({
					type: "POST",
					url: "php/user/delete.user.php",
					data: $scope.userToDelete,
					success: function (response) {
						response = JSON.parse(response);
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully delete \"" + $scope.userToDelete.username + "\"";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}
						$scope.showBanner();
						$uibModalInstance.close();

					}
				});
			};

			// Function to close modal dialog
			$scope.cancel = function () {
				$uibModalInstance.dismiss('cancel');
			};

		};

		// Function for when the user has been clicked for editing 
		// We open a modal
		$scope.editUser = function (user) {

			$scope.currentUser = user;
			var modalInstance = $uibModal.open({
				templateUrl: 'editUserModalContent.htm',
				controller: EditUserModalInstanceCtrl,
				scope: $scope,
				windowClass: 'editUserModal',
				backdrop: 'static'
			});

			// After update, refresh the user list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing users
				userCollectionService.getUsers().then(function (response) {
					$scope.userList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting user list:', response.status, response.data);
				});
			});
		};

		// Controller for the edit user modal
		var EditUserModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Default booleans
			$scope.changesMade = false;
			$scope.passwordChange = false;

			$scope.user = {};

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
					// one-time pad using current time and rng
					var cypher = (moment().unix() % (Math.floor(Math.random() * 20))) + 103; 
					// encode passwords before request
					user.password = Encrypt.encode(user.password, cypher);
					user.confirmPassword = Encrypt.encode(user.confirmPassword, cypher);
					user.cypher = cypher;

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

		};





	});

