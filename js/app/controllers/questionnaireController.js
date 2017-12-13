angular.module('opalAdmin.controllers.questionnaireController', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('questionnaireController', function ($sce, $scope, $state, $filter, $timeout, $uibModal, questionnaireCollectionService, filterCollectionService, Session, uiGridConstants) {
		
		// get current user id
		var user = Session.retrieveObject('user');
		var userId = user.id;

		// navigating functions
		$scope.goToQuestionnaire = function () {
			$state.go('questionnaire');
		};
		$scope.goToAddQuestionnaire = function () {
			$state.go('questionnaire-add');
		};
		$scope.goToQuestionnaireQuestionBank = function () {
			$state.go('questionnaire-question');
		};
		$scope.goToQuestionnaireCompleted = function () {
			$state.go('questionnaire-completed');
		};

		// Banner
		$scope.bannerMessage = "";
		// Function to show page banner 
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 3000);
			});
		};

		// Function to set banner class
		$scope.setBannerClass = function (classname) {
			// Remove any classes starting with "alert-" 
			$(".bannerMessage").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessage").addClass('alert-' + classname);
		};

		// Filter
		// search text-box param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN'].forEach(function (field) {
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

		// Function to filter questionnaires
		$scope.filterQuestionnaire = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table
		// Templates
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editQuestionnaire(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteQuestionnaire(row.entity)">Delete</a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editQuestionnaire(row.entity)">' +
			'<a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
		var cellTemplatePublish = '<div class="ui-grid-cell-contents" ng-show="row.entity.publish == 0"><p>No</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.publish == 1"><p>Yes</p></div>';
		var cellTemplateTags = '<div class="ui-grid-cell-contents">' +
			'<span ng-repeat="tag in row.entity.tags">{{tag.name_EN}} / {{tag.name_FR}} ; </span></div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'questionnaireList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '25%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
					}
				},
				{
					field: 'publish', displayName: 'Publish', cellTemplate: cellTemplatePublish, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Yes' }, { value: '0', label: 'No' }]
					}
				},
				{ field: 'created_by', displayName: 'Author', width: '10%' },
				{ field: 'tags', displayName: 'Tags (EN / FR)', cellTemplate: cellTemplateTags, width: '30%', enableFiltering: false },
				{ name: 'Operations', width: '15%', cellTemplate: cellTemplateOperations, enableFiltering: false, sortable: false }
			],
			enableFiltering: true,
			enableSorting: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize object for storing questionnaires
		$scope.questionnaireList = [];

		// Call API to get the list of questionnaires
		questionnaireCollectionService.getQuestionnaires(userId).then(function (response) {
			$scope.questionnaireList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting questionnaire list:', response.status, response.data);
		});	

		// Initialize the questionnaire to be deleted
		$scope.questionnaireToDelete = {};

		// Function to delete questionnaire
		$scope.deleteQuestionnaire = function (questionnaire) {
			$scope.questionnaireToDelete = questionnaire;

			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'deleteQuestionnaireModalContent.htm',
				controller: DeleteQuestionnaireModalInstanceCtrl,
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the questionnaire list
			modalInstance.result.then(function () {
				questionnaireCollectionService.getQuestionnaires(userId).then(function (response) {
					$scope.questionnaireList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting questionnaire list after modal close:', response.status, response.data);
				});
			});
		};

		var DeleteQuestionnaireModalInstanceCtrl = function ($scope, $uibModalInstance) {
			
			// Submit delete
			$scope.deleteQuestionnaire = function () {
				$.ajax({
					type: "POST",
					url: "php/questionnaire/delete.questionnaire.php",
					data: $scope.questionnaireToDelete,
					success: function (response) {
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.$parent.bannerMessage = "Successfully deleted \"" + $scope.questionnaireToDelete.name_EN + "/ " + $scope.questionnaireToDelete.name_FR + "\"!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.$parent.bannerMessage = response.message;
						}
						$scope.showBanner();
						$uibModalInstance.close();
					}
				});
			};

			// Function to close modal dialog
			$scope.cancel = function () {
				$uibModalInstance.dismiss('cancel');
			};
		};

		// Initialize a scope variable for a selected questionnaire
		$scope.currentQuestionnaire = {};

		// Function to edit questionnaire
		$scope.editQuestionnaire = function (questionnaire) {
			$scope.currentQuestionnaire = questionnaire;

			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'editQuestionnaireModalContent.htm',
				controller: EditQuestionnaireModalInstanceCtrl,
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				questionnaireCollectionService.getQuestionnaires(userId).then(function (response) {
					$scope.questionnaireList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting questionnaire list after modal close:', response.status, response.data);
				});
			});
		};

		// Controller for editModal
		var EditQuestionnaireModalInstanceCtrl = function ($scope, $uibModalInstance, $filter) {
			// get current user id
			var user = Session.retrieveObject('user');
			var userId = user.id;

			// initialize default variables & lists
			$scope.changesMade = false;
			$scope.questionnaire = {};

			// Responsible for "searching" in search bars
			$scope.filter = $filter('filter');

			// Initialize a list of sexes
			$scope.sexes = [
				{ name: 'Male' },
				{ name: 'Female' }
			];

			// Initialize to hold demographic filters
			$scope.demoFilter = {
				sex: null,
				age: {
					min: 0,
					max: 100
				}
			};

			// Initialize search field variables
			$scope.termSearchField = "";
			$scope.dxSearchField = "";
			$scope.doctorSearchField = "";
			$scope.resourceSearchField = "";
			$scope.patientSearchField = "";

			// Function to assign search fields when textbox changes
			$scope.searchTerm = function (field) {
				$scope.termSearchField = field;
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
			$scope.searchTermsFilter = function (Filter) {
				var keyword = new RegExp($scope.termSearchField, 'i');
				return !$scope.termSearchField || keyword.test(Filter.name);
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

			// Initialize lists to hold filters
			$scope.termList = [];
			$scope.dxFilterList = [];
			$scope.doctorFilterList = [];
			$scope.resourceFilterList = [];
			$scope.patientFilterList = [];

			// initialize variables
			$scope.tagList = [];
			$scope.groupList = [];
			$scope.selectedGroups;
			$scope.tagFilter = "";

			// table
			// Filter in table
			$scope.filterOptions = function (renderableRows) {
				var matcher = new RegExp($scope.filterValue, 'i');
				renderableRows.forEach(function (row) {
					var match = false;
					['name_EN'].forEach(function (field) {
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

			// Template for group table
			var cellTemplateName = '<div class="ui-grid-cell-contents" ' +
				'<p>{{row.entity.name_EN}} / {{row.entity.name_FR}}</p></div>';
			var cellTemplateCat = '<div class="ui-grid-cell-contents" ' +
				'<p>{{row.entity.category_EN}} / {{row.entity.category_FR}}</p></div>';
			var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
				'<p>{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</p></div>';
			var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
				'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
			var cellTemplateTags = '<div class="ui-grid-cell-contents">' +
				'<span ng-repeat="tag in row.entity.tags">{{tag.name_EN}} / {{tag.name_FR}} ; </span></div>';

			// Table Data binding
			$scope.gridGroups = {
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
					{ field: 'tags', displayName: 'Tags (EN / FR)', cellTemplate: cellTemplateTags, enableFiltering: true, width: '30%' }
				],
				enableColumnResizing: true,
				enableFiltering: true,
				enableSorting: true,
				enableRowSelection: true,
				enableSelectAll: true,
				enableSelectionBatchEvent: true,
				showGridFooter: true,
				onRegisterApi: function (gridApi) {
					$scope.gridApi = gridApi;
					gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
					gridApi.selection.on.rowSelectionChanged($scope, function (row) {
						selectUpdate(row);

					});
				},
			};

			// Function to update the newQuestionnaire groups after changing selection
			var selectUpdate = function (row) {

				// get selected rows
				$scope.selectedGroups = $scope.gridApi.selection.getSelectedGridRows();

				// sort question groups by retrieved position
				$scope.questionnaire.groups.sort(function(a,b){
					return (a.position > b.position) ? 1 : ((b.position > a.position) ? -1 : 0);
				});

				// Check to see if current row was (de)selected
				var wasSelected = false;
				angular.forEach($scope.selectedGroups, function(selectedGroup) {
					if (row.entity.serNum == selectedGroup.entity.serNum) {
						wasSelected = true;
					}
				});

				var groupIndex = null;
				if (!wasSelected) {  // Deselected
					angular.forEach($scope.questionnaire.groups, function(group, index) {
						if(group.serNum == row.entity.serNum) {
							groupIndex = index; // array index of the group that was removed
						}
					});
					$scope.questionnaire.groups.splice(groupIndex, 1); // Take it out of the array
					angular.forEach($scope.questionnaire.groups, function(group, index) {
						group.position = index + 1; // Refactor the positions of the leftover groups
					});
					$scope.changesMade = true; // set changes made
				}
				else {
					// Check to see if added row exists already in the groups
					var inGroups = false;
					angular.forEach($scope.questionnaire.groups, function(group){
						if (row.entity.serNum == group.serNum) {
							inGroups = true;
						}
					});

					if (!inGroups) { // If not, append it to existing groups
						var currentPosition = $scope.questionnaire.groups.length + 1;
						var group = {
							questionnaire_serNum: $scope.questionnaire.serNum,
							created_by: userId,
							last_updated_by: userId,
							serNum: row.entity.serNum,
							optional: 0,
							position: currentPosition,
							name_EN: row.entity.name_EN,
							name_FR: row.entity.name_FR
						};
						$scope.questionnaire.groups.push(group);
						$scope.changesMade = true; // set changes made
					}
				}
			};

			/* Function for the "Processing" dialog */
			var processingModal;
			$scope.showProcessingModal = function () {

				processingModal = $uibModal.open({
					templateUrl: 'processingModal.htm',
					backdrop: 'static',
					keyboard: false,
				});
			};

			// Show processing dialog upon first load
			$scope.showProcessingModal();

			// Call our API service to get questionnaire details
			questionnaireCollectionService.getQuestionnaireDetails($scope.currentQuestionnaire.serNum).then(function (response) {

				// Assign value
				$scope.questionnaire = response.data;

			}).catch(function (response) {
				console.error('Error occurred getting questionnaire details after modal open:', response.status, response.data);
				
			}).finally(function () {

				// Call our API service to get the list of possible question groups
				questionnaireCollectionService.getQuestionGroupWithLibraries(userId).then(function (response) {

					$scope.groupList = response.data; // Assign response data

					// This preselects the existing question groups in the table
					$timeout(function () {
						if ($scope.gridApi.selection.selectRow) {
							angular.forEach($scope.questionnaire.groups, function (selectedGroup) {
								angular.forEach($scope.groupList, function (group) {
									if (selectedGroup.serNum == group.serNum) {
										$scope.gridApi.selection.selectRow(group);
									}
								});
							});
						}
					});

				}).catch(function (response){
					console.error('Error occurred getting question groups:', response.status, response.data);
				});

				// Call our API service to get the list of possible tags
				questionnaireCollectionService.getTags().then(function (response) {
					$scope.tagList = checkAdded(response.data); // Assign value and check those that were already added
				}).catch(function(response) {
					console.error('Error occurred getting tags:', response.status, response.data);
				});

				// Assign demographic filters
				checkDemographicFilters();

				// Call our API service to get each filter
				filterCollectionService.getFilters().then(function (response) {

					$scope.termList = checkAddedFilter(response.data.expressions); // Assign value
					$scope.dxFilterList = checkAddedFilter(response.data.dx);
					$scope.doctorFilterList = checkAddedFilter(response.data.doctors);
					$scope.resourceFilterList = checkAddedFilter(response.data.resources);
					$scope.patientFilterList = checkAddedFilter(response.data.patients);

				}).catch(function(response) {
					console.error('Error occurred getting filter list:', response.status, response.data);
				});

				processingModal.close(); // hide modal
				processingModal = null; // remove reference
			});

			// Function to toggle Item in a list on/off
			$scope.selectItem = function (item) {
				$scope.changesMade = true;
				if (item.added)
					item.added = 0;
				else
					item.added = 1;
			};

			// Function for selecting all terms in the expression list
			var selectAllTerms = false;
			$scope.selectAllTerms = function () {
				var filtered = $scope.filter($scope.termList, $scope.termSearchField);

				$scope.changesMade = true;

				if (selectAllTerms) {
					angular.forEach(filtered, function (term) {
						term.added = 0;
					});
					selectAllTerms = !selectAllTerms;
				} else {
					angular.forEach(filtered, function (term) {
						term.added = 1;
					});
					selectAllTerms = !selectAllTerms;
				}
			};

			// Function to assign '1' to existing filters 
			function checkAddedFilter(filterList) {
				angular.forEach($scope.questionnaire.filters, function (selectedFilter) {
					var selectedFilterId = selectedFilter.id;
					var selectedFilterType = selectedFilter.type;
					angular.forEach(filterList, function (filter) {
						var filterId = filter.id;
						var filterType = filter.type;
						if (filterId == selectedFilterId && filterType == selectedFilterType) {
							console.log("HERE");
							filter.added = 1;
						}
					});
				});

				return filterList;
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
				angular.forEach($scope.questionnaire.filters, function (selectedFilter) {
					if (selectedFilter.type == 'Sex')
						$scope.demoFilter.sex = selectedFilter.id;
					if (selectedFilter.type == 'Age') {
						$scope.demoFilter.age.min = parseInt(selectedFilter.id.split(',')[0]);
						$scope.demoFilter.age.max = parseInt(selectedFilter.id.split(',')[1]);
					}
				});

				return demoFilter;
			}

			// Function to assign a "1" to existing tags
			function checkAdded(filterList) {
				angular.forEach($scope.questionnaire.tags, function (selectedFilter) {
					var selectedFilterId = selectedFilter.serNum;
					angular.forEach(filterList, function (filter) {
						var filterId = filter.serNum;
						if (filterId == selectedFilterId) {
							filter.added = 1;
						}
					});
				});
				return filterList;
			}

			// assign search field for tags
			$scope.searchTag = function (field) {
				$scope.tagFilter = field;
			};

			// search filter for tags
			$scope.searchTagFilter = function (Filter) {
				var keyword = new RegExp($scope.tagFilter, 'i');
				return !$scope.tagFilter || keyword.test(Filter.name_EN);
			};

			// Function to toggle Tag in a list on/off
			$scope.selectTag = function (tag) {
				$scope.changesMade = true;
				if (tag.added) {
					tag.added = 0;
				} else {
					tag.added = 1;
				}
			};

			// add tags to the questionnaire tag array
			function addTags(tagList) {
				angular.forEach(tagList, function (Filter) {
					if (Filter.added) {
						$scope.questionnaire.tags.push(Filter);
					}
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

			// Function called when changing the questionnaire privacy flag
			$scope.privacyUpdate = function (value) {
				if (value == 0 || value == 1) {
					// update value
					$scope.questionnaire.private = value;
				}
				$scope.changesMade = true;
			};

			// Function called whenever there has been a change in the form
			$scope.setChangesMade = function () {
				$scope.changesMade = true;
			};

			// Function to close edit modal dialog
			$scope.cancel = function () {
				$uibModalInstance.dismiss('cancel');
			};

			// Function to check necessary form fields are complete
			$scope.checkForm = function () {
				if ($scope.questionnaire.name_EN && $scope.questionnaire.name_FR && $scope.questionnaire.tags.length && $scope.questionnaire.groups.length && $scope.changesMade) {
					return true;
				}
				else
					return false;
			};

			// Function to return filters that have been checked
			function addFilters(filterList) {
				angular.forEach(filterList, function (Filter) {
					if (Filter.added)
						$scope.questionnaire.filters.push({ id: Filter.id, type: Filter.type });
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

			// Function for updating the questionnaire 
			$scope.updateQuestionnaire = function () {

				if ($scope.checkForm()) {

					// Initialize filter
					$scope.questionnaire.filters = [];

					// Add demographic filters, if defined
					if ($scope.demoFilter.sex)
						$scope.questionnaire.filters.push({ id: $scope.demoFilter.sex, type: 'Sex' });
					if ($scope.demoFilter.age.min >= 0 && $scope.demoFilter.age.max <= 100) { // i.e. not empty
						if ($scope.demoFilter.age.min !== 0 || $scope.demoFilter.age.max != 100) { // Filters were changed
							$scope.questionnaire.filters.push({
								id: String($scope.demoFilter.age.min).concat(',', String($scope.demoFilter.age.max)),
								type: 'Age'
							});
						}
					}

					// Add filters to edu material
					addFilters($scope.termList);
					addFilters($scope.dxFilterList);
					addFilters($scope.doctorFilterList);
					addFilters($scope.resourceFilterList);
					addFilters($scope.patientFilterList);

					addTags($scope.tagList); // Add tags to questionnaire object
					// ajax POST
					$.ajax({
						type: "POST",
						url: "php/questionnaire/update.questionnaire.php",
						data: $scope.questionnaire,
						success: function (response) {
							response = JSON.parse(response);
							if (response.value) {
								$scope.setBannerClass('success');
								$scope.$parent.bannerMessage = "Successfully updated \"" + $scope.questionnaire.name_EN + "/ " + $scope.questionnaire.name_FR + "\"!";
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


		};


	});
