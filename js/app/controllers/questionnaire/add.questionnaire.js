angular.module('opalAdmin.controllers.questionnaire.add', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.pagination', 'ui.grid.selection', 'ui.grid.resizeColumns']).

	controller('questionnaire.add', function ($scope, $state, $filter, $uibModal, questionnaireCollectionService, filterCollectionService, Session, uiGridConstants) {

		// navigation function
		$scope.goBack = function () {
			$state.go('questionnaire');
		};

		// Default booleans
		$scope.titleSection = { open: false, show: true };
		$scope.privacySection = { open: false, show: false };
		$scope.questionsSection = { open: false, show: false };
		$scope.tagsSection = { open: false, show: false };
		$scope.demoSection = {open:false, show:false};
		$scope.filterSection = {open:false, show:false};

		// get current user id
		var user = Session.retrieveObject('user');
		var userId = user.id;

		// initialize variables
		$scope.tagList = [];
		$scope.groupList = [];
		$scope.selectedGroups;
		$scope.tagFilter = "";

		// step bar
		var steps = {
			title: { completed: false },
			privacy: { completed: false },
			questions: { completed: false },
			tags: { completed: false }
		};

		$scope.numOfCompletedSteps = 0;
		$scope.stepTotal = 4;
		$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

		/* Function for the "Processing" dialog */
		var processingModal;
		$scope.showProcessingModal = function () {

			processingModal = $uibModal.open({
				templateUrl: 'templates/processingModal.html',
				backdrop: 'static',
				keyboard: false,
			});
		};
		$scope.showProcessingModal(); // Calling function

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

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// Initialize search field variables
		$scope.appointmentSearchField = "";
		$scope.dxSearchField = "";
		$scope.doctorSearchField = "";
		$scope.resourceSearchField = "";
		$scope.patientSearchField = "";

		// new questionnaire object
		$scope.newQuestionnaire = {
			name_EN: "",
			name_FR: "",
			private: undefined,
			publish: 0,
			created_by: userId,
			last_updated_by: userId,
			groups: [],
			tags: [],
			filters: []
		};

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

		// Initialize lists to hold filters
		$scope.demoFilter = {
			sex: null,
			age: {
				min: 0,
				max: 100
			}
		};

		$scope.appointmentList = [];
		$scope.dxFilterList = [];
		$scope.doctorFilterList = [];
		$scope.resourceFilterList = [];
		$scope.patientFilterList = [];

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

		// update form functions
		$scope.titleUpdate = function () {

			$scope.titleSection.open = true;

			if (!$scope.newQuestionnaire.name_EN && !$scope.newQuestionnaire.name_FR) {
				$scope.titleSection.open = false;
			}

			if ($scope.newQuestionnaire.name_EN && $scope.newQuestionnaire.name_FR) {

				$scope.privacySection.show = true;

				steps.title.completed = true;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			} else {

				steps.title.completed = false;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}
		};

		$scope.privacyUpdate = function (value) {

			$scope.privacySection.open = true;

			if (value == 0 || value == 1) {

				// update value
				$scope.newQuestionnaire.private = value;

				$scope.questionsSection.show = true;

				steps.privacy.completed = true;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			} else {

				steps.privacy.completed = false;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}
		};

		var tagsUpdate = function (tagList) {

			$scope.tagsSection.open = true;

			// update steps bar
			if ($scope.checkTags(tagList)) {

				$scope.demoSection.show = true;
				$scope.filterSection.show = true;

				steps.tags.completed = true;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			} else {

				$scope.tagsSection.open = false;
				steps.tags.completed = false;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}
		};

		var questionsUpdate = function () {

			$scope.questionsSection.open = true;
			if ($scope.newQuestionnaire.groups.length) {

				$scope.tagsSection.show = true;
				steps.questions.completed = true;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			} else {

				$scope.questionsSection.open = false
				steps.questions.completed = false;
				$scope.numOfCompletedSteps = stepsCompleted(steps);
				$scope.stepProgress = trackProgress($scope.numOfCompletedSteps, $scope.stepTotal);

			}
		};

		// Function to toggle necessary changes when updating the sex
		$scope.sexUpdate = function (sex) {

			$scope.demoSection.open = true;

			if (!$scope.demoFilter.sex) {
				$scope.demoFilter.sex = sex.name;
			} else if ($scope.demoFilter.sex == sex.name) {
				$scope.demoFilter.sex = null; // Toggle off
			} else {
				$scope.demoFilter.sex = sex.name;
			}

		};

		// Function to toggle necessary changes when updating the age 
		$scope.ageUpdate = function () {

			$scope.demoSection.open = true;
			
		};

		// table
		// Filter in table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN', 'category_EN', 'library_name_EN'].forEach(function (field) {
					if (row.entity[field].match(matcher)) {
						match = true;
					}
				});
				if (!match) {
					row.visible = false;
				}
			});
			return renderableRows;
		};

		// Template for table
		var cellTemplateName = '<div class="ui-grid-cell-contents" ' +
			'<p>{{row.entity.name_EN}} / {{row.entity.name_FR}}</p></div>';
		var cellTemplateCat = '<div class="ui-grid-cell-contents" ' +
			'<p>{{row.entity.category_EN}} / {{row.entity.category_FR}}</p></div>';
		var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
			'<p>{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</p></div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
		var cellTemplateTags = '<div class="ui-grid-cell-contents" ng-repeat="tag in row.entity.tags"' +
			'<p>{{tag.name_EN}} / {{tag.name_FR}} ;</p></div>';


		// Table Data binding
		$scope.gridOptions = {
			data: 'groupList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Group (EN / FR)', cellTemplate: cellTemplateName, width: '20%' },
				{ field: 'category_EN', displayName: 'Category (EN / FR)', cellTemplate: cellTemplateCat, width: '25%' },
				{ field: 'library_name_EN', displayName: 'Library (EN / FR)', cellTemplate: cellTemplateLib, width: '15%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
					}
				},
				{ field: 'tags', displayName: 'Tags (EN / FR)', cellTemplate: cellTemplateTags, enableFiltering: false, width: '30%' }
			],
			enableColumnResizing: true,
			enableFiltering: true,
			enableSorting: true,
			enableRowSelection: true,
			//enableSelectAll: true,
			enableSelectionBatchEvent: true,
			//showGridFooter:true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
				gridApi.selection.on.rowSelectionChanged($scope, function (row) {
					selectUpdate();
					questionsUpdate();
				});
			},
		};

		// cancel selection
		$scope.cancelSelection = function () {
			$scope.gridApi.selection.clearSelectedRows();
			selectUpdate();
		};

		// select all rows
		$scope.selectAll = function () {
			$scope.gridApi.selection.selectAllRows();
			selectUpdate();
		};

		// function to update the newQuestionnaire content after changing selection
		var selectUpdate = function () {
			$scope.selectedGroups = $scope.gridApi.selection.getSelectedGridRows();
			var selectedNum = $scope.gridApi.selection.getSelectedCount();
			if (selectedNum === 0) {
				$scope.newQuestionnaire.groups = [];
			} else {
				var tempGroupArray = [];
				for (var i = 0; i < selectedNum; i++) {
					var group = {
						position: i + 1,
						questiongroup_serNum: $scope.selectedGroups[i].entity.serNum,
						optional: 0,
						name_EN: $scope.selectedGroups[i].entity.name_EN,
						name_FR: $scope.selectedGroups[i].entity.name_FR
					};
					tempGroupArray.push(group);
					$scope.newQuestionnaire.groups = tempGroupArray.slice(0);
				}
			}
		};

		// API getting group list
		questionnaireCollectionService.getQuestionGroupWithLibraries(userId).then(function (response) {
			$scope.groupList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting group list:', response.status, response.data);
		});

		// get tag list
		questionnaireCollectionService.getTags().then(function (response) {
			$scope.tagList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting tags:', response.status, response.data);
		});

		// assign search field 
		$scope.searchTag = function (field) {
			$scope.tagFilter = field;
		};

		// search filter
		$scope.searchTagFilter = function (Filter) {
			var keyword = new RegExp($scope.tagFilter, 'i');
			return !$scope.tagFilter || keyword.test(Filter.name_EN);
		};

		// Function to toggle Item in a list on/off
		$scope.selectTag = function (tag) {
			if (tag.added) {
				tag.added = 0;
			} else {
				tag.added = 1;
			}

			tagsUpdate($scope.tagList);
		};

		// add tags
		function addTags(tagList) {
			angular.forEach(tagList, function (Filter) {
				if (Filter.added)
					$scope.newQuestionnaire.tags.push(Filter.serNum);
			});
		}

		// check if there's any tag added
		$scope.checkTags = function (tagList) {
			var tagsAdded = false;
			angular.forEach(tagList, function (Filter) {
				if (Filter.added)
					tagsAdded = true;
			});
			return tagsAdded;
		};

		// new tag
		$scope.newTag = {
			name_EN: '',
			name_FR: '',
			level: undefined,
			last_updated_by: userId,
			created_by: userId
		};

		$scope.addNewTag = function () {

			// Prompt to confirm user's action
			var confirmation = confirm("Confirm to create the new tag [" + $scope.newTag.name_EN + "].");
			if (confirmation) {
				// write in to db
				$.ajax({
					type: "POST",
					url: "php/questionnaire/insert.tag.php",
					data: $scope.newTag,
					success: function () {
						alert('Successfully added the new tag. Please find your new tag in the form above.');
						// update answer type list
						questionnaireCollectionService.getTags().then(function (response) {
							$scope.tagList = response.data;
						}).catch(function(response) {
							console.error('Error occurred getting tags:', response.status, response.data);
						});

					},
					error: function () {
						alert("Something went wrong.");
					}
				});
			} else {
				// do nothing
				console.log("Cancel creating new tag.")
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

		// Function to return filters that have been checked
		function addFilters(filterList) {
			angular.forEach(filterList, function (Filter) {
				if (Filter.added)
					$scope.newQuestionnaire.filters.push({ id: Filter.id, type: Filter.type });
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

		// submit 
		$scope.submitQuestionnaire = function () {
			if ($scope.checkForm()) {
				// Add tags
				addTags($scope.tagList);

				// Add demographic filters, if defined
				if ($scope.demoFilter.sex)
					$scope.newQuestionnaire.filters.push({ id: $scope.demoFilter.sex, type: 'Sex' });
				if ($scope.demoFilter.age.min >= 0 && $scope.demoFilter.age.max <= 100) { // i.e. not empty
					if ($scope.demoFilter.age.min !== 0 || $scope.demoFilter.age.max != 100) { // Filters were changed
						$scope.newQuestionnaire.filters.push({
							id: String($scope.demoFilter.age.min).concat(',', String($scope.demoFilter.age.max)),
							type: 'Age'
						});
					}
				}
				// Add other filters to new questionnaire object
				addFilters($scope.appointmentList);
				addFilters($scope.dxFilterList);
				addFilters($scope.doctorFilterList);
				addFilters($scope.resourceFilterList);
				addFilters($scope.patientFilterList);

				// Log who created questionnaire
				var currentUser = Session.retrieveObject('user');
				$scope.newQuestionnaire.user = currentUser;
				// Submit 
				$.ajax({
					type: "POST",
					url: "php/questionnaire/insert.questionnaire.php",
					data: $scope.newQuestionnaire,
					success: function () {
						$state.go('questionnaire');
					}
				});
			}
		};

		var fixmeTop = $('.summary-fix').offset().top;
		$(window).scroll(function () {
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
