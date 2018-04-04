angular.module('opalAdmin.controllers.educationalMaterial.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns']).

	controller('educationalMaterial.edit', function ($scope, $filter, $sce, $uibModal, $uibModalInstance, $state, educationalMaterialCollectionService, filterCollectionService, uiGridConstants, Session) {

		// Default Booleans
		$scope.changesMade = false; // changes have been made? 

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		$scope.eduMat = {}; // initialize edumat object

		// Initialize a list of sexes
		$scope.sexes = [
			{
				name: 'Male',
				icon: 'male'
			}, {
				name: 'Female',
				icon: 'female'
			}
		];

		// Initialize to hold demographic filters
		$scope.demoFilter = {
			sex: null,
			age: {
				min: 0,
				max: 100
			}
		};

		// Initialize lists to hold filters
		$scope.appointmentList = [];
		$scope.dxFilterList = [];
		$scope.doctorFilterList = [];
		$scope.resourceFilterList = [];
		$scope.patientFilterList = [];

		$scope.contentTypeList = []; 

		$scope.tocsComplete = true;

		// Initialize lists to hold the distinct edu material types
		$scope.EduMatTypes_EN = [];
		$scope.EduMatTypes_FR = [];
		// Call our API to get the list of edu material types
		educationalMaterialCollectionService.getEducationalMaterialTypes().then(function (response) {

			$scope.EduMatTypes_EN = response.data.EN;
			$scope.EduMatTypes_FR = response.data.FR;
		}).catch(function(response) {
			console.error('Error occurred getting educational material types:', response.status, response.data);
		});

		$scope.bannerMessageModal = "";

		// Function to show page banner 
		$scope.showBannerModal = function () {
			$(".bannerMessageModal").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessageModal").slideUp();
				}, 5000);
			});
		};

		// Function to set banner class
		$scope.setBannerModalClass = function (classname) {
			// Remove any classes starting with "alert-" 
			$(".bannerMessageModal").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessageModal").addClass('alert-' + classname);
		};

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

		// Call our API service to get the current educational material details
		educationalMaterialCollectionService.getEducationalMaterialDetails($scope.currentEduMat.serial).then(function (response) {

			// Assign value
			$scope.eduMat = response.data;

			// Assign demographic filters
			checkDemographicFilters();

			// Call our API to get the list of allowable educational material content type tags
			educationalMaterialCollectionService.getAllowableContentTypes().then(function (response) {
				$scope.contentTypeList = response.data;
				// Assign content type 
				checkContentTypes();
			}).catch(function(response) {
				console.error('Error occurred getting allowable content types: ', response.status, response.data);
			});

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
				console.error('Error occurred getting filters:', response.status, response.data);
			});
		}).catch(function(response) {
			console.error('Error occurred getting educational material details:', response.status, response.data);
		});

		// Function to toggle Item in a list on/off
		$scope.selectItem = function (item) {
			$scope.setChangesMade();
			$scope.eduMat.filters_updated = 1;
			if (item.added)
				item.added = 0;
			else
				item.added = 1;
		};

		$scope.detailsUpdated = function () {
			$scope.eduMat.details_updated = 1;
			$scope.setChangesMade();
		}

		// Function to assign '1' to existing filters
		function checkAdded(filterList) {
			angular.forEach($scope.eduMat.filters, function (selectedFilter) {
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

		// Function to assign '1' to existing content types
		function checkContentTypes() {
			angular.forEach($scope.eduMat.content_types, function (selectedType){
				var selectedTypeSer = selectedType.serial;
				angular.forEach($scope.contentTypeList, function (type) {
					var typeSer = type.serial;
					if (typeSer == selectedTypeSer) {
						type.added = 1;
					}
				});
			});

			return;
		}

		// Function to check demographic filters
		function checkDemographicFilters() {
			var demoFilter = {
				sex: null,
				age: {
					min: 0,
					max: 100
				}
			};
			angular.forEach($scope.eduMat.filters, function (selectedFilter) {
				if (selectedFilter.type == 'Sex')
					$scope.demoFilter.sex = selectedFilter.id;
				if (selectedFilter.type == 'Age') {
					$scope.demoFilter.age.min = parseInt(selectedFilter.id.split(',')[0]);
					$scope.demoFilter.age.max = parseInt(selectedFilter.id.split(',')[1]);
				}
			});

			return demoFilter;
		}

		// Function to toggle necessary changes when updating the sex
		$scope.sexUpdate = function (sex) {

			if (!$scope.demoFilter.sex) {
				$scope.demoFilter.sex = sex.name;
			} else if ($scope.demoFilter.sex == sex.name) {
				$scope.demoFilter.sex = null; // Toggle off
			} else {
				$scope.demoFilter.sex = sex.name;
			}

			$scope.setChangesMade();
			$scope.eduMat.filters_updated = 1;

		};

		// Function to toggle necessary changes when updating the age
		$scope.ageUpdate = function () {

			$scope.setChangesMade();
			$scope.eduMat.filters_updated = 1;
		}

		// Function to check necessary form fields are complete
		$scope.checkForm = function () {
			if ($scope.eduMat.name_EN && $scope.eduMat.name_FR && (($scope.eduMat.url_EN && $scope.eduMat.url_FR)
				|| $scope.tocsComplete) && $scope.changesMade && $scope.checkTagsAdded($scope.contentTypeList)) {
				return true;
			}
			else
				return false;
		};

		$scope.setChangesMade = function () {
			$scope.changesMade = true;
		};

		$scope.validateTOCs = function () {

			$scope.setChangesMade();
			$scope.tocsComplete = true;
			$scope.eduMat.tocs_updated = 1;
			if (!$scope.eduMat.tocs.length) {
				$scope.tocsComplete = false;
				$scope.eduMat.tocs_updated = 0;
			}
			else {
				angular.forEach($scope.eduMat.tocs, function (toc) {
					if (!toc.name_EN || !toc.name_FR || !toc.url_EN
						|| !toc.url_FR || !toc.type_EN || !toc.type_FR) {
						$scope.tocsComplete = false;
					$scope.eduMat.tocs_updated = 0;
					}
				});
			}
		}

		// Function to return boolean for # of added content type tags
		$scope.checkTagsAdded = function (contentTypeList) {

			var addedParam = false;
			angular.forEach(contentTypeList, function (contentType) {
				if (contentType.added)
					addedParam = true;
			});
			if (addedParam)
				return true;
			else
				return false;
		};

		// Function to validate english share url
		$scope.validShareURLEN = { status: null, message: null };
		$scope.validateShareURLEN = function (url) {
			if (!url) {
				$scope.validShareURLEN.status = null;
				$scope.setChangesMade();
				return;
			}
			// regex to check pdf extension
			var re = /(?:\.([^.]+))?$/;
			if (re.exec(url)[1] != 'pdf') {
				$scope.validShareURLEN.status = 'invalid';
				$scope.validShareURLEN.message = 'URL must be a pdf';
				$scope.setChangesMade();
				return;
			} else {
				$scope.validShareURLEN.status = 'valid';
				$scope.validShareURLEN.message = null;
				$scope.setChangesMade();
			}
		}

		// Function to validate french share url
		$scope.validShareURLFR = { status: null, message: null };
		$scope.validateShareURLFR = function (url) {
			if (!url) {
				$scope.validShareURLFR.status = null;
				$scope.setChangesMade();
				return;
			}
			// regex to check pdf extension
			var re = /(?:\.([^.]+))?$/;
			if (re.exec(url)[1] != 'pdf') {
				$scope.validShareURLFR.status = 'invalid';
				$scope.validShareURLFR.message = 'URL must be a pdf';
				$scope.setChangesMade();
				return;
			} else {
				$scope.validShareURLFR.status = 'valid';
				$scope.validShareURLFR.message = null;
				$scope.setChangesMade();
			}
		}

		// Submit changes
		$scope.updateEduMat = function () {

			if ($scope.checkForm()) {

				// Initialize filter
				$scope.eduMat.filters = [];
				$scope.eduMat.content_types = [];

				// Add demographic filters, if defined
				if ($scope.demoFilter.sex)
					$scope.eduMat.filters.push({ id: $scope.demoFilter.sex, type: 'Sex' });
				if ($scope.demoFilter.age.min >= 0 && $scope.demoFilter.age.max <= 100) { // i.e. not empty
					if ($scope.demoFilter.age.min !== 0 || $scope.demoFilter.age.max != 100) { // Filters were changed
						$scope.eduMat.filters.push({
							id: String($scope.demoFilter.age.min).concat(',', String($scope.demoFilter.age.max)),
							type: 'Age'
						});
					}
				}

				// Add filters to edu material
				addFilters($scope.appointmentList);
				addFilters($scope.dxFilterList);
				addFilters($scope.doctorFilterList);
				addFilters($scope.resourceFilterList);
				addFilters($scope.patientFilterList);

				// Add content type tags to edu material
				addContentTypes($scope.contentTypeList);

				// Log who updated educational material
				var currentUser = Session.retrieveObject('user');
				$scope.eduMat.user = currentUser;

				// Submit form
				$.ajax({
					type: "POST",
					url: "php/educational-material/update.educational_material.php",
					data: $scope.eduMat,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.eduMat.name_EN + "/ " + $scope.eduMat.name_FR + "\"!";
							$scope.showBanner();
							$uibModalInstance.close();
						}
						else {
							$scope.setBannerModalClass('danger');
							$scope.bannerMessageModal = response.message;
							$scope.$apply();
							$scope.showBannerModal();
						}
						
					}
				});
			}
		};

		// Function to add table of contents to eduMat object
		$scope.addTOC = function () {
			var newOrder = $scope.eduMat.tocs.length + 1;
			$scope.eduMat.tocs.push({
				name_EN: "",
				name_FR: "",
				url_EN: "",
				url_FR: "",
				order: newOrder,
				serial: null
			});
			$scope.validateTOCs();
		};

		// Function to remove table of contents from eduMat object
		$scope.removeTOC = function (order) {
			$scope.eduMat.tocs.splice(order - 1, 1);
			// Decrement orders for content after the one just removed
			for (var index = order - 1; index < $scope.eduMat.tocs.length; index++) {
				$scope.eduMat.tocs[index].order -= 1;
			}
			$scope.validateTOCs();
		};

		// Function to return filters that have been checked
		function addFilters(filterList) {
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					$scope.eduMat.filters.push({ id: Filter.id, type: Filter.type });
			});
		}

		// Function to return content types that have been checked
		function addContentTypes(contentTypeList) {
			angular.forEach(contentTypeList, function (contentType) {
				if (contentType.added)
					$scope.eduMat.content_types.push({ serial: contentType.serial });
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

		// Function to accept/trust html (styles, classes, etc.)
		$scope.deliberatelyTrustAsHtml = function (htmlSnippet) {
			return $sce.trustAsHtml(htmlSnippet);
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