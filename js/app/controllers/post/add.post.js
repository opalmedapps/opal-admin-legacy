angular.module('opalAdmin.controllers.post.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'textAngular']).


	// Function to accept/trust html (styles, classes, etc.)
	filter('deliberatelyTrustAsHtml', function ($sce) {
		return function (text) {
			return $sce.trustAsHtml(text);
		};
	}).
	/******************************************************************************
	* Add Post Page controller 
	*******************************************************************************/
	controller('post.add', function ($scope, $filter, $state, $sce, $uibModal, aliasCollectionService, filterCollectionService, Session) {

		// Function to go to previous page
		$scope.goBack = function () {
			window.history.back();
		};

		// Default boolean variables
		$scope.typeSection = {open:false, show:true};
		$scope.titleSection = {open:false, show:false};
		$scope.bodySection = {open:false, show:false};
		$scope.publishSection = {open:false, show:false};
		$scope.triggerSection = {
			show:false,
			patient: {open:false},
			appointment: {open:false},
			doctor: {open:false},
			machine: {open:false},
			diagnosis: {open:false}
		};

		// completed steps boolean object; used for progress bar
		var steps = {
			title: { completed: false },
			body: { completed: false },
			type: { completed: false },
			publish_date: { completed: false }
		};

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Initialize search field variables
		$scope.appointmentSearchField = null;
		$scope.dxSearchField = null;
		$scope.doctorSearchField = null;
		$scope.machineSearchField = null;
		$scope.patientSearchField = null; 

		// Default count of completed steps
		$scope.numOfCompletedSteps = 0;

		// Default total number of steps 
		$scope.stepTotal = 4;

		// Progress for progress bar on default steps and total
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		// Initialize the list of post types
		$scope.postTypes = [
			{
				name: 'Announcement',
				icon: 'bullhorn'
			},
			{
				name: 'Treatment Team Message',
				icon: 'user-md'
			},
			{
				name: 'Patients for Patients',
				icon: 'users'
			}
		];

		// Initialize the new post object
		$scope.newPost = {
			name_EN: null,
			name_FR: null,
			type: null,
			body_EN: null,
			body_FR: null,
			publish_date: null,
			publish_time: null,
			triggers: []
		};

		// Initialize lists to hold triggers
		$scope.appointmentTriggerList = [];
		$scope.dxTriggerList = [];
		$scope.doctorTriggerList = [];
		$scope.machineTriggerList = [];
		$scope.patientTriggerList = [];

		$scope.selectAll = {
			appointment: {all:false, checked:false},
			diagnosis: {all:false, checked:false},
			doctor: {all:false, checked:false},
			machine: {all:false, checked:false},
			patient: {all:false, checked:false}
		}

		/* Function for the "Processing..." dialog */
		var processingModal;
		$scope.showProcessingModal = function () {

			processingModal = $uibModal.open({
				templateUrl: 'templates/processingModal.html',
				backdrop: 'static',
				keyboard: false,
			});
		};
		$scope.showProcessingModal(); // Calling function

		$scope.formLoaded = false;
		// Function to load form as animations
		$scope.loadForm = function () {
			$('.form-box-left').addClass('fadeInDown');
			$('.form-box-right').addClass('fadeInRight');
		};


		// Call our API service to get each trigger
		filterCollectionService.getFilters().then(function (response) {

			$scope.appointmentTriggerList = response.data.appointments; // Assign value
			$scope.dxTriggerList = response.data.dx;
			$scope.doctorTriggerList = response.data.doctors;
			$scope.machineTriggerList = response.data.machines;
			$scope.patientTriggerList = response.data.patients;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference

			$scope.formLoaded = true;
			$scope.loadForm();

		}).catch(function(response) {
			console.error('Error occurred getting triggers:', response.status, response.data);
		});

		// Function to toggle necessary changes when updating post name
		$scope.titleUpdate = function () {

			$scope.titleSection.open = true;

			if ($scope.newPost.name_EN && $scope.newPost.name_FR) {

				$scope.bodySection.show = true;

				// Toggle step completion
				steps.title.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle step completion
				steps.title.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to toggle necessary changes when updating the post body
		$scope.bodyUpdate = function () {

			$scope.bodySection.open = true;

			if ($scope.newPost.body_EN && $scope.newPost.body_FR) {

				$scope.publishSection.show = true;

				if ($scope.newPost.type.name != 'Announcement') {
					$scope.triggerSection.show = true;
				}
				// Toggle boolean
				steps.body.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle boolean
				steps.body.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to toggle necessary changes when updating the post type
		$scope.typeUpdate = function (type) {

			$scope.newPost.type = type;

			// Toggle boolean
			steps.type.completed = true;
			$scope.titleSection.show = true;
			$scope.typeSection.open = true;

			// Remove any entry in publish date
			$scope.newPost.publish_date = null;
			$scope.newPost.publish_time = null;
			// toggle publish date logic
			if ($scope.newPost.type.name == 'Announcement') {
				steps.publish_date.completed = false;
			}
			else {
				steps.publish_date.completed = true;
			}

			// Count the number of completed steps
			$scope.numOfCompletedSteps = stepsCompleted(steps);
			// Change progress bar
			$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
		};

		// Function to toggle necessary changes when updating the publish date
		$scope.publishDateUpdate = function () {

			$scope.publishSection.open = true; 

			if ($scope.newPost.publish_date && $scope.newPost.publish_time) {

				$scope.triggerSection.show = true;

				// Toggle boolean
				steps.publish_date.completed = true;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			} else {
				// Toggle boolean
				steps.publish_date.completed = false;
				// Count the number of completed steps
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				// Change progress bar
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);
			}
		};

		// Function to submit the new post
		$scope.submitPost = function () {
			if ($scope.checkForm()) {
				// For some reason the HTML text fields add a zero-width-space
				// https://stackoverflow.com/questions/24205193/javascript-remove-zero-width-space-unicode-8203-from-string
				$scope.newPost.body_EN = $scope.newPost.body_EN.replace(/\u200B/g,'');
				$scope.newPost.body_FR = $scope.newPost.body_FR.replace(/\u200B/g,'');
				
				// Add triggers to new post object
				addTriggers($scope.appointmentTriggerList, $scope.selectAll.appointment.all);
				addTriggers($scope.dxTriggerList, $scope.selectAll.diagnosis.all);
				addTriggers($scope.doctorTriggerList, $scope.selectAll.doctor.all);
				addTriggers($scope.machineTriggerList, $scope.selectAll.machine.all);
				addTriggers($scope.patientTriggerList, $scope.selectAll.patient.all);
				if ($scope.newPost.publish_date && $scope.newPost.publish_time) {
					// Concat date and time
					$scope.newPost.publish_date = String(moment($scope.newPost.publish_date).format("YYYY-MM-DD")) + " " +
						String(moment($scope.newPost.publish_time).format("HH:mm"));
				}
				// Log who updated post 
				var currentUser = Session.retrieveObject('user');
				$scope.newPost.user = currentUser;
				// Submit 
				$.ajax({
					type: "POST",
					url: "php/post/insert.post.php",
					data: $scope.newPost,
					success: function () {
						$state.go('post');
					}
				});
			}
		};

		// Function to toggle trigger in a list on/off
		$scope.selectTrigger = function (trigger, selectAll) {
			selectAll.all = false;
			selectAll.checked = false;
			if (trigger.added)
				trigger.added = 0;
			else
				trigger.added = 1;
		};

		// Function to assign search fields when textbox changes
		$scope.searchAppointment = function (field) {
			$scope.appointmentSearchField = field;
			$scope.selectAll.appointment.all = false;
		};
		$scope.searchDiagnosis = function (field) {
			$scope.dxSearchField = field;
			$scope.selectAll.diagnosis.all = false;
		};
		$scope.searchDoctor = function (field) {
			$scope.doctorSearchField = field;
			$scope.selectAll.doctor.all = false;
		};
		$scope.searchMachine = function (field) {
			$scope.machineSearchField = field;
			$scope.selectAll.machine.all = false;
		};
		$scope.searchPatient = function (field) {
			$scope.patientSearchField = field;
			$scope.selectAll.patient.all = false;
		};

		// Function for search through the triggers
		$scope.searchAppointmentFilter = function (Filter) {
			var keyword = new RegExp($scope.appointmentSearchField, 'i');
			return !$scope.appointmentSearchField || keyword.test(Filter.name);
		};
		$scope.searchDxFilter = function (Filter) {
			var keyword = new RegExp($scope.dxSearchField, 'i');
			return !$scope.dxSearchField || keyword.test(Filter.name);
		};
		$scope.searchDoctorFilter = function (Filter) {
			var keyword = new RegExp($scope.doctorSearchField, 'i');
			return !$scope.doctorSearchField || keyword.test(Filter.name);
		};
		$scope.searchMachineFilter = function (Filter) {
			var keyword = new RegExp($scope.machineSearchField, 'i');
			return !$scope.machineSearchField || keyword.test(Filter.name);
		};
		$scope.searchPatientFilter = function (Filter) {
			var keyword = new RegExp($scope.patientSearchField, 'i');
			return !$scope.patientSearchField || keyword.test(Filter.name);
		};

		// Function for selecting all triggers in a trigger list
		$scope.selectAllTriggers = function (triggerList,triggerFilter,selectAll) {

			var filtered = $scope.filter(triggerList,triggerFilter);
			
			if (filtered.length == triggerList.length) { // search field wasn't used
				if (selectAll.checked) {
					angular.forEach(filtered, function (trigger) {
						trigger.added = 0;
					});
					selectAll.checked = false; // toggle off
					selectAll.all = false;
				}
				else {
					angular.forEach(filtered, function (trigger) {
						trigger.added = 1;
					});

					selectAll.checked = true; // toggle on
					selectAll.all = true;
				}
			}
			else {
				if (selectAll.checked) { // was checked
					angular.forEach(filtered, function (trigger) {
						trigger.added = 0;
					});
					selectAll.checked = false; // toggle off
					selectAll.all = false;
				}
				else { // was not checked
					angular.forEach(filtered, function (trigger) {
						trigger.added = 1;
					});

					selectAll.checked = true; // toggle on

				}
			}

		};

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

		// Function to return triggers that have been checked
		function addTriggers(triggerList, selectAll) {
			if(selectAll) {
				$scope.newPost.triggers.push({id: 'ALL', type: triggerList[0].type});
			}
			else {
				angular.forEach(triggerList, function (trigger) {
					if (trigger.added)
						$scope.newPost.triggers.push({ id: trigger.id, type: trigger.type });
				});
			}
		}

		// Function to check if triggers are added
		$scope.checkTriggers = function (triggerList) {
			var triggersAdded = false;
			angular.forEach(triggerList, function (trigger) {
				if (trigger.added)
					triggersAdded = true;
			});
			return triggersAdded;
		};

		// Function to return boolean for form completion
		$scope.checkForm = function () {
			if (trackProgress($scope.numOfCompletedSteps, $scope.stepTotal) == 100)
				return true;
			else
				return false;
		};


		$scope.showWeeks = true; // show weeks sidebar 
		$scope.toggleWeeks = function () {
			$scope.showWeeks = !$scope.showWeeks;
		};

		// set minimum date (today's date)
		$scope.toggleMin = function () {
			$scope.minDate = ($scope.minDate) ? null : new Date();
		};
		$scope.toggleMin();

		$scope.popup = {
			opened: false
		};

		// Open popup calendar
		$scope.open = function () {
			$scope.popup.opened = true;
		};

		$scope.dateOptions = {
			'year-format': "'yy'",
			'starting-day': 1
		};

		// Date format
		$scope.format = 'yyyy-MM-dd';

		// object for cron repeat units
		$scope.repeatUnits = [
			'Minutes',
			'Hours'
		];

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

