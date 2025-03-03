// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.educationalMaterial.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns']).

controller('educationalMaterial.delete', function ($scope, $filter, $sce, $uibModal, $uibModalInstance, $state, uiGridConstants, Session, ErrorHandler) {

	// Submit delete
	$scope.deleteEducationalMaterial = function () {
		// Log who deleted educational material
		var currentUser = Session.retrieveObject('user');
		$scope.eduMatToDelete.user = currentUser;

		$.ajax({
			type: "POST",
			url: "educational-material/delete/educational-material",
			dataType: "json",
			data: $scope.eduMatToDelete,
			success: function (response) {
				// Show success or failure depending on response
				if (response.value) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('EDUCATION.DELETE.SUCCESS');
					$scope.showBanner();
				}
				else {
					ErrorHandler.onError(response, $filter('translate')('EDUCATION.DELETE.ERROR'));
				}
				$uibModalInstance.close();
			},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('EDUCATION.DELETE.ERROR'));
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});