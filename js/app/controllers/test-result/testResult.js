angular.module('opalAdmin.controllers.testResult', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

	/******************************************************************************
	* Test Result Page controller 
	*******************************************************************************/
	controller('testResult', function ($scope, $filter, $sce, $state, $uibModal, testResultCollectionService, educationalMaterialCollectionService, Session) {

		// Function to go to add test result page
		$scope.goToAddTestResult = function () {
			$state.go('test-result-add');
		};

		// Function to control search engine model
		$scope.filterTestResult = function (filter) {
			$scope.filterValue = filter;
			$scope.gridApi.grid.refresh();
		};

		$scope.detailView = "list";
		
		// Templates for the table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editTestResult(row.entity)"> ' +
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
		var cellTemplateGroupName = '<div class="ui-grid-cell-contents" >' +
			'{{row.entity.group_EN}} / {{row.entity.group_FR}}</div>';
		var checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updatePublishFlag(row.entity.publish)" ng-model="row.entity.publish"></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.showTestResultLog(row.entity)">Logs</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.editTestResult(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteTestResult(row.entity)">Delete</a></strong></div>';

		// Search engine for table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN', 'group_EN'].forEach(function (field) {
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


		// Table options for test results
		$scope.gridOptions = {
			data: 'testList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Name (EN/FR)', cellTemplate: cellTemplateName, width: '40%' },
				{ field: 'group_EN', displayName: 'Test Group (EN/FR)', cellTemplate: cellTemplateGroupName, width: '20%' },
				{ field: 'publish', displayName: 'Publish Flag', width: '15%', cellTemplate: checkboxCellTemplate },
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, width: '25%' }
			],
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list of existing test results
		$scope.testList = [];
		$scope.testResultPublishes = {
			publishList: []
		};

		// Initialize an object for deleting a test result
		$scope.testResultToDelete = {};

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

		$scope.changesMade = false;

		// When this function is called, we set the "publish" field to checked 
		// or unchecked based on value in the argument
		$scope.updatePublishFlag = function (value) {
			value = parseInt(value);
			if (value == 1) {
				return 1;
			} else {
				return 0;
			}
		};

		// Function for when the publish flag checkbox has been modified
		$scope.checkPublishFlag = function (testResult) {

			$scope.changesMade = true;
			testResult.publish = parseInt(testResult.publish);
			// If the "publish" column has been checked
			if (testResult.publish) {
				testResult.publish = 0; // set publish to "false"
			}

			// Else the "publish" column was unchecked
			else {
				testResult.publish = 1; // set publish to "true"
			}
			testResult.changed = 1; // flag change
		};

		// Function to submit changes when publish flags have been modified
		$scope.submitPublishFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.testList, function (testResult) {
					if (testResult.changed) {
						$scope.testResultPublishes.publishList.push({
							serial: testResult.serial,
							publish: testResult.publish
						});
					}
				});
				// Log who updated test result publish flag
				var currentUser = Session.retrieveObject('user');
				$scope.testResultPublishes.user = currentUser;
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/test-result/update.test_result_publish_flags.php",
					data: $scope.testResultPublishes,
					success: function (response) {
						// Call our API to get the list of existing test results
						testResultCollectionService.getExistingTestResults().then(function (response) {
							// Assign value
							$scope.testList = response.data;
						}).catch(function(response) {
							console.error('Error occurred getting test results:', response.status, response.data);
						});
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.bannerMessage = "Publish Flags Saved!";
						}
						else {
							$scope.setBannerClass('danger');
							$scope.bannerMessage = response.message;
						}
						$scope.showBanner();
						$scope.changesMade = false;
						$scope.testResultPublishes.publishList = [];
					}
				});
			}
		};

		$scope.switchDetailView = function (view) {
			// only switch when there's no changes that have been made
			if (!$scope.changesMade) {
				$scope.detailView = view;
			}
		}

		$scope.$watch('detailView', function (view) {
			if (view == 'list') {
				// Call our API to get the list of existing test results
				testResultCollectionService.getExistingTestResults().then(function (response) {

					$scope.testList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting test results:', response.status, response.data);
				});

				if ($scope.testResultListLogs.length) {
					$scope.testResultListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			}	
			else if (view == 'chart') {
				// Call our API to get alias logs
				testResultCollectionService.getTestResultChartLogs().then(function (response) {
					$scope.testResultChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.testResultChartLogs, function(serie) {
						angular.forEach(serie.data, function(log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function(response) {
					console.error('Error occurred getting test result logs: ', response.status, response.data);
				});
			}	
		}, true);

		var chartConfig = $scope.chartConfig = { 
		    chart: {
		        type: 'spline',
		        zoomType: 'x',
		        className: 'logChart'
		    },
		    title: {
		        text: 'All test result logs'
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
		     				testResultCollectionService.getTestResultListLogs(cronSerials).then(function(response){ 
	        					$scope.testResultListLogs = response.data;
	        				});
		        		}
		        		else {
		        			$scope.testResultListLogs = [];
	        				$scope.gridApiLog.grid.refresh();

		        		}
		        	}
		        }
		    },
		    yAxis: {
		        title: {
		            text: 'Number of test results published'
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
		        				testResultCollectionService.getTestResultListLogs(cronLogSerNum).then(function(response){ 
		        					$scope.testResultListLogs = response.data;
		        				});
		        			},
		        			unselect: function (point) {
		        				$scope.testResultListLogs = [];
		        				$scope.gridApiLog.grid.refresh();

		        			}
		        		}
		        	}
		        }
			},

		    series: []
		};

		$scope.testResultListLogs = [];
		// Table options for test result logs
		$scope.gridLogOptions = {
			data: 'testResultListLogs',
			columnDefs: [
				{ field: 'expression_name', displayName: 'Test Name' },
				{ field: 'revision', displayName: 'Revision No.' },
				{ field: 'cron_serial', displayName: 'CronLogSer' },
				{ field: 'patient_serial', displayName: 'PatientSer' },
				{ field: 'source_db', displayName: 'Database' },
				{ field: 'source_uid', displayName: 'Clinical UID' },
				{ field: 'abnormal_flag', displayName: 'Abnormal Flag' },
				{ field: 'test_date', displayName: 'Test Date' },
				{ field: 'max_norm', displayName: 'Max Norm' },
				{ field: 'min_norm', displayName: 'Min Norm' },
				{ field: 'test_value', displayName: 'Test Value' },
				{ field: 'unit', displayName: 'Unit' },
				{ field: 'valid', displayName: 'Valid' },
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


		// Initialize a scope variable for a selected test result
		$scope.currentTestResult = {};

		// Function for when the test result has been clicked for viewing logs
		$scope.showTestResultLog = function (testResult) {

			$scope.currentTestResult = testResult;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/test-result/log.test-result.html',
				controller: 'testResult.log',
				scope: $scope,
				windowClass: 'logModal',
				backdrop: 'static',
			});
		};

		// Function for when the test result has been clicked for editing
		// We open a modal
		$scope.editTestResult = function (testResult) {

			$scope.currentTestResult = testResult;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/test-result/edit.test-result.html',
				controller: 'testResult.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});
			// After update, refresh the test result list
			modalInstance.result.then(function () {
				$scope.testList = [];
				// Call our API to get the list of existing test results
				testResultCollectionService.getExistingTestResults().then(function (response) {
					$scope.testList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting test results:', response.status, response.data);
				});

			});

		};

		// Function for when the test result has been clicked for deletion
		// Open a modal
		$scope.deleteTestResult = function (currentTestResult) {

			// Assign selected test result as the item to delete 
			$scope.testResultToDelete = currentTestResult;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/test-result/delete.test-result.html',
				controller: 'testResult.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});
			// After delete, refresh the test result list
			modalInstance.result.then(function () {
				$scope.testList = [];
				// Call our API to get the list of existing test result
				testResultCollectionService.getExistingTestResults().then(function (response) {
					$scope.testList = response.data;
				}).catch(function(response) {
					console.error('Error occurred test results:', response.status, response.data);
				});

			});
		};

	});
