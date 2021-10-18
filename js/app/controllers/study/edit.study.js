angular.module('opalAdmin.controllers.study.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('study.edit', function ($scope, $filter, $uibModal, $uibModalInstance, $locale, studyCollectionService, Session, ErrorHandler) {
	$scope.toSubmit = {
		OAUserId: Session.retrieveObject('user').id,
		ID: "",
		details: {
			code: "",
			title: "",
		},
		investigator: {
			name: ""
		},
		dates: {
			start_date: "",
			end_date: "",
		}

	};

	$scope.oldData = {};
	$scope.changesDetected = false;
	$scope.formReady = false;

	$scope.validator = {
		details: {
			completed: true,
			mandatory: true,
			valid: true,
		},
		investigator: {
			completed: true,
			mandatory: true,
			valid: true,
		},
		dates: {
			completed: false,
			mandatory: false,
			valid: true,
		},
	};

	// Date format for start and end frequency dates
	$scope.format = 'yyyy-MM-dd';
	$scope.dateOptionsStart = {
		formatYear: "'yy'",
		startingDay: 0,
		minDate: new Date(),
		maxDate: null
	};
	$scope.dateOptionsEnd = {
		formatYear: "'yy'",
		startingDay: 0,
		minDate: new Date(),
		maxDate: null
	};

	$locale["DATETIME_FORMATS"]["SHORTDAY"] = [
		$filter('translate')('DATEPICKER.SUNDAY_S'),
		$filter('translate')('DATEPICKER.MONDAY_S'),
		$filter('translate')('DATEPICKER.TUESDAY_S'),
		$filter('translate')('DATEPICKER.WEDNESDAY_S'),
		$filter('translate')('DATEPICKER.THURSDAY_S'),
		$filter('translate')('DATEPICKER.FRIDAY_S'),
		$filter('translate')('DATEPICKER.SATURDAY_S')
	];

	$locale["DATETIME_FORMATS"]["MONTH"] = [
		$filter('translate')('DATEPICKER.JANUARY'),
		$filter('translate')('DATEPICKER.FEBRUARY'),
		$filter('translate')('DATEPICKER.MARCH'),
		$filter('translate')('DATEPICKER.APRIL'),
		$filter('translate')('DATEPICKER.MAY'),
		$filter('translate')('DATEPICKER.JUNE'),
		$filter('translate')('DATEPICKER.JULY'),
		$filter('translate')('DATEPICKER.AUGUST'),
		$filter('translate')('DATEPICKER.SEPTEMBER'),
		$filter('translate')('DATEPICKER.OCTOBER'),
		$filter('translate')('DATEPICKER.NOVEMBER'),
		$filter('translate')('DATEPICKER.DECEMBER')
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
	studyCollectionService.getStudiesDetails($scope.currentStudy.ID, $scope.toSubmit.OAUserId).then(function (response) {
		var dateArray, year, month, date;
		$scope.toSubmit.ID = response.data.ID;
		$scope.toSubmit.details.code = response.data.code;
		$scope.toSubmit.details.title = response.data.title;
		$scope.toSubmit.investigator.name = response.data.investigator;
		if(response.data.startDate !== "" && response.data.startDate !== null) {
			dateArray = response.data.startDate.split("-");
			year = dateArray[0];
			month = parseInt(dateArray[1], 10) - 1;
			date = dateArray[2];
			$scope.toSubmit.dates.start_date = new Date(year, month, date);
		}
		if(response.data.endDate !== "" && response.data.endDate !== null) {
			dateArray = response.data.endDate.split("-");
			year = dateArray[0];
			month = parseInt(dateArray[1], 10) - 1;
			date = dateArray[2];
			$scope.toSubmit.dates.end_date = new Date(year, month, date);
		}

		$scope.oldData = JSON.parse(JSON.stringify($scope.toSubmit));
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_DETAILS'));
	}).finally(function() {
		processingModal.close(); // hide modal
		processingModal = null; // remove reference
	});

	$scope.popupStart = {};
	$scope.popupEnd = {};
	$scope.openStart = function ($event) {
		$event.preventDefault();
		$event.stopPropagation();
		$scope.popupStart['opened'] = true;
		$scope.popupEnd['opened'] = false;
	};
	$scope.openEnd = function ($event) {
		$event.preventDefault();
		$event.stopPropagation();
		$scope.popupStart['opened'] = false;
		$scope.popupEnd['opened'] = true;
	};

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
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('STUDY.EDIT.ERROR_UPDATE'));
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