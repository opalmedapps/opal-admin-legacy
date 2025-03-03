// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.notification.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('notification.edit', function ($scope, $uibModal, $uibModalInstance, $filter, $state, $sce, notificationCollectionService, Session, ErrorHandler) {

		// Default Booleans
		$scope.changesMade = false; // changes have been made? 
		$scope.notification = {}; // initialize notification object

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

		// Call our API to get the current notification details
		notificationCollectionService.getNotificationDetails($scope.currentNotification.serial).then(function (response) {
			$scope.notification = response.data;
			processingModal.close(); // hide modal
			processingModal = null; // remove reference
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('NOTIFICATIONS.EDIT.ERROR_DETAILS'));
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
				// Log who updated notification
				var currentUser = Session.retrieveObject('user');
				$scope.notification.user = currentUser;
				// Submit form
				$.ajax({
					type: "POST",
					url: "notification/update/notification",
					data: $scope.notification,
					success: function () {},
					error: function (err) {
						ErrorHandler.onError(err, $filter('translate')('NOTIFICATIONS.EDIT.ERROR_EDIT'));
					},
					complete: function () {
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