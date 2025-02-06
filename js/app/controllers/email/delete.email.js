// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.email.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('email.delete', function ($scope, $uibModal, $uibModalInstance, $filter, $state, $sce, emailCollectionService, Session) {

	// Submit delete
	$scope.deleteEmail = function () {
		// Log who deleted email
		var currentUser = Session.retrieveObject('user');
		$scope.emailToDelete.user = currentUser;
		$.ajax({
			type: "POST",
			url: "email/delete/email",
			dataType: "json",
			data: $scope.emailToDelete,
			success: function (response) {
				// Show success or failure depending on response
				if (response.value) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('EMAILS.DELETE.SUCCESS');
					$scope.showBanner();
				}
				else {
					alert($filter('translate')('EMAILS.DELETE.ERROR') + "\r\n\r\n" + response.message);
				}
				$uibModalInstance.close();
			},
			error: function (err) {
				alert($filter('translate')('EMAILS.DELETE.ERROR') + "\r\n\r\n" + err.status + " - " + err.statusText);
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});
