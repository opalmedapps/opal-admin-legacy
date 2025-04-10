// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.audit.view', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('audit.view', function ($scope, $filter, $uibModal, $uibModalInstance, $locale, auditCollectionService, Session, ErrorHandler) {

	$scope.language = Session.retrieveObject('user').language;
	$scope.auditDetails = {};
	$scope.previewArgument = null;

	/* Function for the "Processing" dialog */
	var processingModal;
	$scope.showProcessingModal = function () {

		processingModal = $uibModal.open({
			templateUrl: 'templates/processingModal.html',
			backdrop: 'static',
			keyboard: false,
		});
	};
	// Show processing dialog
	$scope.showProcessingModal();

	// Call our API service to get the current diagnosis translation details
	auditCollectionService.getAuditDetails($scope.currentAudit.ID).then(function (response) {
		$scope.auditDetails = response.data;
		$scope.auditDetails["argument"] = JSON.parse( $scope.auditDetails["argument"]);
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('AUDIT.VIEW.ERROR_DETAILS'));
	}).finally(function() {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	});

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});
