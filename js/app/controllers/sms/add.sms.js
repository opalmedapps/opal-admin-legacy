angular.module('opalAdmin.controllers.add.sms', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


	/******************************************************************************
	 * SMS Page controller
	 *******************************************************************************/
	controller('add.sms', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		var arrValidationInsert = [
			$filter('translate')('SMS.VALIDATION.ENGLISH'),
			$filter('translate')('SMS.VALIDATION.FRENCH')
		];

		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 1)) !== 0);
		if(!$scope.writeAccess) {
			alert($filter('translate')('SMS.ADD.ERROR_ACCESS'));
			$state.go('sms');
		}

		$scope.dataReceived = {
			speciality: null,
			type: null,
			event: null,
		};

		smsCollectionService.getSmsSpeciality().then(function (response) {
			$scope.dataReceived.speciality = response.data;
		}).catch(function (err) {
			ErrorHandler.onError(err, $filter('translate')('SMS.ADD.ERROR_DETAILS'));
		});

		$scope.totalSteps = 0;
		$scope.completedSteps = 0;
		$scope.formReady = false;

		$scope.oldSms = {};

		$scope.toSubmit = {
			speciality: {
				data: {
					name: "",
					code: "",
				},
			},
			type: {
				data: "",
			},
			event: {
				data: "",
			},
			message: {
				en: {
					sms: "",
					id: "",
				},
				fr: {
					sms: "",
					id: "",
				},
			}
		};

		$scope.validator = {
			speciality: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			type: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			event: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			message: {
				completed: false,
				mandatory: true,
				valid: true,
			},
		};

		$scope.leftMenu = {
			speciality: {
				display: false,
				open: false,
				preview: false,
			},
			type: {
				display: false,
				open: false,
				preview: false,
			},
			event: {
				display: false,
				open: false,
				preview: false,
			},
			message: {
				display: false,
				open: false,
				preview: false,
			},
		};

		$scope.$watch('toSubmit.speciality', function(){
			$scope.toSubmit.type.data = "";
			$scope.toSubmit.event.data = "";
			$scope.leftMenu.speciality.display = !!($scope.toSubmit.speciality.data.code);
			$scope.leftMenu.speciality.open = !!($scope.toSubmit.speciality.data.code);
			$scope.leftMenu.speciality.preview = !!($scope.toSubmit.speciality.data.code);
			$scope.validator.type.completed = false;
			$scope.validator.event.completed = false;
			$scope.validator.message.completed = false;
			$scope.getSmsTypeList();
			$scope.validator.speciality.completed = !!($scope.toSubmit.speciality.data.code);
		}, true);

		$scope.$watch('toSubmit.type', function(){
			$scope.toSubmit.event.data = "";
			$scope.leftMenu.type.display = !!($scope.toSubmit.type.data);
			$scope.leftMenu.type.open = !!($scope.toSubmit.type.data);
			$scope.leftMenu.type.preview = !!($scope.toSubmit.type.data);
			$scope.validator.event.completed = false;
			$scope.validator.message.completed = false;
			if($scope.toSubmit.type.data !== "") $scope.getSmsEventList();
			$scope.validator.type.completed = !!($scope.toSubmit.type.data);
		}, true);

		$scope.$watch('toSubmit.event', function(){
			$scope.validator.message.completed = false;
			$scope.leftMenu.event.display = !!($scope.toSubmit.event.data);
			$scope.leftMenu.event.open = !!($scope.toSubmit.event.data);
			$scope.leftMenu.event.preview = !!($scope.toSubmit.event.data);
			$scope.validator.event.completed = !!($scope.toSubmit.event.data);
		}, true);

		$scope.$watch('toSubmit.message', function(){
			$scope.validator.message.completed =
				(JSON.stringify($scope.oldSms) !== JSON.stringify($scope.toSubmit.message) && !!($scope.toSubmit.message.en.sms) && !!($scope.toSubmit.message.en.id) && !!($scope.toSubmit.message.fr.sms) && !!($scope.toSubmit.message.fr.id));
			$scope.leftMenu.message.display = $scope.validator.message.completed;
			$scope.leftMenu.message.open = $scope.validator.message.completed;
			$scope.leftMenu.message.preview = $scope.validator.message.completed;
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

			$scope.totalSteps = totalsteps;
			$scope.completedSteps = completedSteps;
			$scope.stepProgress = $scope.totalSteps > 0 ? ($scope.completedSteps / $scope.totalSteps * 100) : 0;
			$scope.formReady = ($scope.completedSteps >= $scope.totalSteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
		}, true);

		$scope.getSmsTypeList = function () {
			smsCollectionService.getSmsType($scope.toSubmit.speciality.data.code).then(function (response) {
				$scope.dataReceived.type = response.data;
				if($scope.dataReceived.type.length <= 0)
					alert($filter('translate')('SMS.ADD.ERROR_NO_TYPE'));
			}).catch(function (err) {
				ErrorHandler.onError(err, $filter('translate')('SMS.ADD.ERROR_DETAILS'));
			});
		};

		$scope.getSmsEventList = function () {
			smsCollectionService.getSmsMessages($scope.toSubmit.type.data, $scope.toSubmit.speciality.data.code).then(function (response) {
				$scope.dataReceived.event = [];
				response.data.forEach(function (row) {
					if (!$scope.dataReceived.event.includes(row.event)) $scope.dataReceived.event.push(row.event);
				});
				$scope.MessageList = response.data;
			}).catch(function (err) {
				ErrorHandler.onError(err, $filter('translate')('SMS.ADD.ERROR_DETAILS'));
			});
		};

		$scope.SpecialityUpdate = function (element) {
			$scope.toSubmit.speciality.data.code = element.specialityCode;
			$scope.toSubmit.speciality.data.name = element.specialityName;
		};

		//Function to update the type selected
		$scope.TypeUpdate = function (element) {
			$scope.toSubmit.type.data = element;
		};

		//Function to update the event selected
		$scope.EventUpdate = function (element) {
			$scope.toSubmit.event.data = element;
			var tempData = $scope.MessageList.filter(x => x.event === $scope.toSubmit.event.data && x.language === "French")[0];
			$scope.toSubmit.message.fr.sms = tempData.smsMessage;
			$scope.toSubmit.message.fr.id = tempData.messageId;
			tempData = $scope.MessageList.filter(x => x.event === $scope.toSubmit.event.data && x.language === "English")[0];
			$scope.toSubmit.message.en.sms = tempData.smsMessage;
			$scope.toSubmit.message.en.id = tempData.messageId;
			$scope.oldSms = JSON.parse(JSON.stringify($scope.toSubmit.message));
		};

		//Update Message information
		$scope.UpdateMessage = function () {
			$.ajax({
				type: "POST",
				url: "sms/update/sms-message",
				data: $scope.toSubmit.message,
				success: function () {
				},
				error: function (err) {
					err.responseText = JSON.parse(err.responseText);
					ErrorHandler.onError(err, $filter('translate')('SMS.ADD.ERROR'), arrValidationInsert);
				},
				complete: function () {
					$state.go('sms');
				}
			});
		};
	});