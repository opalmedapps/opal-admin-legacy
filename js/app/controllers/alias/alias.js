// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.alias', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


	/******************************************************************************
	 * Alias Page controller
	 *******************************************************************************/
	controller('alias', function ($scope, $uibModal, $filter, $state, aliasCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 2)) !== 0);

		// Function to go to add alias page
		$scope.goToAddAlias = function () {
			$state.go('alias-add');
		};

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
		$scope.setBannerClass = function (className) {
			/* Remove any classes starting with "alert-"*/$(".bannerMessage").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessage").addClass('alert-' + className);
		};

		var arrValidationInsert = [
			$filter('translate')('ALIAS.VALIDATION.ID'),
		];

		// Initialize a scope variable for a selected alias
		$scope.currentAlias = {};
		$scope.detailView = "list";
		$scope.changesMade = false;
		$scope.sourceDatabase = [];

		// Templates for alias table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editAlias(row.entity)">' +
			'<strong><a href="">{{row.entity.name_'+Session.retrieveObject('user').language.toUpperCase()+'}}</a></strong></div>';

		var checkboxCellTemplate;
		if($scope.writeAccess)
			checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ng-click="grid.appScope.checkAliasUpdate(row.entity)" class="ui-grid-cell-contents"><input style="margin: 4px; pointer-events: none;" type="checkbox" ng-checked="grid.appScope.updateVal(row.entity.update)" ng-model="row.entity.update"></div>';
		else
			checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents"><i ng-class="row.entity.update == 1 ? \'fa-check text-success\' : \'fa-times text-danger\'" class="fa"></i></div>';

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';
		if($scope.readAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.showAliasLog(row.entity)"><i title="' + $filter('translate')('ALIAS.LIST.LOGS') + '" class="fa fa-area-chart"></i></a></strong> ';
		if($scope.writeAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editAlias(row.entity)"><i title="' + $filter('translate')('ALIAS.LIST.EDIT') + '" class="fa fa-pencil"></i></a></strong> ';
		else
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editAlias(row.entity)"><i title="' + $filter('translate')('ALIAS.LIST.VIEW') + '" class="fa fa-eye"></i></a></strong> ';
		// if($scope.deleteAccess)
		// 	cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteAlias(row.entity)"><i title="' + $filter('translate')('ALIAS.LIST.DELETE') + '" class="fa fa-trash"></i></a></strong>';
		cellTemplateOperations += '</div>';

		var cellTemplateColor = '<div class="color-palette-sm" style="margin-top: 7px; margin-left: auto; margin-right: auto" ' +
			'ng-style="{\'background-color\': row.entity.color}"></div>';

		// Alias table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_'+Session.retrieveObject('user').language.toUpperCase(), 'type_display'].forEach(function (field) {
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

		$scope.filterAlias = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table options for aliases
		$scope.gridOptions = {
			data: 'aliasList',
			columnDefs: [
				{ field: 'name_'+Session.retrieveObject('user').language.toUpperCase(), displayName: $filter('translate')('ALIAS.LIST.TITLE_2'), cellTemplate: cellTemplateName, width: '30%', enableColumnMenu: false },
				{
					field: 'type_display', displayName: $filter('translate')('ALIAS.LIST.TYPE'), width: '10%', enableColumnMenu: false, filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: $filter('translate')('ALIAS.LIST.APPOINTMENT'), label: $filter('translate')('ALIAS.LIST.APPOINTMENT') }, { value: $filter('translate')('ALIAS.LIST.DOCUMENT'), label: $filter('translate')('ALIAS.LIST.DOCUMENT') }]
					}
				},
				{ field: 'update', displayName: $filter('translate')('ALIAS.LIST.PUBLISH'), enableColumnMenu: false, width: '5%', cellTemplate: checkboxCellTemplate, enableFiltering: false },
				{ field: 'count', type: 'number', enableColumnMenu: false, displayName: $filter('translate')('ALIAS.LIST.NUMBER'), width: '10%', enableFiltering: false },
				{
					field: 'source_db.name', enableColumnMenu: false, displayName: $filter('translate')('ALIAS.LIST.CLINICAL_DATABASE'), width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: $scope.sourceDatabase
					}
				},
				{ field: 'color', displayName: $filter('translate')('ALIAS.LIST.COLOR'), enableColumnMenu: false, width: '10%', cellTemplate: cellTemplateColor, enableFiltering: false },
				{ field: "lastupdated", displayName: $filter('translate')('ALIAS.LIST.UPDATED'), enableColumnMenu: false , width: '15%', sort: {direction: uiGridConstants.DESC, priority: 0}},
				{ name: $filter('translate')('ALIAS.LIST.OPERATIONS'), enableColumnMenu: false, cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '10%' }
			],
			//useExternalFiltering: true,
			enableColumnResizing: true,
			enableFiltering: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},

		};

		// Initialize list of existing aliases
		$scope.aliasList = [];
		$scope.aliasUpdates = {
			data: []
		};

		// Initialize an object for deleting alias
		$scope.aliasToDelete = {};

		$scope.switchDetailView = function (view) {
			// only switch when there's no changes that have been made
			if (!$scope.changesMade) {
				$scope.detailView = view;
			}
		};

		aliasCollectionService.getSourceDatabases().then(function (response) {

			response.data.forEach(function (row) {
				$scope.sourceDatabase.push({value: row.name, label: row.name});
			});
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('ALIAS.LIST.ERROR_SOURCE_DB'));
		});

		function getAliasesList() {
			// Call our API to get the list of existing aliases
			aliasCollectionService.getAliases().then(function (response) {
				response.data.forEach(function (row) {
					switch (row.type) {
					case "Appointment":
						row.type_display = $filter('translate')('ALIAS.LIST.APPOINTMENT');
						break;
					case "Document":
						row.type_display = $filter('translate')('ALIAS.LIST.DOCUMENT');
						break;
					default:
						row.type_display = $filter('translate')('ALIAS.LIST.NOT_TRANSLATED');
					}
				});
				$scope.aliasList = response.data;

			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('ALIAS.LIST.ERROR_ALIASES'));
			});
		}

		$scope.$watch('detailView', function (view) {
			if (view === 'list') {
				// Call our API to get the list of existing aliases
				getAliasesList();
				if ($scope.aliasListLogs.length) {
					$scope.aliasListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			}
			else if (view === 'chart') {
				// Call our API to get alias logs
				aliasCollectionService.getAliasChartLogs().then(function (response) {
					$scope.aliasChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.aliasChartLogs, function(series) {
						angular.forEach(series.data, function(log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function(err) {
					ErrorHandler.onError(err, $filter('translate')('ALIAS.LIST.ERROR_LOGS'));
				});
			}

		}, true);

		// When this function is called, we set the "update" field to checked
		// or unchecked based on value in the argument
		$scope.updateVal = function (value) {
			return (parseInt(value) === 1);
		};
		// Function for when the alias "Update" checkbox has been modified
		// for the selected alias in the table row
		$scope.checkAliasUpdate = function (alias) {
			$scope.changesMade = true;
			alias.update = parseInt(alias.update);
			// If the "Update" column has been checked
			if (alias.update) {
				alias.update = 0; // set update to "true"
			}

			// Else the "Update" column was unchecked
			else {
				alias.update = 1; // set update to "false"
			}
			// flag parameter that changed
			alias.changed = 1;
		};

		$scope.chartConfig = {
			chart: {
				type: 'spline',
				zoomType: 'x',
				className: 'logChart'
			},
			title: {
				text: $filter('translate')('ALIAS.LIST.ALL')
			},
			subtitle: {
				text: $filter('translate')('ALIAS.LIST.HIGHLIGHT')
			},
			xAxis: {
				type: 'datetime',
				title: {
					text: $filter('translate')('ALIAS.LIST.DATETIME_SENT')
				},
				events: {
					setExtremes: function (selection) {
						if (selection.min !== undefined && selection.max !== undefined) {
							var cronSerials = new Set();
							var allSeries = selection.target.series; // get all series
							angular.forEach(allSeries, function (series) {
								// check if series is visible (i.e. not disabled via the legend)
								if (series.visible) {
									var points = series.points;
									angular.forEach(points, function (point) {
										var timeInMilliSeconds = point.x.getTime();
										if (timeInMilliSeconds >= selection.min && timeInMilliSeconds <= selection.max) {
											if (!cronSerials.has(point.cron_serial)) {
												cronSerials.add(point.cron_serial);
											}
										}
									});
								}
							});
							// convert set to array
							cronSerials = Array.from(cronSerials);
							aliasCollectionService.getAliasListLogs(cronSerials).then(function(response){
								$scope.aliasListLogs = response.data;
							});
						}
						else {
							$scope.aliasListLogs = [];
							$scope.gridApiLog.grid.refresh();

						}
					}
				}
			},
			yAxis: {
				title: {
					text: $filter('translate')('ALIAS.LIST.CONTENT')
				},
				tickInterval: 1,
				min: 0
			},
			tooltip: {
				headerFormat: '<b>{series.name}</b><br>',
				pointFormat: '{point.x:%e. %b}: {point.y:.2f} m'
			},

			plotOptions: {
				spline: {
					marker: {
						enabled: true
					}
				},
				series: {
					allowPointSelect: true,
					point: {
						events: {
							select: function(point) {
								var cronLogSerNum = [point.target.cron_serial];
								aliasCollectionService.getAliasListLogs(cronLogSerNum).then(function(response){
									$scope.aliasListLogs = response.data;
								});
							},
							unselect: function () {
								$scope.aliasListLogs = [];
								$scope.gridApiLog.grid.refresh();

							}
						}
					}
				}
			},

			series: []
		};

		// Function to submit changes when update checkboxes have been modified
		$scope.submitUpdate = function () {

			if ($scope.changesMade && $scope.writeAccess) {
				angular.forEach($scope.aliasList, function (alias) {
					if (alias.changed) {
						$scope.aliasUpdates.data.push({
							serial: alias.serial,
							update: alias.update
						});
					}
				});

				// Submit form
				$.ajax({
					type: "POST",
					url: "alias/update/publish-flags",
					data: $scope.aliasUpdates,

					success: function () {
						$scope.setBannerClass('success');
						$scope.bannerMessage = $filter('translate')('ALIAS.LIST.SUCCESS_FLAGS');
						$scope.showBanner();
					},
					error: function (err) {
						err.responseText = JSON.parse(err.responseText);
						ErrorHandler.onError(err, $filter('translate')('ALIAS.LIST.ERROR_FLAGS'), arrValidationInsert);
					},
					complete: function () {
						getAliasesList();
						$scope.changesMade = false;
						$scope.aliasUpdates = {
							data: []
						};
					}
				});
			}
		};

		$scope.aliasListLogs = [];
		// Table options for alias logs
		$scope.gridLogOptions = {
			data: 'aliasListLogs',
			columnDefs: [
				{ field: 'expression_name', displayName: $filter('translate')('ALIAS.LIST.CODE'), enableColumnMenu: false },
				{ field: 'expression_description', displayName: $filter('translate')('ALIAS.LIST.RESOURCE'), enableColumnMenu: false },
				{ field: 'type', displayName: $filter('translate')('ALIAS.LIST.TYPE'), enableColumnMenu: false },
				{ field: 'revision', displayName: $filter('translate')('ALIAS.LIST.REVISION'), enableColumnMenu: false },
				{ field: 'cron_serial', displayName: $filter('translate')('ALIAS.LIST.CRONLOGSER'), enableColumnMenu: false },
				{ field: 'patient_serial', displayName: $filter('translate')('ALIAS.LIST.PATIENTSER'), enableColumnMenu: false },
				{ field: 'source_db', displayName: $filter('translate')('ALIAS.LIST.DATABASE'), enableColumnMenu: false },
				{ field: 'source_uid', displayName: $filter('translate')('ALIAS.LIST.UID'), enableColumnMenu: false },
				{ field: 'read_status', displayName: $filter('translate')('ALIAS.LIST.READ'), enableColumnMenu: false },
				{ field: 'date_added', displayName: $filter('translate')('ALIAS.LIST.DATETIME_SENT'), enableColumnMenu: false },
				{ field: 'mod_action', displayName: $filter('translate')('ALIAS.LIST.ACTION'), enableColumnMenu: false }
			],
			rowHeight: 30,
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApiLog = gridApi;
			},
		};


		// Function for when the alias has been clicked for viewing logs
		$scope.showAliasLog = function (alias) {

			$scope.currentAlias = alias;
			$uibModal.open({
				templateUrl: 'templates/alias/log.alias.html',
				controller: 'alias.log',
				scope: $scope,
				windowClass: 'logModal',
				backdrop: 'static',
			});
		};

		// Function for when the alias has been clicked for editing
		// We open a modal
		$scope.editAlias = function (alias) {

			$scope.currentAlias = alias;
			var modalInstance = $uibModal.open({
				// templateUrl: ($scope.writeAccess ? 'templates/alias/edit.alias.html' : 'templates/alias/view.alias.html'),
				templateUrl: 'templates/alias/edit.alias.html',
				controller: 'alias.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the alias list
			modalInstance.result.then(function () {
				getAliasesList();
			});

		};


		// Function for when the alias has been clicked for deletion
		// Open a modal
/*		$scope.deleteAlias = function (currentAlias) {
			if ($scope.deleteAccess) {
				// Assign selected alias as the alias to delete
				$scope.aliasToDelete = currentAlias;
				var modalInstance = $uibModal.open({
					templateUrl: 'templates/alias/delete.alias.html',
					controller: 'alias.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});

				// After delete, refresh the alias list
				modalInstance.result.then(function () {
					getAliasesList();
				});
			}
		};*/
	});
