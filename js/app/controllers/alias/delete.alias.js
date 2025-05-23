// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.alias.delete', [])

	.controller('alias.delete', function ($scope, $uibModalInstance, $filter, Session, ErrorHandler) {
		// Submit delete
		$scope.deleteAlias = function () {

			// Log who updated alias
			var currentUser = Session.retrieveObject('user');
			$scope.aliasToDelete.user = currentUser;

			$.ajax({
				type: "POST",
				url: "alias/delete/alias",
				data: $scope.aliasToDelete,
				success: function (response) {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('ALIAS.DELETE.SUCCESS');
					$scope.showBanner();
					$uibModalInstance.close();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('ALIAS.DELETE.ERROR'));
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});
