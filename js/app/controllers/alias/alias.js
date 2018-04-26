angular.module('opalAdmin.controllers.alias', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


	/******************************************************************************
	* Alias Page controller 
	*******************************************************************************/
	controller('alias', function ($scope, $uibModal, $filter, aliasCollectionService, educationalMaterialCollectionService, uiGridConstants, $state, Session) {

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
			// Remove any classes starting with "alert-" 
			$(".bannerMessage").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessage").addClass('alert-' + className);
		};

		// Initialize a scope variable for a selected alias
		$scope.currentAlias = {};

		$scope.detailView = "list";

		$scope.changesMade = false;

		// Templates for alias table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editAlias(row.entity)">' +
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
		var checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkAliasUpdate(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updateVal(row.entity.update)" ng-model="row.entity.update"></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.showAliasLog(row.entity)">Logs</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.editAlias(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteAlias(row.entity)">Delete</a></strong></div>';
		var cellTemplateColor = '<div class="color-palette-sm" style="margin-top: 7px; margin-left: auto; margin-right: auto" ' +
			'ng-style="{\'background-color\': row.entity.color}"></div>';

		// Alias table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN', 'type'].forEach(function (field) {
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
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '30%' },
				{
					field: 'type', displayName: 'Type', width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: 'Appointment', label: 'Appointment' }, { value: 'Document', label: 'Document' }, { value: 'Task', label: 'Task' }]
					}
				},
				{ field: 'update', displayName: 'Publish Flag', width: '5%', cellTemplate: checkboxCellTemplate, enableFiltering: false },
				{ field: 'count', type: 'number', displayName: '# of assigned codes', width: '5%', enableFiltering: false },
				{
					field: 'source_db.name', displayName: 'Clinical Database', width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: 'Aria', label: 'Aria' }, { value: 'MediVisit', label: 'MediVisit' }]
					}
				},
				{ field: 'color', displayName: 'Color Tag', width: '10%', cellTemplate: cellTemplateColor, enableFiltering: false },
				{ field: "lastupdated", displayName: 'Last Updated', width: '15%', sort: {direction: uiGridConstants.DESC, priority: 0}},
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '15%' }
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
			updateList: []
		};

		// Initialize an object for deleting alias
		$scope.aliasToDelete = {};

		$scope.switchDetailView = function (view) {
			// only switch when there's no changes that have been made
			if (!$scope.changesMade) {
				$scope.detailView = view;
			}
		}

		$scope.$watch('detailView', function (view) {
			if (view == 'list') {
				// Call our API to get the list of existing aliases
				aliasCollectionService.getAliases().then(function (response) {
					// Assign value
					$scope.aliasList = response.data;

				}).catch(function(response) {
					console.error('Error occurred getting alias list:', response.status, response.data);
				});
				if ($scope.aliasListLogs.length) {
					$scope.aliasListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			}
			else if (view == 'chart') {
				// Call our API to get alias logs
				aliasCollectionService.getAliasChartLogs().then(function (response) {
					$scope.aliasChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.aliasChartLogs, function(serie) {
						angular.forEach(serie.data, function(log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function(response) {
					console.error('Error occurred getting alias logs:', response.status, response.data);
				});
			}

		}, true);
		
		// When this function is called, we set the "update" field to checked 
		// or unchecked based on value in the argument
		$scope.updateVal = function (value) {
			value = parseInt(value);
			if (value == 1) {
				return 1;
			} else {
				return 0;
			}
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

		var chartConfig = $scope.chartConfig = { 
		    chart: {
		        type: 'spline',
		        zoomType: 'x',
		        className: 'logChart'
		    },
		    title: {
		        text: 'All appointment/task/document logs'
		    },
		    subtitle: {
		        text: 'Highlight the plot area to zoom in and show detailed data'
		    },
		    xAxis: {
		        type: 'datetime',
		        title: {
		            text: 'Datetime sent'
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
		     							timeInMilliSeconds = point.x.getTime();
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
		            text: 'Number of content published'
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
		        			unselect: function (point) {
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

			if ($scope.changesMade) {
				angular.forEach($scope.aliasList, function (alias) {
					if (alias.changed) {
						$scope.aliasUpdates.updateList.push({
							serial: alias.serial,
							update: alias.update
						});
					}
				});
				// Log who updated alias
				var currentUser = Session.retrieveObject('user');
				$scope.aliasUpdates.user = currentUser;
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/alias/update.alias_publish_flags.php",
					data: $scope.aliasUpdates,
					success: function (response) {
						// Call our API to get the list of existing aliases
						aliasCollectionService.getAliases().then(function (response) {
							// Assign value
							$scope.aliasList = response.data;

						}).catch(function(response) {
							console.error('Error occurred getting alias list:', response.status, response.data);
						});
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.bannerMessage = "Updates Saved!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.bannerMessage = response.message;
						}

						$scope.showBanner();
						$scope.changesMade = false;
						$scope.aliasUpdates.updateList = [];
					}
				});
			}
		};

		$scope.aliasListLogs = [];
		// Table options for alias logs
		$scope.gridLogOptions = {
			data: 'aliasListLogs',
			columnDefs: [
				{ field: 'expression_name', displayName: 'Clinical Code' },
				{ field: 'expression_description', displayName: 'Resource Description'},
				{ field: 'type', displayName: 'Type' },
				{ field: 'revision', displayName: 'Revision No.' },
				{ field: 'cron_serial', displayName: 'CronLogSer' },
				{ field: 'patient_serial', displayName: 'PatientSer' },
				{ field: 'source_db', displayName: 'Database' },
				{ field: 'source_uid', displayName: 'Clinical UID' },
				{ field: 'read_status', displayName: 'Read Status' },
				{ field: 'date_added', displayName: 'Datetime Sent' },
				{ field: 'mod_action', displayName: 'Action' }
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
			var modalInstance = $uibModal.open({
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
				templateUrl: 'templates/alias/edit.alias.html',
				controller: 'alias.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the alias list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing aliases
				aliasCollectionService.getAliases().then(function (response) {

					// Assign the retrieved response
					$scope.aliasList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting alias list:', response.status, response.data);
				});
			});

		};


		// Function for when the alias has been clicked for deletion
		// Open a modal
		$scope.deleteAlias = function (currentAlias) {

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
				// Call our API to get the list of existing aliases
				aliasCollectionService.getAliases().then(function (response) {

					// Assign the retrieved response
					$scope.aliasList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting alias list:', response.status, response.data);
				});
			});

		};


	});

