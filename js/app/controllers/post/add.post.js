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
		$scope.filterSection = {open:false, show:false};

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
		$scope.resourceSearchField = null;
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
			filters: []
		};

		// Initialize lists to hold filters
		$scope.appointmentList = [];
		$scope.dxFilterList = [];
		$scope.doctorFilterList = [];
		$scope.resourceFilterList = [];
		$scope.patientFilterList = [];

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


		// Call our API service to get each filter
		filterCollectionService.getFilters().then(function (response) {

			$scope.appointmentList = response.data.appointments; // Assign value
			$scope.dxFilterList = response.data.dx;
			$scope.doctorFilterList = response.data.doctors;
			$scope.resourceFilterList = response.data.resources;
			$scope.patientFilterList = response.data.patients;

			processingModal.close(); // hide modal
			processingModal = null; // remove reference

			$scope.formLoaded = true;
			$scope.loadForm();

		}).catch(function(response) {
			console.error('Error occurred getting filter list:', response.status, response.data);
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
					$scope.filterSection.show = true;
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

				$scope.filterSection.show = true;

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
				
				// Add filters to new post object
				addFilters($scope.appointmentList);
				addFilters($scope.dxFilterList);
				addFilters($scope.doctorFilterList);
				addFilters($scope.resourceFilterList);
				addFilters($scope.patientFilterList);
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

		// Function to toggle Item in a list on/off
		$scope.selectItem = function (item) {
			if (item.added)
				item.added = 0;
			else
				item.added = 1;
		};

		// Function to assign search fields when textbox changes
		$scope.searchAppointment = function (field) {
			$scope.appointmentSearchField = field;
		};
		$scope.searchDiagnosis = function (field) {
			$scope.dxSearchField = field;
		};
		$scope.searchDoctor = function (field) {
			$scope.doctorSearchField = field;
		};
		$scope.searchResource = function (field) {
			$scope.resourceSearchField = field;
		};
		$scope.searchPatient = function (field) {
			$scope.patientSearchField = field;
		};

		// Function for search through the filters
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
		$scope.searchResourceFilter = function (Filter) {
			var keyword = new RegExp($scope.resourceSearchField, 'i');
			return !$scope.resourceSearchField || keyword.test(Filter.name);
		};
		$scope.searchPatientFilter = function (Filter) {
			var keyword = new RegExp($scope.patientSearchField, 'i');
			return !$scope.patientSearchField || keyword.test(Filter.name);
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

		// Function to return filters that have been checked
		function addFilters(filterList) {
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					$scope.newPost.filters.push({ id: Filter.id, type: Filter.type });
			});
		}

		// Function to check if filters are added
		$scope.checkFilters = function (filterList) {
			var filtersAdded = false;
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					filtersAdded = true;
			});
			return filtersAdded;
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

