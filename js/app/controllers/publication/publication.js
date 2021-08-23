angular.module('opalAdmin.controllers.publication', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('publication', function ($scope, $state, $filter, $uibModal, publicationCollectionService, Session, uiGridConstants, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.publication]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.publication]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.publication]) & (1 << 2)) !== 0);

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		$scope.goToAddPublication = function () {
			$state.go('publication-add');
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

		getPublicationsList();

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
				['name_'+Session.retrieveObject('user').language, 'module_'+Session.retrieveObject('user').language, 'type_'+Session.retrieveObject('user').language].forEach(function (field) {
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
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';
		if($scope.readAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.showPublicationLog(row.entity)"><i title="'+$filter('translate')('PUBLICATION.LIST.LOGS')+'" class="fa fa-area-chart" aria-hidden="true"></i></a></strong> ';
		if($scope.writeAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editPublication(row.entity)"<i title="'+$filter('translate')('PUBLICATION.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong></div>';
		else
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editPublication(row.entity)"<i title="'+$filter('translate')('PUBLICATION.LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editPublication(row.entity)">' +
			'<strong><a href="">{{row.entity.name_'+ Session.retrieveObject('user').language +'}}</a></strong></div>';


		var cellTemplatePublish;
		if($scope.writeAccess)
			cellTemplatePublish = '<div style="text-align: center; cursor: pointer;" ' +
				'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
				'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
				'ng-checked="grid.appScope.updatePublishFlag(row.entity.publishFlag)" ng-model="row.entity.publishFlag"></div>';
		else
			cellTemplatePublish = '<div style="text-align: center;" class="ui-grid-cell-contents">'+
				'<i ng-class="row.entity.publishFlag == 1 ? \'fa-check text-success\' : \'fa-times text-danger\'" class="fa"></i>' +
				+'</div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'publicationList',
			columnDefs: [
				{ field: 'name_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('PUBLICATION.LIST.NAME'), cellTemplate: cellTemplateName, width: '30%', sort: {direction: uiGridConstants.ASC, priority: 0} },
				{
					field: 'module_'+Session.retrieveObject('user').language, displayName: $filter('translate')('PUBLICATION.LIST.TYPE'), enableColumnMenu: false, width: '15%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: $filter('translate')('PUBLICATION.LIST.PUBLICATION'), label: $filter('translate')('PUBLICATION.LIST.PUBLICATION') }, { value: $filter('translate')('PUBLICATION.LIST.EDUCATION'), label: $filter('translate')('PUBLICATION.LIST.EDUCATION') }, { value: $filter('translate')('PUBLICATION.LIST.QUESTIONNAIRE'), label: $filter('translate')('PUBLICATION.LIST.QUESTIONNAIRE') }]
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

		$scope.publicationListLogs = [];
		// Table options for educational material logs
		$scope.gridLogOptions = {
			data: 'publicationListLogs',
			columnDefs: [
				{field: 'material_name', displayName: $filter('translate')('EDUCATION.LIST.NAME'), enableColumnMenu: false},
				{field: 'revision', displayName: $filter('translate')('EDUCATION.LIST.REVISION'), enableColumnMenu: false},
				{field: 'cron_serial', displayName: $filter('translate')('EDUCATION.LIST.CRONLOGSER'), enableColumnMenu: false},
				{field: 'patient_serial', displayName: $filter('translate')('EDUCATION.LIST.PATIENTSER'), enableColumnMenu: false},
				{field: 'read_status', displayName: $filter('translate')('EDUCATION.LIST.READ_STATUS'), enableColumnMenu: false},
				{field: 'date_added', displayName: $filter('translate')('EDUCATION.LIST.PATIENTSER'), enableColumnMenu: false},
				{field: 'mod_action', displayName: $filter('translate')('EDUCATION.LIST.ACTION'), enableColumnMenu: false}
			],
			rowHeight: 30,
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApiLog = gridApi;
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

				// Submit form
				$.ajax({
					type: "POST",
					url: "publication/update/publish-flag",
					data: $scope.publicationFlags,
					success: function (response) {
						$scope.setBannerClass('success');
						$scope.bannerMessage = $filter('translate')('PUBLICATION.LIST.SUCCESS_FLAGS');
						$scope.showBanner();
						getPublicationsList();
						$scope.changesMade = false;
						$scope.publicationFlags.flagList = [];
					},
					error: function (err) {
						ErrorHandler.onError(err, $filter('translate')('PUBLICATION.LIST.ERROR_FLAGS'));
					}
				});
			}
		};

		function getPublicationsList() {
			publicationCollectionService.getPublications(OAUserId).then(function (response) {
				$scope.publicationList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('PUBLICATION.LIST.ERROR_PUBLICATION'));
			});
		}

		// Function to edit questionnaire
		$scope.editPublication = function (publication) {
			$scope.currentPublication = publication;
			var modalInstance = $uibModal.open({ // open modal
				templateUrl: ($scope.writeAccess ? 'templates/publication/edit.publication.html' : 'templates/publication/view.publication.html'),
				controller: 'publication.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				getPublicationsList();
			});
		};

		// Function for when the post has been clicked for viewing logs
		$scope.showPublicationLog = function (publication) {
			$scope.currentPublication = publication;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/publication/log.publication.html',
				controller: 'publication.log',
				scope: $scope,
				windowClass: 'logModal',
				backdrop: 'static',
			});
		};
	});
