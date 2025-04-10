// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.customCode.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('customCode.delete', function ($scope, $filter, $uibModal, $uibModalInstance, uiGridConstants, $state, Session, ErrorHandler) {

	// Submit delete
	$scope.deleteCustomCode = function () {
		$scope.toDelete = {};
		$scope.toDelete.customCodeId = $scope.customCodeToDelete.ID;
		$scope.toDelete.moduleId = $scope.customCodeToDelete.moduleId;
		$scope.toDelete.OAUserId = Session.retrieveObject('user').id;

		$.ajax({
			type: "POST",
			url: "custom-code/delete/custom-code",
			data: $scope.toDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('CUSTOM_CODE.DELETE.DELETED');
				$scope.showBanner();
			},
			error: function(err) {
				ErrorHandler.onError(err, $filter('translate')('CUSTOM_CODE.DELETE.ERROR'));
			},
			complete: function() {
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};


});
