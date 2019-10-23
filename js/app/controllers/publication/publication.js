angular.module('opalAdmin.controllers.publication', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('publication', function ($sce, $scope, $state, $filter, $timeout, $uibModal, publicationCollectionService, filterCollectionService, Session, uiGridConstants) {

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

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
				['name_'+Session.retrieveObject('user').language].forEach(function (field) {
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
			'<strong><a href="" ng-click="grid.appScope.editPublishedQuestionnaire(row.entity)"><i title="'+$filter('translate')('PUBLICATION.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editPublishedQuestionnaire(row.entity)">' +
			'<strong><a href="">{{row.entity.name_'+ Session.retrieveObject('user').language +'}}</a></strong></div>';
		var cellTemplatePublish = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updatePublishFlag(row.entity.publishFlag)" ng-model="row.entity.publishFlag"></div>';
		var cellTemplatePublication = '<div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==2">'+$filter('translate')('PUBLICATION.LIST.PUBLICATION')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==3">'+$filter('translate')('PUBLICATION.LIST.EDUCATION')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==7">'+$filter('translate')('PUBLICATION.LIST.QUESTIONNAIRE')+'</div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'publicationList',
			columnDefs: [
				{ field: 'name_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('PUBLICATION.LIST.NAME'), cellTemplate: cellTemplateName, width: '30%', sort: {direction: uiGridConstants.ASC, priority: 0} },
				{
					field: 'moduleId', displayName: $filter('translate')('PUBLICATION.LIST.TYPE'), enableColumnMenu: false, cellTemplate: cellTemplatePublication, width: '15%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '2', label: $filter('translate')('PUBLICATION.LIST.PUBLICATION') }, { value: '3', label: $filter('translate')('PUBLICATION.LIST.EDUCATION') }, { value: '7', label: $filter('translate')('PUBLICATION.LIST.QUESTIONNAIRE') }]
					}
				},
				{ field: 'type_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('PUBLICATION.LIST.DESCRIPTION')},
				{
					field: 'publishFlag', displayName: $filter('translate')('PUBLICATION.LIST.PUBLISH'), enableColumnMenu: false, cellTemplate: cellTemplatePublish, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('PUBLICATION.LIST.YES') }, { value: '0', label: $filter('translate')('PUBLICATION.LIST.NO') }]
					}
				},
				{ field: "publishDate", displayName: $filter('translate')('PUBLICATION.LIST.PUBLISH_DATE'), enableColumnMenu: false , width: '15%'},
				{ name: $filter('translate')('PUBLICATION.LIST.OPERATIONS'), width: '10%', cellTemplate: cellTemplateOperations, enableColumnMenu: false, enableFiltering: false, sortable: false }
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
		$scope.publicationList = [];
		$scope.publicationFlags = {
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
			publishedQuestionnaire.publishFlag = parseInt(publishedQuestionnaire.publishFlag);
			// If the "publishFlag" column has been checked
			if (publishedQuestionnaire.publishFlag) {
				publishedQuestionnaire.publishFlag = 0; // set publish to "false"
			}

			// Else the "Publish" column was unchecked
			else {
				publishedQuestionnaire.publishFlag = 1; // set publish to "true"
			}
			publishedQuestionnaire.changed = 1;
		};
		
		// Call API to get the list of questionnaires
		publicationCollectionService.getPublications(OAUserId).then(function (response) {
			$scope.publicationList = response.data;
			console.log($scope.publicationList);
		}).catch(function(response) {
			alert($filter('translate')('PUBLICATION.LIST.ERROR_PUBLICATION') + response.status + " " + response.data);
		});

		// Initialize a scope variable for a selected questionnaire
		$scope.currentPublishedQuestionnaire = {};

		// Function to submit changes when flags have been modified
		$scope.submitPublishFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.publicationList, function (publishedQuestionnaire) {
					if (publishedQuestionnaire.changed) {
						$scope.publicationFlags.flagList.push({
							ID: publishedQuestionnaire.ID,
							moduleId: publishedQuestionnaire.moduleId,
							publishFlag: publishedQuestionnaire.publishFlag
						});
					}
				});
				// Log who updated legacy questionnaire flags
				var currentUser = Session.retrieveObject('user');
				$scope.publicationFlags.OAUserId = currentUser.id;
				$scope.publicationFlags.sessionId = currentUser.sessionid;

				console.log($scope.publicationFlags);

				// Submit form

				$.ajax({
					type: "POST",
					url: "publication/update/publish-flag",
					data: $scope.publicationFlags,
					success: function (response) {
						// Call our API to get the list of existing legacy questionnaires
						publicationCollectionService.getPublications(OAUserId).then(function (response) {
							$scope.publicationList = response.data;
						}).catch(function(response) {
							alert($filter('translate')('PUBLICATION.LIST.ERROR_PUBLICATION') + response.status + " " + response.data);
						});
						response = JSON.parse(response);
						if (response.code === 200) {
							$scope.setBannerClass('success');
							$scope.bannerMessage = $filter('translate')('PUBLICATION.LIST.SUCCESS_FLAGS');
						}
						else {
							$scope.setBannerClass('danger');
							alert($filter('translate')('PUBLICATION.LIST.ERROR_FLAGS') + "\r\n\r\n" + response.status + " " + response.data);
						}
						$scope.showBanner();
						$scope.changesMade = false;
						$scope.publicationFlags.flagList = [];
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
				publicationCollectionService.getPublishedQuestionnaires(OAUserId).then(function (response) {
					$scope.publicationList = response.data;
				}).catch(function(response) {
					alert($filter('translate')('PUBLICATION.LIST.ERROR_PUBLICATION') + response.status + " " + response.data);
				});
			});
		};

	});
