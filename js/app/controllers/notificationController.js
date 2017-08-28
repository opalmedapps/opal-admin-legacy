angular.module('opalAdmin.controllers.notificationController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Controller for the notification page
	*******************************************************************************/
	controller('notificationController', function ($scope, $uibModal, $filter, $state, $sce, notificationCollectionService) {

		// Function to go to add notification page
		$scope.goToAddNotification = function () {
			$state.go('notification-add');
		};

		// Function to control search engine model
		$scope.filterNotification = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Templates for the table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editNotification(row.entity)">' +
			'<a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editNotification(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteNotification(row.entity)">Delete</a></strong></div>';

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

		// Table options for notifications
		$scope.gridOptions = {
			data: 'notificationList',
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

		// Initialize list of existing notifications
		$scope.notificationList = [];

		// Initialize an object for delete a notification
		$scope.notificationToDelete = {};

		// Call our API to get the list of existing notifications
		notificationCollectionService.getNotifications().then(function (response) {
			$scope.notificationList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting notifications:', response.status, response.data);
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

		// Initialize a scope variable for a selected notification
		$scope.currentNotification = {};

		// Function for when the notification has been clicked for editing
		$scope.editNotification = function (notification) {

			$scope.currentNotification = notification;
			var modalInstance = $uibModal.open({
				templateUrl: 'editNotificationModalContent.htm',
				controller: EditNotificationModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the notification list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing notifications
				notificationCollectionService.getNotifications().then(function (response) {

					// Assign the retrieved response
					$scope.notificationList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting notifications:', response.status, response.data);
				});
			});
		};

		// Controller for the edit notification modal
		var EditNotificationModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Default Booleans
			$scope.changesMade = false; // changes have been made? 
			$scope.notification = {}; // initialize notification object

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

			// Call our API to get the current notification details
			notificationCollectionService.getNotificationDetails($scope.currentNotification.serial).then(function (response) {
				$scope.notification = response.data;
				processingModal.close(); // hide modal
				processingModal = null; // remove reference
			}).catch(function(response) {
				console.error('Error occurred getting notification details:', response.status, response.data);
			});

			// Function to check necessary form fields are complete
			$scope.checkForm = function () {
				if ($scope.notification.name_EN && $scope.notification.name_FR
					&& $scope.notification.description_EN && $scope.notification.description_FR
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
			$scope.updateNotification = function () {
				if ($scope.checkForm()) {
					// Submit form
					$.ajax({
						type: "POST",
						url: "php/notification/update.notification.php",
						data: $scope.notification,
						success: function (response) {
							response = JSON.parse(response);
							// Show success or failure depending on response
							if (response.value) {
								$scope.setBannerClass('success');
								$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.notification.name_EN + "/ " + $scope.notification.name_FR + "\"!";
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

		// Function for when the notification has been clicked for deletion
		// Open a modal
		$scope.deleteNotification = function (currentNotification) {

			// Assign selected notification as the item to delete
			$scope.notificationToDelete = currentNotification;
			var modalInstance = $uibModal.open({
				templateUrl: 'deleteNotificationModalContent.htm',
				controller: DeleteNotificationModalInstanceCtrl,
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the map list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing notifications
				notificationCollectionService.getNotifications().then(function (response) {
					// Assign the retrieved response
					$scope.notificationList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting notifications:', response.status, response.data);
				});
			});
		};

		// Controller for the delete notification modal
		var DeleteNotificationModalInstanceCtrl = function ($scope, $uibModalInstance) {

			// Submit delete
			$scope.deleteNotification = function () {
				$.ajax({
					type: "POST",
					url: "php/notification/delete.notification.php",
					data: $scope.notificationToDelete,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.notificationToDelete.name_EN + "/ " + $scope.notificationToDelete.name_FR + "\"!";
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
