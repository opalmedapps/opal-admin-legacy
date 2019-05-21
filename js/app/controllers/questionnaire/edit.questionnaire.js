angular.module('opalAdmin.controllers.questionnaire.edit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('questionnaire.edit', function ($sce, $scope, $state, $filter, $timeout, $uibModal, $uibModalInstance, questionnaireCollectionService, filterCollectionService, Session, uiGridConstants) {

		// get current user id
		var user = Session.retrieveObject('user');
		var userId = user.id;

		// initialize default variables & lists
		$scope.changesMade = false;
		$scope.questionnaire = {};

		// Responsible for "searching" in search bars
		$scope.filter = $filter('filter');

		// initialize variables
		$scope.groupList = [];
		$scope.selectedGroups;
		$scope.tagFilter = "";
		$scope.anyPrivate = false;
		var publicPrivateWarning = true;

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
		// var cellTemplateCat = '<div class="ui-grid-cell-contents" ' +
		// 	'<p>{{row.entity.category_EN}} / {{row.entity.category_FR}}</p></div>';
		var cellTemplateLib = '<div class="ui-grid-cell-contents" ' +
			'<p>{{row.entity.library_name_EN}} / {{row.entity.library_name_FR}}</p></div>';
		var cellTemplatePrivacy = '<div class="ui-grid-cell-contents" ng-show="row.entity.private == 0"><p>Public</p></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.private == 1"><p>Private</p></div>';
		// var cellTemplateTags = '<div class="ui-grid-cell-contents">' +
		// 	'<span ng-repeat="tag in row.entity.tags">{{tag.name_EN}} / {{tag.name_FR}} ; </span></div>';

		// Table Data binding
		$scope.gridGroups = {
			data: 'groupList',
			columnDefs: [
				{ field: 'text_EN', displayName: 'Name (EN / FR)', cellTemplate: cellTemplateName, width: '54%' },
//				{ field: 'category_EN', displayName: 'Category (EN / FR)', cellTemplate: cellTemplateCat, width: '25%' },
				{ field: 'library_name_EN', displayName: 'Library (EN / FR)', cellTemplate: cellTemplateLib, width: '30%' },
				{
					field: 'private', displayName: 'Privacy', cellTemplate: cellTemplatePrivacy, width: '13%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Private' }, { value: '0', label: 'Public' }]
					}
				},
//				{ field: 'tags', displayName: 'Tags (EN / FR)', cellTemplate: cellTemplateTags, enableFiltering: true, width: '30%' }
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
				if (row.entity.ID == selectedGroup.entity.ID) {
					wasSelected = true;
				}
			});

			var groupIndex = null;
			if (!wasSelected) {  // Deselected
				angular.forEach($scope.questionnaire.groups, function(group, index) {
					if(group.ID == row.entity.ID) {
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
					if (row.entity.ID == group.ID) {
						inGroups = true;
					}
				});

				if (!inGroups) { // If not, append it to existing groups
					var currentPosition = $scope.questionnaire.groups.length + 1;
					var group = {
						questionnaire_ID: $scope.questionnaire.ID,
						created_by: userId,
						ID: row.entity.ID,
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
				templateUrl: 'templates/processingModal.html',
				backdrop: 'static',
				keyboard: false,
			});
		};

		// Show processing dialog upon first load
		$scope.showProcessingModal();

		// Call our API service to get questionnaire details
		questionnaireCollectionService.getQuestionnaireDetails($scope.currentQuestionnaire.ID, userId).then(function (response) {

			// Assign value
			$scope.questionnaire = response.data;

		}).catch(function (response) {
			alert('Error occurred getting questionnaire details after modal open: ' + response.status + " " + response.data);
		}).finally(function () {
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

		// Function to assign '1' to existing filters 
		// function checkAddedFilter(filterList) {
		// 	angular.forEach($scope.questionnaire.filters, function (selectedFilter) {
		// 		var selectedFilterId = selectedFilter.id;
		// 		var selectedFilterType = selectedFilter.type;
		// 		angular.forEach(filterList, function (filter) {
		// 			var filterId = filter.id;
		// 			var filterType = filter.type;
		// 			if (filterId == selectedFilterId && filterType == selectedFilterType) {
		// 				filter.added = 1;
		// 			}
		// 		});
		// 	});
		//
		// 	return filterList;
		// }

		// Function to assign a "1" to existing tags
		// function checkAdded(filterList) {
		// 	angular.forEach($scope.questionnaire.tags, function (selectedFilter) {
		// 		var selectedFilterId = selectedFilter.ID;
		// 		angular.forEach(filterList, function (filter) {
		// 			var filterId = filter.ID;
		// 			if (filterId == selectedFilterId) {
		// 				filter.added = 1;
		// 			}
		// 		});
		// 	});
		// 	return filterList;
		// }

		// assign search field for tags
		// $scope.searchTag = function (field) {
		// 	$scope.tagFilter = field;
		// };
		//
		// // search filter for tags
		// $scope.searchTagFilter = function (Filter) {
		// 	var keyword = new RegExp($scope.tagFilter, 'i');
		// 	return !$scope.tagFilter || keyword.test(Filter.name_EN);
		// };
		//
		// // Function to toggle Tag in a list on/off
		// $scope.selectTag = function (tag) {
		// 	$scope.changesMade = true;
		// 	if (tag.added) {
		// 		tag.added = 0;
		// 	} else {
		// 		tag.added = 1;
		// 	}
		// };

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
		// function addFilters(filterList) {
		// 	angular.forEach(filterList, function (Filter) {
		// 		if (Filter.added)
		// 			$scope.questionnaire.filters.push({ id: Filter.id, type: Filter.type });
		// 	});
		// }

		// Function to check if all filters are added
		// $scope.allFilters = function (filterList) {
		// 	var allFiltersAdded = true;
		// 	angular.forEach(filterList, function (Filter) {
		// 		if (Filter.added)
		// 			allFiltersAdded = false;
		// 	});
		// 	return allFiltersAdded;
		// };

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

				// Log who updated questionnaire
				var currentUser = Session.retrieveObject('user');
				$scope.questionnaire.user = currentUser;
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

	});