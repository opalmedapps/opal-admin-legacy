angular.module('opalAdmin.controllers.masterSourceTestResult.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('masterSourceTestResult.edit', function ($scope, $filter, $uibModal, $uibModalInstance, masterSourceCollectionService, Session, ErrorHandler) {
	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;

	$scope.readOnly = {
		name_display: null,
		externalId: null,
		code: null,
		SourceDatabaseName: null,
	};

	$scope.toSubmit = {
		details: {
			description: null,
		},
	};

	$scope.validator = {
		details: {
			completed: false,
			mandatory: true,
		},
	};

	var arrValidationUpdate = [
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.VALIDATION_SOURCE'),
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.VALIDATION_EXTERNAL_ID'),
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.VALIDATION_CODE'),
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.VALIDATION_DESCRIPTION'),
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.NOT_FOUND')
	];

	var arrValidationGetDetails = [
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.VALIDATION_SOURCE'),
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.VALIDATION_CODE'),
		$filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.NOT_FOUND')
	];
	
	$scope.language = Session.retrieveObject('user').language;

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
	// Call our API service to get the current test result details
	masterSourceCollectionService.getTestResultDetails($scope.currentTestResult.code, $scope.currentTestResult.source).then(function (response) {
		$scope.toSubmit.details = {
			description: response.data.description,
		};

		$scope.readOnly.externalId = response.data.externalId;
		$scope.readOnly.code = response.data.code;
		$scope.readOnly.SourceDatabaseName = response.data.SourceDatabaseName;
		if($scope.language.toUpperCase() === "FR")
			$scope.readOnly.name_display = response.data.name_FR;
		else
			$scope.readOnly.name_display = response.data.Name_EN;

		$scope.changesDetected = false;
		$scope.oldData = JSON.parse(JSON.stringify($scope.toSubmit));
	}).catch(function(err) {
		err.responseText = err.data;
		ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.ERROR_DETAILS'), arrValidationGetDetails);
		$uibModalInstance.close();
	}).finally(function() {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	});

	$scope.$watch('toSubmit', function() {
		$scope.changesDetected = JSON.stringify($scope.toSubmit) != JSON.stringify($scope.oldData);
	}, true);

	$scope.$watch('validator', function() {
		var totalsteps = 0;
		var completedSteps = 0;
		var nonMandatoryTotal = 0;
		var nonMandatoryCompleted = 0;
		angular.forEach($scope.validator, function(value) {
			if(value.mandatory)
				totalsteps++;
			else
				nonMandatoryTotal++;
			if(value.mandatory && value.completed)
				completedSteps++;
			else if(!value.mandatory && value.completed)
				nonMandatoryCompleted++;
		});
		$scope.formReady = (completedSteps >= totalsteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
	}, true);

	$scope.detailsUpdate = function () {
		$scope.validator.details.completed = ($scope.toSubmit.details.description != undefined);
	};

	// Submit changes
	$scope.updateCustomCode = function() {
		if($scope.formReady && $scope.changesDetected) {
			var ready = {};
			ready[0] = {
				source: $scope.currentTestResult.source,
				code: $scope.readOnly.code,
				description: $scope.toSubmit.details.description
			};
			$.ajax({
				type: "POST",
				url: "master-source/update/test-results",
				data: ready,
				success: function () {},
				error: function (err) {
					err.responseText = JSON.parse(err.responseText)[0];
					ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.TEST_RESULT_EDIT.ERROR_UPDATE'), arrValidationUpdate);
				},
				complete: function () {
					$uibModalInstance.close();
				}
			});
		}
	};

	// Function to close modal dialog
	$scope.cancel = function () {
		$uibModalInstance.dismiss('cancel');
	};
});