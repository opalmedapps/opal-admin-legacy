// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.user.delete', ['ui.bootstrap', 'ui.grid']).

controller('user.delete', function ($scope, $uibModal, $uibModalInstance,  $filter, $sce, $state, userCollectionService, Session, ErrorHandler) {

	// Submit delete
	$scope.deleteUser = function () {
		if($scope.userToDelete.serial !== Session.retrieveObject('user').id) {
			$.ajax({
				type: "POST",
				url: "user/delete/user",
				data: {"ID": $scope.userToDelete.serial, "OAUserId": Session.retrieveObject('user').id, "username": $scope.userToDelete.username},
				success: function () {
					$scope.setBannerClass('success');
					$scope.$parent.bannerMessage = $filter('translate')('USERS.DELETE.SUCCESS');
					$scope.showBanner();
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('USERS.DELETE.ERROR'));
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		}
		else {
			alert($filter('translate')('USERS.DELETE.ERROR_SAME_USER'));
			$uibModalInstance.close();
		}
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});
