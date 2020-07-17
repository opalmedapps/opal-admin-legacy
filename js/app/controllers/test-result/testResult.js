angular.module('opalAdmin.controllers.testResult', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

/******************************************************************************
 * Test Result Page controller
 *******************************************************************************/
controller('testResult', function ($scope, $filter, $sce, $state, $uibModal, testResultCollectionService, Session, ErrorHandler, MODULE) {
	$scope.navMenu = Session.retrieveObject('menu');
	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.test_results]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.test_results]) & (1 << 1)) !== 0);
	$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.test_results]) & (1 << 2)) !== 0);

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
		'<strong><a href="">{{row.entity.name_'+ Session.retrieveObject('user').language.toUpperCase() +'}}</a></strong></div>';
	var cellTemplateGroupName = '<div class="ui-grid-cell-contents" >' +
		'{{row.entity.group_' + Session.retrieveObject('user').language.toUpperCase() + '}}</div>';

	var checkboxCellTemplate;
	if($scope.writeAccess) {
		checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updatePublishFlag(row.entity.publish)" ng-model="row.entity.publish"></div>';
	} else {
		checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents">'+
			'<i ng-class="row.entity.publish == 1 ? \'fa-check text-success\' : \'fa-times text-danger\'" class="fa"></i>' +
			+'</div>';
	}

	var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

	if($scope.readAccess)
		cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.showTestResultLog(row.entity)"><i title="'+$filter('translate')('TEST.LIST.LOGS')+'" class="fa fa-area-chart" ></i></a></strong> ';
	if($scope.writeAccess)
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editTestResult(row.entity)"><i title="'+$filter('translate')('TEST.LIST.EDIT')+'" class="fa fa-pencil" ></i></a></strong> ';
	else
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editTestResult(row.entity)"><i title="'+$filter('translate')('TEST.LIST.VIEW')+'" class="fa fa-eye" ></i></a></strong> ';
	if($scope.deleteAccess)
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteTestResult(row.entity)"><i title="'+$filter('translate')('TEST.LIST.DELETE')+'" class="fa fa-trash" ></i></a></strong>';

	cellTemplateOperations += '</div>';

	// Search engine for table
	$scope.filterOptions = function (renderableRows) {
		var matcher = new RegExp($scope.filterValue, 'i');
		renderableRows.forEach(function (row) {
			var match = false;
			['name_'+Session.retrieveObject('user').language.toUpperCase(), 'group_'+Session.retrieveObject('user').language.toUpperCase()].forEach(function (field) {
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
			{ field: 'name_'+Session.retrieveObject('user').language.toUpperCase(), displayName: $filter('translate')('TEST.LIST.NAME'), cellTemplate: cellTemplateName, enableColumnMenu: false },
			{ field: 'group_'+Session.retrieveObject('user').language.toUpperCase(), displayName: $filter('translate')('TEST.LIST.TEST_GROUP'), cellTemplate: cellTemplateGroupName, width: '15%', enableColumnMenu: false },
			{ field: 'publish', displayName: $filter('translate')('TEST.LIST.PUBLISH'), width: '15%', cellTemplate: checkboxCellTemplate, enableColumnMenu: false },
			{ name: $filter('translate')('TEST.LIST.OPERATIONS'), cellTemplate: cellTemplateOperations, sortable: false, width: '15%', enableColumnMenu: false }
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
		return (parseInt(value) === 1);
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
				url: "test-result/update/test-result-publish-flags",
				data: $scope.testResultPublishes,
				success: function (response) {
					getTestResults();
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
				},
				error: function (err) {
					ErrorHandler.onError(err, $filter('translate')('TEST.LIST.ERROR_FLAGS'));
				}
			});
		}
	};

	$scope.switchDetailView = function (view) {
		// only switch when there's no changes that have been made
		if (!$scope.changesMade) {
			$scope.detailView = view;
		}
	};

	$scope.$watch('detailView', function (view) {
		if (view === 'list') {
			getTestResults();

			if ($scope.testResultListLogs.length) {
				$scope.testResultListLogs = [];
				$scope.gridApiLog.grid.refresh();
			}
		}
		else if (view === 'chart') {
			// Call our API to get alias logs
			testResultCollectionService.getTestResultChartLogs().then(function (response) {
				$scope.testResultChartLogs = $scope.chartConfig.series = response.data;
				angular.forEach($scope.testResultChartLogs, function(serie) {
					angular.forEach(serie.data, function(log) {
						log.x = new Date(log.x);
					});
				});
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('TEST.LIST.ERROR_LOGS'));
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
			text: $filter('translate')('TEST.LIST.ALL_LOGS')
		},
		subtitle: {
			text: $filter('translate')('TEST.LIST.HIGHLIGHT')
		},
		xAxis: {
			type: 'datetime',
			title: {
				text: $filter('translate')('TEST.LIST.DATETIME')
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
				text: $filter('translate')('TEST.LIST.NUMBER')
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
			{ field: 'expression_name', displayName: $filter('translate')('TEST.LIST.TEST_NAME'), enableColumnMenu: false },
			{ field: 'revision', displayName: $filter('translate')('TEST.LIST.REVISION_NO'), enableColumnMenu: false },
			{ field: 'cron_serial', displayName: $filter('translate')('TEST.LIST.CRONLOGSER'), enableColumnMenu: false },
			{ field: 'patient_serial', displayName: $filter('translate')('TEST.LIST.PATIENTSER'), enableColumnMenu: false },
			{ field: 'source_db', displayName: $filter('translate')('TEST.LIST.DATABASE'), enableColumnMenu: false },
			{ field: 'source_uid', displayName: $filter('translate')('TEST.LIST.CLINICAL_UID'), enableColumnMenu: false },
			{ field: 'abnormal_flag', displayName: $filter('translate')('TEST.LIST.ABNORMAL_FLAG'), enableColumnMenu: false },
			{ field: 'test_date', displayName: $filter('translate')('TEST.LIST.TEST_DATE'), enableColumnMenu: false },
			{ field: 'max_norm', displayName: $filter('translate')('TEST.LIST.MAX_NORM'), enableColumnMenu: false },
			{ field: 'min_norm', displayName: $filter('translate')('TEST.LIST.MIN_NORM'), enableColumnMenu: false },
			{ field: 'test_value', displayName: $filter('translate')('TEST.LIST.TEST_VALUE'), enableColumnMenu: false },
			{ field: 'unit', displayName: $filter('translate')('TEST.LIST.UNIT'), enableColumnMenu: false },
			{ field: 'valid', displayName: $filter('translate')('TEST.LIST.VALID'), enableColumnMenu: false },
			{ field: 'read_status', displayName: $filter('translate')('TEST.LIST.READ_STATUS'), enableColumnMenu: false },
			{ field: 'date_added', displayName: $filter('translate')('TEST.LIST.DATETIME'), enableColumnMenu: false },
			{ field: 'mod_action', displayName: $filter('translate')('TEST.LIST.ACTION'), enableColumnMenu: false }
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
			templateUrl: ($scope.writeAccess ? 'templates/test-result/edit.test-result.html' : 'templates/test-result/view.test-result.html'),
			controller: 'testResult.edit',
			scope: $scope,
			windowClass: 'customModal',
			backdrop: 'static',
		});
		// After update, refresh the test result list
		modalInstance.result.then(function () {
			$scope.testList = [];
			// Call our API to get the list of existing test results
			getTestResults();
		});

	};

	function getTestResults() {
		testResultCollectionService.getExistingTestResults().then(function (response) {
			$scope.testList = response.data;
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('TEST.LIST.ERROR_LIST'));
		});
	}

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
			getTestResults();
		});
	};

});
