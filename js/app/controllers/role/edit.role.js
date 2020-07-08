angular.module('opalAdmin.controllers.role.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('role.edit', function ($scope, $filter, $uibModal, $uibModalInstance, $locale, roleCollectionService, uiGridConstants, $state, Session) {

	// get current user id
	var user = Session.retrieveObject('user');
	var OAUserId = user.id;

	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;

	$scope.validator = {
		name: {
			completed: true,
			mandatory: true,
			valid: true,
		},
		operations: {
			completed: true,
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

	$scope.toSubmit = {
		OAUserId: OAUserId,
		roleId: $scope.currentRole.ID,
		name: {
			name_EN: "",
			name_FR: "",
		},
		operations: []
	};

	$scope.updatedRole = {};

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
		roleCollectionService.getRoleDetails($scope.toSubmit.roleId, OAUserId).then(function (response) {
			$scope.toSubmit.name.name_EN = response.data.name_EN;
			$scope.toSubmit.name.name_FR = response.data.name_FR;
			response.data.operations.forEach(function(ops) {
				$scope.toSubmit.operations.forEach(function(module) {
					if(module.ID === ops.moduleId) {
						module.read = ((parseInt(ops.access) & (1 << 0)) !== 0);
						module.write = ((parseInt(ops.access) & (1 << 1)) !== 0);
						module.delete = ((parseInt(ops.access) & (1 << 2)) !== 0);
					}
				});

			});
			$scope.oldData = JSON.parse(JSON.stringify($scope.toSubmit));
		}).catch(function(err) {
			alert($filter('translate')('ROLE.EDIT.ERROR_MODULE'));
			$state.go('role');
		});
	}).catch(function(err) {
		alert($filter('translate')('ROLE.EDIT.ERROR_MODULE'));
		$state.go('role');
	}).finally(function() {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	});

	$scope.nameUpdate = function () {
		$scope.validator.name.completed = ($scope.toSubmit.name.name_EN !== undefined && $scope.toSubmit.name.name_FR !== undefined);
		$scope.changesDetected = (JSON.stringify($scope.toSubmit) !== JSON.stringify($scope.oldData));
	};

	$scope.$watch('toSubmit.operations', function(nv) {
		var atLeastOne = false;
		angular.forEach(nv, function(value) {
			if(value.read || value.write || value.delete)
				atLeastOne = true;
			if(value.write) {
				if(value.canRead) value.read = true;
			}
			if(value.delete) {
				if(value.canWrite) value.write = true;
				if(value.canRead) value.read = true;
			}
		});

		$scope.validator.operations.completed = atLeastOne;
		$scope.changesDetected = (JSON.stringify($scope.toSubmit) !== JSON.stringify($scope.oldData));

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

	function buildOperations() {
		$scope.updatedRole = JSON.parse(JSON.stringify($scope.toSubmit));
		var newSubmit = [];
		var noError = true;

		$scope.toSubmit.operations.forEach(function(entry) {

			sup = parseInt((+entry.delete + "" + +entry.write + "" + +entry.read), 2);
			if (sup !== 0 && sup !== 1 && sup !== 3 && sup !== 7)
				noError = false;

			if(sup !== 0) {
				newSubmit.push({"moduleId": entry.ID, "access": sup});
			}
		});
		$scope.updatedRole.operations = newSubmit;
		return noError;
	}

	// Submit changes
	$scope.updateRole = function() {
		if($scope.formReady && $scope.changesDetected) {
			var validResult = buildOperations();
			$.ajax({
				type: "POST",
				url: "role/update/role",
				data: $scope.updatedRole,
				success: function () {
				},
				error: function (err) {
					alert($filter('translate')('ROLE.EDIT.ERROR_UPDATE') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
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