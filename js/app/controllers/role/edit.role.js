angular.module('opalAdmin.controllers.role.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('role.edit', function ($scope, $filter, $uibModal, $uibModalInstance, $locale, roleCollectionService, uiGridConstants, $state, Session) {
	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;

	$scope.validator = {
		name: {
			completed: false,
			mandatory: true,
			valid: true,
		},
		operations: {
			completed: false,
			mandatory: true,
			valid: true,
		}
	};

	$scope.leftMenu = {
		name: {
			display: false,
			open: false,
		},
		operations: {
			display: false,
			open: false,
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

	// Call our API to ge the list of diagnoses
	roleCollectionService.getAvailableRoleModules(OAUserId).then(function (response) {
		var temp;
		response.data.forEach(function(entry) {
			if (parseInt(entry.operation) < 0)
				entry.operation = "0";
			if (parseInt(entry.operation) > 7)
				entry.operation = "7";

			temp = {
				"ID": entry.ID,
				canRead : ((parseInt(entry.operation) & (1 << 0)) !== 0),
				canWrite : ((parseInt(entry.operation) & (1 << 1)) !== 0),
				canDelete : ((parseInt(entry.operation) & (1 << 2)) !== 0),
				read : false,
				write : false,
				delete : false
			};

			if($scope.language.toUpperCase() === "FR")
				temp.name_display = entry.name_FR;
			else
				temp.name_display = entry.name_EN;

			$scope.toSubmit.operations.push(temp);
		});
		roleCollectionService.getAvailableRoleModules(OAUserId).then(function (response) {});
	}).catch(function(err) {
		alert($filter('translate')('ROLE.ADD.ERROR_MODULE'));
		$state.go('role');
	});

	$scope.detailsUpdate = function () {
		$scope.validator.details.completed = ($scope.toSubmit.details.code !== "" && $scope.toSubmit.details.title !== "");
	};

	$scope.nameUpdate = function () {
		$scope.validator.investigator.completed = ($scope.toSubmit.investigator.name !== "");
	};

	$scope.$watch('toSubmit', function() {
		$scope.changesDetected = JSON.stringify($scope.toSubmit) !== JSON.stringify($scope.oldData);
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
			else if(!value.mandatory) {
				if(value.completed) {
					if (value.valid)
						nonMandatoryCompleted++;
				}
				else
					nonMandatoryCompleted++;
			}

		});
		$scope.formReady = (completedSteps >= totalsteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
	}, true);

	// Watch to restrict the end calendar to not choose an earlier date than the start date
	$scope.$watch('toSubmit.dates.start_date', function(startDate){
		if (startDate !== undefined && startDate !== "")
			$scope.dateOptionsEnd.minDate = startDate;
		else
			$scope.dateOptionsEnd.minDate = null;
	});

	// Watch to restrict the start calendar to not choose a start after the end date
	$scope.$watch('toSubmit.dates.end_date', function(endDate){
		if (endDate !== undefined && endDate !== "")
			$scope.dateOptionsStart.maxDate = endDate;
		else
			$scope.dateOptionsStart.maxDate = null;
	});

	// Submit changes
	$scope.updateCustomCode = function() {
		if($scope.formReady && $scope.changesDetected) {
			if ($scope.toSubmit.dates.start_date)
				$scope.toSubmit.dates.start_date = moment($scope.toSubmit.dates.start_date).format('X');
			if ($scope.toSubmit.dates.end_date)
				$scope.toSubmit.dates.end_date = moment($scope.toSubmit.dates.end_date).format('X');
			$.ajax({
				type: "POST",
				url: "study/update/study",
				data: $scope.toSubmit,
				success: function () {
				},
				error: function (err) {
					alert($filter('translate')('STUDY.EDIT.ERROR_UPDATE') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
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