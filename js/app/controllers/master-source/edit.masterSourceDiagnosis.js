angular.module('opalAdmin.controllers.masterSourceDiagnosis.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('masterSourceDiagnosis.edit', function ($scope, $filter, $uibModal, $uibModalInstance, masterSourceCollectionService, Session, ErrorHandler) {
	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;

	$scope.readOnly = {
		name_display: null,
		externalId: null,
		SourceDatabaseName: null,
	};

	$scope.toSubmit = {
		details: {
			code: null,
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
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.VALIDATION_SOURCE'),
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.VALIDATION_EXTERNAL_ID'),
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.VALIDATION_CODE'),
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.VALIDATION_DESCRIPTION'),
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.VALIDATION_DATE'),
	];

	var arrValidationGetDetails = [
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.NO_ID'),
		$filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.NOT_FOUND')
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

	// Call our API service to get the current diagnosis translation details
	masterSourceCollectionService.getDiagnosisDetails($scope.currentDiagnosis.externalId, $scope.currentDiagnosis.source).then(function (response) {
		$scope.toSubmit.details = {
			code: response.data.code,
			description: response.data.description,
		};

		$scope.readOnly.externalId = response.data.externalId;
		$scope.readOnly.SourceDatabaseName = response.data.SourceDatabaseName;
		if($scope.language.toUpperCase() === "FR")
			$scope.readOnly.name_display = response.data.Name_FR;
		else
			$scope.readOnly.name_display = response.data.Name_EN;

		$scope.changesDetected = false;
		$scope.oldData = JSON.parse(JSON.stringify($scope.toSubmit));
	}).catch(function(err) {
		err.responseText = err.data;
		ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.ERROR_DETAILS'), arrValidationGetDetails);
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
		$scope.validator.details.completed = ($scope.toSubmit.details.code != undefined && $scope.toSubmit.details.description != undefined);
	};

	// Submit changes
	$scope.updateCustomCode = function() {
		if($scope.formReady && $scope.changesDetected) {
			var ready = {};
			ready[0] = {
				externalId: $scope.currentDiagnosis.externalId,
				source: $scope.currentDiagnosis.source,
				code: $scope.toSubmit.details.code,
				description: $scope.toSubmit.details.description
			};
			$.ajax({
				type: "POST",
				url: "master-source/update/diagnosis",
				data: ready,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('MASTER_SOURCE_MODULE.DIAGNOSIS_EDIT.ERROR_UPDATE'));
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