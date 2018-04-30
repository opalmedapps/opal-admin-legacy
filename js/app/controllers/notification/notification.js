angular.module('opalAdmin.controllers.notification', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Controller for the notification page
	*******************************************************************************/
	controller('notification', function ($scope, $uibModal, $filter, $state, $sce, notificationCollectionService, Session) {

		// Function to go to add notification page
		$scope.goToAddNotification = function () {
			$state.go('notification-add');
		};

		// Function to control search engine model
		$scope.filterNotification = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		$scope.detailView = "list";

		// Templates for the table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editNotification(row.entity)">' +
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.showNotificationLog(row.entity)">Logs</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.editNotification(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteNotification(row.entity)">Delete</a></strong></div>';

		// Search engine for table
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

		// Table options for notifications
		$scope.gridOptions = {
			data: 'notificationList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '40%' },
				{ field: 'type', displayName: 'Type', width: '15%' },
				{ field: 'description_EN', displayName: 'Message (EN)', width: '30%' },
				{ name: 'Operations', width: '15%', cellTemplate: cellTemplateOperations, sortable: false }
			],
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list of existing notifications
		$scope.notificationList = [];

		// Initialize an object for delete a notification
		$scope.notificationToDelete = {};

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
				// Call our API to get the list of existing notifications
				notificationCollectionService.getNotifications().then(function (response) {
					$scope.notificationList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting notifications:', response.status, response.data);
				});
				if ($scope.notificationListLogs.length) {
					$scope.notificationListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			}	
			else if (view == 'chart') {
				// Call our API to get notification logs
				notificationCollectionService.getNotificationChartLogs().then(function (response) {
					$scope.notificationChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.notificationChartLogs, function(serie) {
						angular.forEach(serie.data, function(log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function(response) {
					console.error('Error occurred getting notification logs:', response.status, response.data);
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
		        text: 'All notification logs'
		    },
		    subtitle: {
		        text: 'Highlight the plot area to zoom in and show detailed data'
		    },
		    xAxis: {
		        type: 'datetime',
		        // dateTimeLabelFormats: { // don't display the dummy year
		        //     month: '%e. %b %H:%M',
		        //     year: '%b'
		        // },
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
		     				notificationCollectionService.getNotificationListLogs(cronSerials).then(function(response){ 
	        					$scope.notificationListLogs = response.data;
	        				});
		        		}
		        		else {
		        			$scope.notificationListLogs = [];
	        				$scope.gridApiLog.grid.refresh();

		        		}
		        	}
		        }
		    },
		    yAxis: {
		        title: {
		            text: 'Number of notifications sent'
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
		        				notificationCollectionService.getNotificationListLogs(cronLogSerNum).then(function(response){ 
		        					$scope.notificationListLogs = response.data;
		        				});
		        			},
		        			unselect: function (point) {
		        				$scope.notificationListLogs = [];
		        				$scope.gridApiLog.grid.refresh();

		        			}
		        		}
		        	}
		        }
			},

		    series: []
		};

		$scope.notificationListLogs = [];
		// Table options for notification logs
		$scope.gridLogOptions = {
			data: 'notificationListLogs',
			columnDefs: [
				{ field: 'control_serial', displayName: 'ControlSer' },
				{ field: 'revision', displayName: 'Revision No.' },
				{ field: 'cron_serial', displayName: 'CronLogSer' },
				{ field: 'patient_serial', displayName: 'PatientSer' },
				{ field: 'type', displayName: 'Notification Type' },
				{ field: 'ref_table_serial', displayName: 'Ref Table Ser' },
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

		// Initialize a scope variable for a selected notification
		$scope.currentNotification = {};

		// Function for when the notification has been clicked for viewing logs
		$scope.showNotificationLog = function (notification) {

			$scope.currentNotification = notification;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/notification/log.notification.html',
				controller: 'notification.log',
				scope: $scope,
				windowClass: 'logModal',
				backdrop: 'static',
			});
		};

		// Function for when the notification has been clicked for editing
		$scope.editNotification = function (notification) {

			$scope.currentNotification = notification;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/notification/edit.notification.html',
				controller: 'notification.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the notification list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing notifications
				notificationCollectionService.getNotifications().then(function (response) {

					// Assign the retrieved response
					$scope.notificationList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting notifications:', response.status, response.data);
				});
			});
		};

		// Function for when the notification has been clicked for deletion
		// Open a modal
		$scope.deleteNotification = function (currentNotification) {

			// Assign selected notification as the item to delete
			$scope.notificationToDelete = currentNotification;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/notification/delete.notification.html',
				controller: 'notification.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the map list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing notifications
				notificationCollectionService.getNotifications().then(function (response) {
					// Assign the retrieved response
					$scope.notificationList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting notifications:', response.status, response.data);
				});
			});
		};

	});
