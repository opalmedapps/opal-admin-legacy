angular.module('opalAdmin.controllers.study.add', ['ngAnimate', 'ui.bootstrap']).

	/******************************************************************************
	 * Add Diagnosis Translation Page controller
	 *******************************************************************************/
	controller('study.add', function ($scope, $filter, $uibModal, $state, $locale, Session) {

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

		// Default toolbar for wysiwyg
		$scope.toolbar = [
			['h1', 'h2', 'h3', 'p'],
			['bold', 'italics', 'underline', 'ul', 'ol'],
			['justifyLeft', 'justifyCenter', 'indent', 'outdent'],
			['html', 'insertLink']
		];

		// completed steps booleans - used for progress bar
		var steps = {
			diagnoses: { completed: false },
			title_description: { completed: false }
		};

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps
		$scope.stepTotal = 2;

		// Progress for progress bar on default steps and total
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		// Function to calculate / return step progress
		function trackProgress(value, total) {
			return Math.round(100 * value / total);
		}

		// Function to return number of steps completed
		function stepsCompleted(steps) {

			var numberOfTrues = 0;
			for (var step in steps) {
				if (steps[step].completed === true) {
					numberOfTrues++;
				}
			}

			return numberOfTrues;
		}
		
		$scope.toSubmit = {
			OAUserId: OAUserId,
			details: {
				code: null,
				title: null,
			},
			investigator: {
				name: null
			},
			dates: {
				start_date: null,
				end_date: null,
			}
		};

		$scope.validator = {
			details: {
				completed: false,
				mandatory: true,
			},
			investigator: {
				completed: false,
				mandatory: true,
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
			$scope.openAndValidate();
		};
		$scope.openEnd = function ($event) {
			$event.preventDefault();
			$event.stopPropagation();
			$scope.popupStart['opened'] = false;
			$scope.popupEnd['opened'] = true;
			$scope.openAndValidate();
		};

		$scope.openAndValidate = function(){
			try {
				// $scope.leftMenu.publishFrequency.open = true;
				// $scope.validator.occurrence.completed = $scope.checkFrequencyTrigger();
			} catch(e) {}
		};

		$scope.detailsUpdate = function () {
			$scope.validator.details.completed = ($scope.toSubmit.details.code != null && $scope.toSubmit.details.title != null);
			$scope.leftMenu.details.open = ($scope.toSubmit.details.code != null || $scope.toSubmit.details.title != null);
			$scope.leftMenu.details.display = ($scope.toSubmit.details.code != null || $scope.toSubmit.details.title != null);
		};

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

			$scope.totalSteps = totalsteps;
			$scope.completedSteps = completedSteps;
			$scope.stepProgress = $scope.totalSteps > 0 ? ($scope.completedSteps / $scope.totalSteps * 100) : 0;
			$scope.formReady = ($scope.completedSteps >= $scope.totalSteps) && (nonMandatoryCompleted >= nonMandatoryTotal);
		}, true);

		// Function to submit the new diagnosis translation
		$scope.submitCustomCode = function () {
			$.ajax({
				type: 'POST',
				url: 'study/insert/study',
				data: $scope.toSubmit,
				success: function () {},
				error: function (err) {
					console.log(err);
					alert($filter('translate')('STUDY.ADD.ERROR_ADD') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.responseText));
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
