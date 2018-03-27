angular.module('opalAdmin.controllers.legacyQuestionnaire', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular', 'multipleDatePicker', 'angularjs-dropdown-multiselect']).
	
	controller('legacyQuestionnaire', function ($sce, $scope, $state, $filter, $timeout, $uibModal, legacyQuestionnaireCollectionService, filterCollectionService, uiGridConstants, FrequencyFilterService, Session) {

		$scope.goToAddLegacyQuestionnaire = function () {
			$state.go('legacy-questionnaire-add');
		};

		$scope.changesMade = false;

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
		$scope.filterLegacyQuestionnaire = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		// Table
		// Templates
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
		'<strong><a href="" ng-click="grid.appScope.editLegacyQuestionnaire(row.entity)">Edit</a></strong> ' +
		'- <strong><a href="" ng-click="grid.appScope.deleteLegacyQuestionnaire(row.entity)">Delete</a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
		'ng-click="grid.appScope.editLegacyQuestionnaire(row.entity)">' +
		'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
		var cellTemplatePublish = '<div style="text-align: center; cursor: pointer;" ' +
		'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
		'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
		'ng-checked="grid.appScope.updatePublishFlag(row.entity.publish)" ng-model="row.entity.publish"></div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'legacyQuestionnaireList',
			columnDefs: [
			{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '25%' },
			{
				field: 'publish', displayName: 'Publish', cellTemplate: cellTemplatePublish, width: '10%', filter: {
					type: uiGridConstants.filter.SELECT,
					selectOptions: [{ value: '1', label: 'Yes' }, { value: '0', label: 'No' }]
				}
			},
			{ field: 'expression', name: 'Legacy Questionnaire', filter: 'text'},
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

		// Initialize object for storing legacy questionnaires
		$scope.legacyQuestionnaireList = [];
		$scope.legacyQuestionnairePublishFlags = {
			flagList: []
		};

		// Call API to get the list of legacy questionnaires
		legacyQuestionnaireCollectionService.getLegacyQuestionnaires().then(function (response) {
			$scope.legacyQuestionnaireList = response.data;
		}).catch(function(response) {
			console.error('Error occurred getting legacy questionnaire list:', response.status, response.data);
		});	

		// When this function is called, we set the publish flags to checked 
		// or unchecked based on value in the argument
		$scope.updatePublishFlag = function (value) {
			value = parseInt(value);
			if (value == 1) {
				return 1;
			} else {
				return 0;
			}
		};


		// Function for when the publish checkbox has been modified
		$scope.checkPublishFlag = function (legacyQuestionnaire) {

			$scope.changesMade = true;
			legacyQuestionnaire.publish = parseInt(legacyQuestionnaire.publish);
			// If the "publish" column has been checked
			if (legacyQuestionnaire.publish) {
				legacyQuestionnaire.publish = 0; // set publish to "false"
			}

			// Else the "Publish" column was unchecked
			else {
				legacyQuestionnaire.publish = 1; // set publish to "true"
			}
			legacyQuestionnaire.changed = 1;
		};


		// Function to submit changes when flags have been modified
		$scope.submitPublishFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.legacyQuestionnaireList, function (legacyQuestionnaire) {
					if (legacyQuestionnaire.changed) {
						$scope.legacyQuestionnairePublishFlags.flagList.push({
							serial: legacyQuestionnaire.serial,
							publish: legacyQuestionnaire.publish
						});
					}
				});
				// Log who updated legacy questionnaire flags
				var currentUser = Session.retrieveObject('user');
				$scope.legacyQuestionnairePublishFlags.user = currentUser;
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/legacy-questionnaire/update.legacy_questionnaire_publish_flags.php",
					data: $scope.legacyQuestionnairePublishFlags,
					success: function (response) {
						// Call our API to get the list of existing legacy questionnaires
						legacyQuestionnaireCollectionService.getLegacyQuestionnaires().then(function (response) {
							// Assign value
							$scope.legacyQuestionnaireList = response.data;
						}).catch(function(response) {
							console.error('Error occurred getting legacy questionnaires:', response.status, response.data);
						});
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.bannerMessage = "Flag(s) Successfully Saved!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.bannerMessage = response.message;
						}
						$scope.showBanner();
						$scope.changesMade = false;
						$scope.legacyQuestionnairePublishFlags.flagList = [];

					}
				});
			}
		};
		// Initialize the legacy questionnaire to be deleted
		$scope.legacyQuestionnaireToDelete = {};

		// Function to delete questionnaire
		$scope.deleteLegacyQuestionnaire = function (legacyQuestionnaire) {
			$scope.legacyQuestionnaireToDelete = legacyQuestionnaire;

			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'templates/legacy-questionnaire/delete.legacy-questionnaire.html',
				controller: 'legacyQuestionnaire.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the legacy questionnaire list
			modalInstance.result.then(function () {
				legacyQuestionnaireCollectionService.getLegacyQuestionnaires().then(function (response) {
					$scope.legacyQuestionnaireList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting legacy questionnaire list after modal close:', response.status, response.data);
				});
			});
		};

		// Initialize a scope variable for a selected legacy questionnaire
		$scope.currentLegacyQuestionnaire = {};

		// Function to edit legacy questionnaire
		$scope.editLegacyQuestionnaire = function (legacyQuestionnaire) {
			$scope.currentLegacyQuestionnaire = legacyQuestionnaire;

			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'templates/legacy-questionnaire/edit.legacy-questionnaire.html',
				controller: 'legacyQuestionnaire.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the legacy questionnaire list
			modalInstance.result.then(function () {
				legacyQuestionnaireCollectionService.getLegacyQuestionnaires().then(function (response) {
					$scope.legacyQuestionnaireList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting legacy questionnaire list after modal close:', response.status, response.data);
				});
			});
		};

	})

	.filter('range', function() {
	  	return function(input, total) {
	    	total = parseInt(total);

	    	for (var i=0; i<total; i++) {
	      		input.push(i);
	    	}

	    	return input;
		};
	});
