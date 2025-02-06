// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.alert', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('alert', function ($scope, $state, $filter, $uibModal, alertCollectionService, Session, uiGridConstants, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alert]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alert]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alert]) & (1 << 2)) !== 0);

		// get current user id
		var user = Session.retrieveObject('user');1
		var OAUserId = user.id;

		$scope.goToAddAlert = function () {
			$state.go('alert-add');
		};

		$scope.activationFlags = {
			flagList: []
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

		// Function to filter custom codes
		$scope.filterAlert = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		getAlertsList();

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
				['subject', 'creationDate', 'lastUpdated'].forEach(function (field) {
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

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editAlert(row.entity)"<i title="'+$filter('translate')('ALERT.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong>';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editAlert(row.entity)"<i title="'+$filter('translate')('ALERT.LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong>';
		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteAlert(row.entity)"><i title="'+$filter('translate')('ALERT.LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong>';
		cellTemplateOperations += '</div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editAlert(row.entity)">' +
			'<strong><a href="">{{row.entity.subject}}</a></strong></div>';

		if($scope.writeAccess)
			cellTemplatePublish = '<div style="text-align: center; cursor: pointer;" ' +
				'ng-click="grid.appScope.checkActiveFlag(row.entity)" ' +
				'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
				'ng-checked="grid.appScope.updateActiveFlag(row.entity.active)" ng-model="row.entity.active"></div>';
		else
			cellTemplatePublish = '<div style="text-align: center;" class="ui-grid-cell-contents">'+
				'<i ng-class="row.entity.active == 1 ? \'fa-check text-success\' : \'fa-times text-danger\'" class="fa"></i>' +
				+'</div>';
		
		// Data binding for main table
		$scope.gridOptions = {
			data: 'alertsList',
			columnDefs: [
				{ field: 'subject', enableColumnMenu: false, displayName: $filter('translate')('ALERT.LIST.SUBJECT'), cellTemplate: cellTemplateName, sort: {direction: uiGridConstants.ASC, priority: 0}},
				{
					field: 'active', displayName: $filter('translate')('ALERT.LIST.ACTIVE'), enableColumnMenu: false, cellTemplate: cellTemplatePublish, width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: '1', label: $filter('translate')('ALERT.LIST.YES') }, { value: '0', label: $filter('translate')('ALERT.LIST.NO') }]
					}
				},
				{ field: 'creationDate', enableColumnMenu: false, displayName: $filter('translate')('ALERT.LIST.CREATION_DATE'), width: '20%'},
				{ field: 'lastUpdated', enableColumnMenu: false, displayName: $filter('translate')('ALERT.LIST.LAST_UPDATED'), width: '20%'},
				{ name: $filter('translate')('ALERT.LIST.OPERATIONS'), width: '10%', cellTemplate: cellTemplateOperations, enableColumnMenu: false, enableFiltering: false, sortable: false }
			],
			enableFiltering: true,
			enableSorting: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		$scope.updateActiveFlag = function (value) {
			value = parseInt(value);
			if (value == 1) {
				return 1;
			} else {
				return 0;
			}
		};

		$scope.checkActiveFlag = function (activeAlert) {
			$scope.changesMade = true;
			activeAlert.active = parseInt(activeAlert.active);
			// If the "active" column has been checked
			if (activeAlert.active) {
				activeAlert.active = 0; // set publish to "false"
			}

			// Else the "Publish" column was unchecked
			else {
				activeAlert.active = 1; // set publish to "true"
			}
			activeAlert.changed = 1;
		};

		$scope.submitActiveFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.alertsList, function (activeAlert) {
					if (activeAlert.changed) {
						$scope.activationFlags.flagList.push({
							ID: activeAlert.ID,
							active: activeAlert.active
						});
					}
				});

				// Submit form
				$.ajax({
					type: "POST",
					url: "alert/update/activation-flag",
					data: $scope.activationFlags,
					success: function (response) {
						$scope.setBannerClass('success');
						$scope.bannerMessage = $filter('translate')('ALERT.LIST.SUCCESS_FLAGS');
						$scope.showBanner();
					},
					error: function (err) {
						ErrorHandler.onError(err, $filter('translate')('ALERT.LIST.ERROR_FLAGS'));
					},
					complete: function () {
						getAlertsList();
						$scope.changesMade = false;
						$scope.activationFlags.flagList = [];
					}
				});
			}
		};
		
		// Initialize object for storing questionnaires
		$scope.alertsList = [];

		function getAlertsList() {
			alertCollectionService.getAlerts().then(function (response) {
				$scope.alertsList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('ALERT.LIST.ERROR_ALERT'));
			});
		}

		// Function to edit questionnaire
		$scope.editAlert = function (alert) {
			$scope.currentAlert = alert;
			var modalInstance = $uibModal.open({ // open modal
				templateUrl: ($scope.writeAccess ? 'templates/alert/edit.alert.html' : 'templates/alert/view.alert.html'),
				controller: 'alert.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				getAlertsList();
			});
		};

		// Function for when the custom code has been clicked for deletion
		// Open a modal
		$scope.deleteAlert = function (currentAlert) {
			// Assign selected custom code as the custom code to delete
			$scope.alertToDelete = currentAlert;

			var modalInstance = $uibModal.open({
				templateUrl: 'templates/alert/delete.alert.html',
				controller: 'alert.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the custom code list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing posts
				getAlertsList();
			});
		};
	});

