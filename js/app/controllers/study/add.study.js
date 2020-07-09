angular.module('opalAdmin.controllers.study.add', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	 * Add Diagnosis Translation Page controller
	 *******************************************************************************/
	controller('study.add', function ($scope, $filter, $uibModal, $state, $locale, Session, ErrorHandler) {

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		$scope.showAssigned = false;
		$scope.hideAssigned = false;
		$scope.language = Session.retrieveObject('user').language;

		$scope.toSubmit = {
			OAUserId: OAUserId,
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

		$scope.validator = {
			details: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			investigator: {
				completed: false,
				mandatory: true,
				valid: true,
			},
			dates: {
				completed: false,
				mandatory: false,
				valid: true,
			},
		};

		$scope.leftMenu = {
			details: {
				display: false,
				open: false,
				preview: false,
			},
			investigator: {
				display: false,
				open: false,
				preview: false,
			},
			dates: {
				display: false,
				open: false,
				preview: false,
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

		$scope.totalSteps = 0;
		$scope.completedSteps = 0;
		$scope.formReady = false;

		/* Function for the "Processing..." dialog */
		var processingModal;
		$scope.showProcessingModal = function () {

			processingModal = $uibModal.open({
				templateUrl: 'templates/processingModal.html',
				backdrop: 'static',
				keyboard: false,
			});
		};

		$scope.formLoaded = false;
		// Function to load form as animations
		$scope.loadForm = function () {
			$('.form-box-left').addClass('fadeInDown');
			$('.form-box-right').addClass('fadeInRight');
		};

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
			$scope.leftMenu.details.open = ($scope.toSubmit.details.code !== "" || $scope.toSubmit.details.title !== "");
			$scope.leftMenu.details.display = $scope.leftMenu.details.open;
		};

		$scope.nameUpdate = function () {
			$scope.validator.investigator.completed = ($scope.toSubmit.investigator.name !== "");
			$scope.leftMenu.investigator.open = $scope.validator.details.completed;
			$scope.leftMenu.investigator.display = $scope.validator.details.completed;
		};

		// Watch to restrict the end calendar to not choose an earlier date than the start date
		$scope.$watch('toSubmit.dates.start_date', function(startDate){
			if (startDate !== undefined && startDate !== "")
				$scope.dateOptionsEnd.minDate = startDate;
			else
				$scope.dateOptionsEnd.minDate = Date.now();
			checkOpenDates();
		});

		function checkOpenDates() {
			if($scope.toSubmit.dates.start_date || $scope.toSubmit.dates.end_date) {
				$scope.leftMenu.dates.display = true;
				$scope.leftMenu.dates.open = true;
				$scope.leftMenu.dates.preview = true;
			} else {
				$scope.leftMenu.dates.display = false;
				$scope.leftMenu.dates.open = false;
				$scope.leftMenu.dates.preview = false;
			}
		}

		// Watch to restrict the start calendar to not choose a start after the end date
		$scope.$watch('toSubmit.dates.end_date', function(endDate){
			if (endDate !== undefined && endDate !== "")
				$scope.dateOptionsStart.maxDate = endDate;
			else
				$scope.dateOptionsStart.maxDate = null;
			checkOpenDates();
		});


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

		// Function to submit the new diagnosis translation
		$scope.submitStudy = function () {
			if ($scope.toSubmit.dates.start_date)
				$scope.toSubmit.dates.start_date = moment($scope.toSubmit.dates.start_date).format('X');
			if ($scope.toSubmit.dates.end_date)
				$scope.toSubmit.dates.end_date = moment($scope.toSubmit.dates.end_date).format('X');

			$.ajax({
				type: 'POST',
				url: 'study/insert/study',
				data: $scope.toSubmit,
				success: function () {},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('STUDY.ADD.ERROR_ADD'));
				},
				complete: function () {
					$state.go('study');
				}
			});
		};

		var fixmeTop = $('.summary-fix').offset().top;
		$(window).scroll(function() {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixmeTop) {
				$('.summary-fix').css({
					position: 'fixed',
					top: '0',
					width: '15%'
				});
			} else {
				$('.summary-fix').css({
					position: 'static',
					width: ''
				});
			}
		});

		var fixMeMobile = $('.mobile-side-panel-menu').offset().top;
		$(window).scroll(function() {
			var currentScroll = $(window).scrollTop();
			if (currentScroll >= fixMeMobile) {
				$('.mobile-side-panel-menu').css({
					position: 'fixed',
					top: '50px',
					width: '100%',
					zIndex: '100',
					background: '#6f5499',
					boxShadow: 'rgba(93, 93, 93, 0.6) 0px 3px 8px -3px'

				});
				$('.mobile-summary .summary-title').css({
					color: 'white'
				});
			} else {
				$('.mobile-side-panel-menu').css({
					position: 'static',
					width: '',
					background: '',
					boxShadow: ''
				});
				$('.mobile-summary .summary-title').css({
					color: '#6f5499'
				});
			}
		});
	});
