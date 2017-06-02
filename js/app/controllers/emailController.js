angular.module('opalAdmin.controllers.emailController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Controller for the email page
	*******************************************************************************/
	controller('emailController', function ($scope, $uibModal, $filter, $state, $sce, notifAPIservice) {

		// Function to go to add email page
		$scope.goToAddEmail = function () {
			$state.go('email-add');
		};

		// Function to control search engine model
		$scope.filterEmail = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Templates for the table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editEmail(row.entity)">' +
			'<a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editEmail(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteEmail(row.entity)">Delete</a></strong></div>';

		// Search engine for table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN'].forEach(function (field) {
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

		// Table options for email list
		$scope.gridOptions = {
			data: 'emailList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '40%' },
				{ field: 'type', displayName: 'Type', width: '15%' },
				{ field: 'description_EN', displayName: 'Message (EN)', width: '30%' },
				{ name: 'Operations', width: '15%', cellTemplate: cellTemplateOperations, sortable: false }
			],
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list of existing emails
		$scope.emailList = [];

		// Initialize an object for delete an email
		$scope.emailToDelete = {};

		// Call our API to get the list of existing emails
		notifAPIservice.getEmails().success(function (response) {
			$scope.emailList = response;
		});

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

		// Initialize a scope variable for a selected email
		$scope.currentEmail = {};

		// Function for when the email has been clicked for editing
		$scope.editEmail = function (email) {

			$scope.currentEmail = email;
			var modalInstance = $uibModal.open({
				templateUrl: 'editEmailModalContent.htm',
				controller: EditEmailModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the email list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing emails
				notifAPIservice.getEmails().success(function (response) {

					// Assign the retrieved response
					$scope.emailList = response;
				});
			});
		};

		// Controller for the edit email modal
		var EditEmailModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Default Booleans
			$scope.changesMade = false; // changes have been made? 
			$scope.email = {}; // initialize email object

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

			// Call our API to get the current email details
			notifAPIservice.getEmailDetails($scope.currentEmail.serial).success(function (response) {
				$scope.email = response;
				processingModal.close(); // hide modal
				processingModal = null; // remove reference
			});

			// Function to check necessary form fields are complete
			$scope.checkForm = function () {
				if ($scope.email.name_EN && $scope.email.name_FR
					&& $scope.email.description_EN && $scope.email.description_FR
					&& $scope.changesMade) {
					return true;
				}
				else
					return false;
			};

			$scope.setChangesMade = function () {
				$scope.changesMade = true;
			};

			// Submit changes
			$scope.updateEmail = function () {
				if ($scope.checkForm()) {
					// Submit form
					$.ajax({
						type: "POST",
						url: "php/email/update_email.php",
						data: $scope.email,
						success: function (response) {
							response = JSON.parse(response);
							// Show success or failure depending on response
							if (response.value) {
								$scope.setBannerClass('success');
								$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.email.name_EN + "/ " + $scope.email.name_FR + "\"!";
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

		// Function for when the email has been clicked for deletion
		// Open a modal
		$scope.deleteEmail = function (currentEmail) {

			// Assign selected email as the item to delete
			$scope.emailToDelete = currentEmail;
			var modalInstance = $uibModal.open({
				templateUrl: 'deleteEmailModalContent.htm',
				controller: DeleteEmailModalInstanceCtrl,
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the map list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing emails
				notifAPIservice.getEmails().success(function (response) {
					// Assign the retrieved response
					$scope.emailList = response;
				});
			});
		};

		// Controller for the delete email modal
		var DeleteEmailModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Submit delete
			$scope.deleteEmail = function () {
				$.ajax({
					type: "POST",
					url: "php/email/delete_email.php",
					data: $scope.emailToDelete,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.emailToDelete.name_EN + "/ " + $scope.emailToDelete.name_FR + "\"!";
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


	});
