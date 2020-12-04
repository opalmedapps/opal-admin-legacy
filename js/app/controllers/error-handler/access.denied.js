angular.module('opalAdmin.controllers.errorHandler.accessDenied', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('errorHandler.accessDenied', function ($scope, $filter, $uibModal, $uibModalInstance, $state) {

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
		$state.go('home');
	};
});