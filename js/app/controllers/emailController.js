angular.module('opalAdmin.controllers.emailController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Controller for the email page
	*******************************************************************************/
	controller('emailController', function ($scope, $uibModal, $filter, $state, $sce, emailCollectionService, Session) {

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
			'<a href="">{{row.entity.subject_EN}} / {{row.entity.subject_FR}}</a></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editEmail(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteEmail(row.entity)">Delete</a></strong></div>';

		// Search engine for table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['subject_EN'].forEach(function (field) {
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
				{ field: 'subject_EN', displayName: 'Title (EN)', cellTemplate: cellTemplateName, width: '40%' },
				{ field: 'type', displayName: 'Type', width: '45%' },
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
		emailCollectionService.getEmails().then(function (response) {
			$scope.emailList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting email list:', response.status, response.data);
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
				emailCollectionService.getEmails().then(function (response) {

					// Assign the retrieved response
					$scope.emailList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting email list:', response.status, response.data);
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
			emailCollectionService.getEmailDetails($scope.currentEmail.serial).then(function (response) {
				$scope.email = response.data;
				processingModal.close(); // hide modal
				processingModal = null; // remove reference
			}).catch(function(response) {
				console.error('Error occurred getting email details:', response.status, response.data);
			});

			// Function to check necessary form fields are complete
			$scope.checkForm = function () {
				if ($scope.email.subject_EN && $scope.email.subject_FR
					&& $scope.email.body_EN && $scope.email.body_FR
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
					// Log who updated email
					var currentUser = Session.retrieveObject('user');
					$scope.email.user = currentUser;
					// Submit form
					$.ajax({
						type: "POST",
						url: "php/email/update.email.php",
						data: $scope.email,
						success: function (response) {
							response = JSON.parse(response);
							// Show success or failure depending on response
							if (response.value) {
								$scope.setBannerClass('success');
								$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.email.subject_EN + "/ " + $scope.email.subject_FR + "\"!";
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
				emailCollectionService.getEmails().then(function (response) {
					// Assign the retrieved response
					$scope.emailList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting email list:', response.status, response.data);
				});
			});
		};

		// Controller for the delete email modal
		var DeleteEmailModalInstanceCtrl = function ($scope, $uibModalInstance) {
			// Submit delete
			$scope.deleteEmail = function () {
				// Log who deleted email
				var currentUser = Session.retrieveObject('user');
				$scope.emailToDelete.user = currentUser;
				$.ajax({
					type: "POST",
					url: "php/email/delete.email.php",
					data: $scope.emailToDelete,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.emailToDelete.subject_EN + "/ " + $scope.emailToDelete.subject_FR + "\"!";
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
