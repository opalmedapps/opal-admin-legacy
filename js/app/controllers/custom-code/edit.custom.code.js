// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.customCode.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('customCode.edit', function ($scope, $filter, $uibModal, $uibModalInstance, customCodeCollectionService, Session, ErrorHandler) {
	$scope.toSubmit = {
		OAUserId: Session.retrieveObject('user').id,
		sessionid: Session.retrieveObject('user').sessionid,
	};

	$scope.generalInfo = {
		module_display: null,
	};

	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;

	$scope.validator = {
		details: {
			completed: true,
			mandatory: true,
		},
	};

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
	customCodeCollectionService.getCustomCodeDetails($scope.currentCustomCode.ID, $scope.currentCustomCode.moduleId, $scope.toSubmit.OAUserId).then(function (response) {
		$scope.toSubmit.ID = response.data.ID;
		$scope.toSubmit.moduleId = {
			value: response.data.module.ID,
		};
		$scope.toSubmit.details = {
			code: response.data.code,
			description: response.data.description,
		};

		if($scope.language.toUpperCase() === "FR")
			$scope.generalInfo.module_display = response.data.module.name_FR;
		else
			$scope.generalInfo.module_display = response.data.module.name_EN;

		if(response.data.module.subModule !== "") {
			$scope.toSubmit.type = {
				ID: response.data.module.subModule.ID,
			};

			if($scope.language.toUpperCase() === "FR")
				$scope.generalInfo.type_display = response.data.module.subModule.name_FR;
			else
				$scope.generalInfo.type_display = response.data.module.subModule.name_EN;

		}

		$scope.locked = parseInt(response.data.locked) > 0;

		$scope.changesDetected = false;
		$scope.oldData = JSON.parse(JSON.stringify($scope.toSubmit));
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('CUSTOM_CODE.EDIT.ERROR_DETAILS'));
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
			$.ajax({
				type: "POST",
				url: "custom-code/update/custom-code",
				data: $scope.toSubmit,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('CUSTOM_CODE.EDIT.ERROR_UPDATE'));
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
