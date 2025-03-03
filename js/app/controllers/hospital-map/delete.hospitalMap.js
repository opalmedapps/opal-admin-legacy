// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.hospitalMap.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('hospitalMap.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, hospitalMapCollectionService, Session, ErrorHandler) {

	$scope.deleteHospitalMap = function () {
		$scope.hosMapToDelete.user = Session.retrieveObject('user');
		$.ajax({
			type: "POST",
			url: "hospital-map/delete/hospital-map",
			data: $scope.hosMapToDelete,
			success: function () {
				$scope.$parent.bannerMessage = $filter('translate')('HOSPITAL_MAPS.DELETE.SUCCESS');
				$scope.showBanner();
			},
			error: function(err) {
				ErrorHandler.onError(err, $filter('translate')('HOSPITAL_MAPS.DELETE.ERROR'));
			},
			complete: function() {
				$uibModalInstance.close();
			}
		});
	};

	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});