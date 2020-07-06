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