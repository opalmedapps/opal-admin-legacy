angular.module('opalAdmin.controllers.hospitalMap.delete', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	controller('hospitalMap.delete', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, hospitalMapCollectionService, Session) {

		// Submit delete
		$scope.deleteHospitalMap = function () {
			// Log who deleted hospital map
			var currentUser = Session.retrieveObject('user');
			$scope.hosMapToDelete.user = currentUser;
			$.ajax({
				type: "POST",
				url: "php/hospital-map/delete.hospital_map.php",
				data: $scope.hosMapToDelete,
				success: function () {
					$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.hosMapToDelete.name_EN + "/ " + $scope.hosMapToDelete.name_FR + "\"!";
					$scope.showBanner();
					$uibModalInstance.close();
				}
			});
		};

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};


	});