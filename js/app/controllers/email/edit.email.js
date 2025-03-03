// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.email.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('email.edit', function ($scope, $uibModal, $uibModalInstance, $filter, $state, $sce, emailCollectionService, Session) {

	// Default Booleans
	$scope.changesMade = false; // changes have been made?
	$scope.email = {}; // initialize email object

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

	// Call our API to get the current email details
	emailCollectionService.getEmailDetails($scope.currentEmail.serial).then(function (response) {
		$scope.email = response.data;
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	}).catch(function(response) {
		alert($filter('translate')('EMAILS.EDIT.ERROR_DETAILS') + "\r\n\r\n" + response.status + " - " + response.data);
		processingModal.close(); // hide modal
		$uibModalInstance.close();
		processingModal = null; // remove reference
	});

	// Function to check necessary form fields are complete
	$scope.checkForm = function () {
		return ($scope.email.subject_EN && $scope.email.subject_FR && $scope.email.body_EN && $scope.email.body_FR && $scope.changesMade);
	};

	$scope.setChangesMade = function () {
		$scope.changesMade = true;
	};

	// Submit changes
	$scope.updateEmail = function () {
		if ($scope.checkForm()) {
			// For some reason the HTML text fields add a zero-width-space
			// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
			$scope.email.body_EN = $scope.email.body_EN.replace(/\u200B/g,'');
			$scope.email.body_FR = $scope.email.body_FR.replace(/\u200B/g,'');

			// Log who updated email
			var currentUser = Session.retrieveObject('user');
			$scope.email.user = currentUser;
			// Submit form
			$.ajax({
				type: "POST",
				url: "email/update/ema	il",
				dataType: "json",
				data: $scope.email,
				success: function (response) {
					// Show success or failure depending on response
					if (response.value) {
						$scope.setBannerClass('success');
						$scope.$parent.bannerMessage = $filter('translate')('EMAILS.EDIT.SUCCESS_EDIT');
						$scope.showBanner();
					}
					else
						alert($filter('translate')('EMAILS.EDIT.ERROR_UPDATE'));
					$uibModalInstance.close();
				},
				error: function(err) {
					alert($filter('translate')('EMAILS.EDIT.ERROR_UPDATE') + "\r\n\r\n" + err.status + " - " + err.statusText);
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
