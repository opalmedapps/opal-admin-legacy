// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.testResult.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('testResult.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, Session, ErrorHandler) {
	// Submit delete
	$scope.deleteTestResult = function () {
		// Log who deleted test result
		var currentUser = Session.retrieveObject('user');
		$scope.testResultToDelete.user = currentUser;
		$.ajax({
			type: "POST",
			url: "test-result/delete/test-result",
			data: $scope.testResultToDelete,
			success: function () {},
			error: function (err) {
				ErrorHandler.onError(err, $filter('translate')('TEST.DELETE.ERROR'));
			},
			complete: function () {
				$uibModalInstance.close();
			}
		});
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});