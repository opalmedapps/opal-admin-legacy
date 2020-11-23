angular.module('opalAdmin.controllers.masterSourceAlias.delete', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('masterSourceAlias.delete', function ($scope, $filter, $uibModal, $uibModalInstance, uiGridConstants, $state, Session, ErrorHandler) {

	var arrValidationDelete = [
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_DELETE.VALIDATION_SOURCE'),
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_DELETE.VALIDATION_EXTERNAL_ID'),
		$filter('translate')('MASTER_SOURCE_MODULE.ALIAS_DELETE.IN_USE'),
	];

	// Submit delete
	$scope.deleteMasterSourceAlias = function () {
		$scope.toDelete = {};
		$scope.toDelete[0] = {
			"externalId": $scope.aliasToDelete.externalId,
			"source": $scope.aliasToDelete.source
		};

		$.ajax({
			type: "POST",
			url: "master-source/delete/aliases",
			data: $scope.toDelete,
			success: function () {
				$scope.setBannerClass('success');
				$scope.$parent.bannerMessage = $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_DELETE.DELETED');
				$scope.showBanner();
			},
			error: function(err) {
				err.responseText = JSON.parse(err.responseText)[0];
				ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.ALIAS_DELETE.ERROR'), arrValidationDelete);
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