angular.module('opalAdmin.controllers.email', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Controller for the email page
	*******************************************************************************/
	controller('email', function ($scope, $uibModal, $filter, $state, $sce, emailCollectionService, Session) {

		// Function to go to add email page
		$scope.goToAddEmail = function () {
			$state.go('email-add');
		};

		// Function to control search engine model
		$scope.filterEmail = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		$scope.detailView = "list";

		// Templates for the table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editEmail(row.entity)">' +
			'<strong><a href="">{{row.entity.subject_EN}} / {{row.entity.subject_FR}}</a></strong></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.showEmailLog(row.entity)">Logs</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.editEmail(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteEmail(row.entity)">Delete</a></strong></div>';

		// Search engine for table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['subject_EN'].forEach(function (field) {
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

		// Table options for email list
		$scope.gridOptions = {
			data: 'emailList',
			columnDefs: [
				{ field: 'subject_EN', displayName: 'Title (EN)', cellTemplate: cellTemplateName, width: '40%' },
				{ field: 'type', displayName: 'Type', width: '45%' },
				{ name: 'Operations', width: '15%', cellTemplate: cellTemplateOperations, sortable: false }
			],
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list of existing emails
		$scope.emailList = [];

		// Initialize an object for delete an email
		$scope.emailToDelete = {};

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

		$scope.switchDetailView = function (view) {
			// only switch when there's no changes that have been made
			if (!$scope.changesMade) {
				$scope.detailView = view;
			}
		}

		$scope.$watch('detailView', function (view) {
			if (view == 'list') {
				// Call our API to get the list of existing emails
				emailCollectionService.getEmails().then(function (response) {
					$scope.emailList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting email list:', response.status, response.data);
				});
				if ($scope.emailListLogs.length) {
					$scope.emailListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			}	
			else if (view == 'chart') {
				// Call our API to get email logs
				emailCollectionService.getEmailChartLogs().then(function (response) {
					$scope.emailChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.emailChartLogs, function(serie) {
						angular.forEach(serie.data, function(log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function(response) {
					console.error('Error occurred getting email logs:', response.status, response.data);
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
		        text: 'All email logs'
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
		     				emailCollectionService.getEmailListLogs(cronSerials).then(function(response){ 
	        					$scope.emailListLogs = response.data;
	        				});
		        		}
		        		else {
		        			$scope.emailListLogs = [];
	        				$scope.gridApiLog.grid.refresh();

		        		}
		        	}
		        }
		    },
		    yAxis: {
		        title: {
		            text: 'Number of emails sent'
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
		        				emailCollectionService.getEmailListLogs(cronLogSerNum).then(function(response){ 
		        					$scope.emailListLogs = response.data;
		        				});
		        			},
		        			unselect: function (point) {
		        				$scope.emailListLogs = [];
		        				$scope.gridApiLog.grid.refresh();

		        			}
		        		}
		        	}
		        }
			},

		    series: []
		};

		$scope.emailListLogs = [];
		// Table options for alias logs
		$scope.gridLogOptions = {
			data: 'emailListLogs',
			columnDefs: [
				{ field: 'control_serial', displayName: 'ControlSer' },
				{ field: 'revision', displayName: 'Revision No.' },
				{ field: 'cron_serial', displayName: 'CronLogSer' },
				{ field: 'patient_serial', displayName: 'PatientSer' },
				{ field: 'type', displayName: 'Email Type' },
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

		// Initialize a scope variable for a selected email
		$scope.currentEmail = {};

		// Function for when the email has been clicked for viewing logs
		$scope.showEmailLog = function (email) {

			$scope.currentEmail = email;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/email/log.email.html',
				controller: 'email.log',
				scope: $scope,
				windowClass: 'logModal',
				backdrop: 'static',
			});
		};

		// Function for when the email has been clicked for editing
		$scope.editEmail = function (email) {

			$scope.currentEmail = email;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/email/edit.email.html',
				controller: 'email.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the email list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing emails
				emailCollectionService.getEmails().then(function (response) {

					// Assign the retrieved response
					$scope.emailList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting email list:', response.status, response.data);
				});
			});
		};

		// Function for when the email has been clicked for deletion
		// Open a modal
		$scope.deleteEmail = function (currentEmail) {

			// Assign selected email as the item to delete
			$scope.emailToDelete = currentEmail;

			var modalInstance = $uibModal.open({
				templateUrl: 'templates/email/delete.email.html',
				controller: 'email.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the map list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing emails
				emailCollectionService.getEmails().then(function (response) {
					// Assign the retrieved response
					$scope.emailList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting email list:', response.status, response.data);
				});
			});
		};

	});
