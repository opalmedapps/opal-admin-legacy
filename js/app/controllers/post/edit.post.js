angular.module('opalAdmin.controllers.post.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).


	// Function to accept/trust html (styles, classes, etc.)
	filter('deliberatelyTrustAsHtml', function ($sce) {
		return function (text) {
			return $sce.trustAsHtml(text);
		};
	}).
	controller('post.edit', function ($scope, $filter, $sce, $state, $uibModal, $uibModalInstance, postCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Default Booleans
		$scope.changesMade = false; // changes have been made? 

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		$scope.post = {}; // initialize post object
		$scope.postModal = {}; // for deep copy

		// Initialize lists to hold filters
		$scope.appointmentList = [];
		$scope.dxFilterList = [];
		$scope.doctorFilterList = [];
		$scope.resourceFilterList = [];
		$scope.patientFilterList = [];

		// Initialize search field variables
		$scope.appointmentSearchField = "";
		$scope.dxSearchField = "";
		$scope.doctorSearchField = "";
		$scope.resourceSearchField = "";
		$scope.patientSearchField = "";

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

		// Call our API service to get the current post details
		postCollectionService.getPostDetails($scope.currentPost.serial).then(function (response) {

			// Assign value
			$scope.post = response.data;
			$scope.postModal = jQuery.extend(true, {}, $scope.post); // deep copy

			if ($scope.post.publish_date) {
				var publishDateTime = $scope.post.publish_date.split(" ");
				$scope.post.publish_date = publishDateTime[0];
				$scope.post.publish_time = publishDateTime[1];

				// Split the hours and minutes to display them in their respective text boxes
				var hours = $scope.post.publish_time.split(":")[0];
				var minutes = $scope.post.publish_time.split(":")[1];
				var d = new Date();
				d.setHours(hours);
				d.setMinutes(minutes);
				$scope.post.publish_time = d;

				var year = $scope.post.publish_date.split("-")[0];
				var month = parseInt($scope.post.publish_date.split("-")[1]) - 1;
				var day = parseInt($scope.post.publish_date.split("-")[2]);
				$scope.post.publish_date = new Date(year, month, day);
			}

			// Call our API service to get each filter
			filterCollectionService.getFilters().then(function (response) {

				$scope.appointmentList = checkAdded(response.data.appointments); // Assign value
				$scope.dxFilterList = checkAdded(response.data.dx);
				$scope.doctorFilterList = checkAdded(response.data.doctors);
				$scope.resourceFilterList = checkAdded(response.data.resources);
				$scope.patientFilterList = checkAdded(response.data.patients);

				processingModal.close(); // hide modal
				processingModal = null; // remove reference

			}).catch(function(response) {
				console.error('Error occurred getting filter list:', response.status, response.data);
			});
		}).catch(function(response) {
			console.error('Error occurred getting post details:', response.status, response.data);
		});

		// Function to toggle Item in a list on/off
		$scope.selectItem = function (item) {
			$scope.changesMade = true;
			if (item.added)
				item.added = 0;
			else
				item.added = 1;
		};

		// Function to assign 1 to existing filters
		function checkAdded(filterList) {
			angular.forEach($scope.post.filters, function (selectedFilter) {
				var selectedFilterId = selectedFilter.id;
				var selectedFilterType = selectedFilter.type;
				angular.forEach(filterList, function (filter) {
					var filterId = filter.id;
					var filterType = filter.type;
					if (filterId == selectedFilterId && filterType == selectedFilterType) {
						filter.added = 1;
					}
				});
			});

			return filterList;
		}

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.post.name_EN && $scope.post.name_FR && $scope.post.body_EN && $scope.post.body_FR
				&& ($scope.post.type != 'Announcement' || ($scope.post.publish_date && $scope.post.publish_time))
				&& $scope.changesMade) {
				return true;
			}
			else
				return false;
		};

		$scope.setChangesMade = function () {
			$scope.changesMade = true;
		};

		// Submit changes
		$scope.updatePost = function () {

			if ($scope.checkForm()) {
				$scope.post.filters = []; // Empty filters
				// Add filters to post
				addFilters($scope.appointmentList);
				addFilters($scope.dxFilterList);
				addFilters($scope.doctorFilterList);
				addFilters($scope.resourceFilterList);
				addFilters($scope.patientFilterList);
				if ($scope.post.publish_date) {
					// Concat date and time
					$scope.post.publish_date = String(moment($scope.post.publish_date).format("YYYY-MM-DD")) + " " +
						String(moment($scope.post.publish_time).format("HH:mm"));
				}

				// Log who updated post 
				var currentUser = Session.retrieveObject('user');
				$scope.post.user = currentUser;
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/post/update.post.php",
					data: $scope.post,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.post.name_EN + "/ " + $scope.post.name_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}

						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			}
		};

		// Function to return filters that have been checked
		function addFilters(filterList) {
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					$scope.post.filters.push({ id: Filter.id, type: Filter.type });
			});
		}

		// Function to check if all filters are added
		$scope.allFilters = function (filterList) {
			var allFiltersAdded = true;
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					allFiltersAdded = false;
			});
			return allFiltersAdded;
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

		// Function to close modal dialog
		$scope.cancel = function () {
			$uibModalInstance.dismiss('cancel');
		};
	});