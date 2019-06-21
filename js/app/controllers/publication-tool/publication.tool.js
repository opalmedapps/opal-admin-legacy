angular.module('opalAdmin.controllers.publication.tool', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('publication.tool', function ($sce, $scope, $state, $filter, $timeout, $uibModal, questionnaireCollectionService, filterCollectionService, Session, uiGridConstants) {

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		// navigating functions
		$scope.goToQuestionnaire = function () {
			$state.go('questionnaire');
		};
		$scope.goToPublicationTool = function () {
			$state.go('publication-tool');
		};
		$scope.goToAddPublicationTool = function () {
			$state.go('publication-tool-add');
		};
		$scope.goToQuestionnaireQuestionBank = function () {
			$state.go('questionnaire-question');
		};
		$scope.goToQuestionnaireCompleted = function () {
			$state.go('questionnaire-completed');
		};
		// Function to go to question type page
		$scope.goToTemplateQuestion = function () {
			$state.go('questionnaire-template-question');
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
		var cellTemplateExpressions = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'<strong><a href="">{{row.entity.expression_EN}} / {{row.entity.expression_FR}}</a></strong></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editLegacyQuestionnaire(row.entity)">Edit</a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editPublishedQuestionnaire(row.entity)">' +
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
		var cellTemplatePublish = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updatePublishFlag(row.entity.publish)" ng-model="row.entity.publish"></div>';



		// Data binding for main table
		$scope.gridOptions = {
			data: 'publishedQuestionnaireList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '25%' },
				{
					field: 'publish', displayName: 'Publish', cellTemplate: cellTemplatePublish, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: 'Yes' }, { value: '0', label: 'No' }]
					}
				},
				{ field: 'expression_EN', name: 'Questionnaire name', cellTemplate: cellTemplateExpressions, filter: 'text'},
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
		$scope.publishedQuestionnaireList = [];
		$scope.publishedQuestionnaireFlags = {
			flagList: []
		};

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
		$scope.checkPublishFlag = function (publishedQuestionnaire) {

			$scope.changesMade = true;
			publishedQuestionnaire.publish = parseInt(publishedQuestionnaire.publish);
			// If the "publish" column has been checked
			if (publishedQuestionnaire.publish) {
				publishedQuestionnaire.publish = 0; // set publish to "false"
			}

			// Else the "Publish" column was unchecked
			else {
				publishedQuestionnaire.publish = 1; // set publish to "true"
			}
			publishedQuestionnaire.changed = 1;
		};
		
		// Call API to get the list of questionnaires
		questionnaireCollectionService.getPublishedQuestionnaires(OAUserId).then(function (response) {
			$scope.publishedQuestionnaireList = response.data;
		}).catch(function(response) {
			alert('Error occurred getting published questionnaire list: ' + response.status + " " + response.data);
		});

		// Initialize a scope variable for a selected questionnaire
		$scope.currentPublishedQuestionnaire = {};

		// Function to submit changes when flags have been modified
		$scope.submitPublishFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.publishedQuestionnaireList, function (publishedQuestionnaire) {
					if (publishedQuestionnaire.changed) {
						$scope.publishedQuestionnaireFlags.flagList.push({
							serial: publishedQuestionnaire.serial,
							publish: publishedQuestionnaire.publish
						});
					}
				});
				// Log who updated legacy questionnaire flags
				var currentUser = Session.retrieveObject('user');
				$scope.publishedQuestionnaireFlags.OAUserId = currentUser.id;
				$scope.publishedQuestionnaireFlags.sessionId = currentUser.sessionid;
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/questionnaire/update.questionnaire_publish_flag.php",
					data: $scope.publishedQuestionnaireFlags,
					success: function (response) {
						// Call our API to get the list of existing legacy questionnaires
						questionnaireCollectionService.getPublishedQuestionnaires(OAUserId).then(function (response) {
							$scope.publishedQuestionnaireList = response.data;
						}).catch(function(response) {
							alert('Error occurred getting published questionnaire list: ' + response.status + " " + response.data);
						});
						response = JSON.parse(response);
						if (response.code === 200) {
							$scope.setBannerClass('success');
							$scope.bannerMessage = "Flag(s) Successfully Saved!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.bannerMessage = "A problem occurs. " + response.message;
						}
						$scope.showBanner();
						$scope.changesMade = false;
						$scope.publishedQuestionnaireFlags.flagList = [];

					}
				});
			}
		};
		
		// Function to edit questionnaire
		$scope.editPublishedQuestionnaire = function (questionnaire) {
			$scope.currentPublishedQuestionnaire = questionnaire;
			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'templates/questionnaire/edit.publication.tool.html',
				controller: 'publication.tool.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				questionnaireCollectionService.getPublishedQuestionnaires(OAUserId).then(function (response) {
					$scope.publishedQuestionnaireList = response.data;
				}).catch(function(response) {
					alert('Error occurred getting questionnaire list after modal close: ' + response.status + " " + response.data);
				});
			});
		};

	});
