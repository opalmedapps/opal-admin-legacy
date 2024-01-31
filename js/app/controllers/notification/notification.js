angular.module('opalAdmin.controllers.notification', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).


/******************************************************************************
 * Controller for the notification page
 *******************************************************************************/
controller('notification', function ($scope, $uibModal, $filter, $state, notificationCollectionService, Session, ErrorHandler, MODULE) {
	$scope.navMenu = Session.retrieveObject('menu');
	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.notification]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.notification]) & (1 << 1)) !== 0);
	$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.notification]) & (1 << 2)) !== 0);
	
	// Function to control search engine model
	$scope.filterNotification = function (filterValue) {
		$scope.filterValue = filterValue;
		$scope.gridApi.grid.refresh();

	};

	$scope.detailView = "list";

	// Templates for the table
	var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
		'ng-click="grid.appScope.editNotification(row.entity)">' +
		'<strong><a href="">{{row.entity.name_'+Session.retrieveObject('user').language.toUpperCase()+'}}</a></strong></div>';

	var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

	if($scope.readAccess)
		cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.showNotificationLog(row.entity)"><i title="' + $filter('translate')('NOTIFICATIONS.LIST.LOGS') + '" class="fa fa-area-chart" aria-hidden="true"></i></a></strong> ';

	if($scope.writeAccess)
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editNotification(row.entity)"><i title="' + $filter('translate')('NOTIFICATIONS.LIST.EDIT') + '" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ';
	else
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editNotification(row.entity)"><i title="' + $filter('translate')('NOTIFICATIONS.LIST.VIEW') + '" class="fa fa-eye" aria-hidden="true"></i></a></strong> ';

	if($scope.deleteAccess)
		cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteNotification(row.entity)"><i title="' + $filter('translate')('NOTIFICATIONS.LIST.DELETE') + '" class="fa fa-trash" aria-hidden="true"></i></a></strong>';

	cellTemplateOperations += '</div>';

	// Search engine for table
	$scope.filterOptions = function (renderableRows) {
		var matcher = new RegExp($scope.filterValue, 'i');
		renderableRows.forEach(function (row) {
			var match = false;
			['name_'+Session.retrieveObject('user').language.toUpperCase(), 'description_'+Session.retrieveObject('user').language.toUpperCase(), 'type'].forEach(function (field) {
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
			{ field: 'name_'+Session.retrieveObject('user').language.toUpperCase(), displayName: $filter('translate')('NOTIFICATIONS.LIST.TITLE_2'), cellTemplate: cellTemplateName, width: '40%', enableColumnMenu: false },
			{ field: 'type', displayName: $filter('translate')('NOTIFICATIONS.LIST.TYPE'), width: '15%', enableColumnMenu: false },
			{ field: 'description_'+Session.retrieveObject('user').language.toUpperCase(), displayName: $filter('translate')('NOTIFICATIONS.LIST.MESSAGE'), width: '30%', enableColumnMenu: false },
			{ name: $filter('translate')('NOTIFICATIONS.LIST.OPERATIONS'), width: '15%', cellTemplate: cellTemplateOperations, sortable: false, enableColumnMenu: false }
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
	};

	function getNotificationsList() {
		notificationCollectionService.getNotifications().then(function (response) {
			response.data.forEach(function(entry) {
				switch (entry.type) {
				case "Document":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.DOCUMENT');
					break;
				case "TxTeamMessage":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.TREATMENT_TEAM');
					break;
				case "Announcement":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.ANNOUNCEMENT');
					break;
				case "EducationalMaterial":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.EDUCATION_MATERIAL');
					break;
				case "NextAppointment":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.NEXT_APPOINTMENT');
					break;
				case "AppointmentTimeChange":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.APPOINTMENT_TIME_CHANGE');
					break;
				case "NewMessage":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.NEW_MESSAGE');
					break;
				case "NewLabResult":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.NEW_LAB_RESULT');
					break;
				case "UpdDocument":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.UPDATED_DOCUMENT');
					break;
				case "RoomAssignment":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.ROOM_ASSIGNMENT');
					break;
				case "PatientsForPatients":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.PATIENTS_FOR_PATIENTS');
					break;
				case "Questionnaire":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.QUESTIONNAIRE');
					break;
				case "LegacyQuestionnaire":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.LEGACY_QUESTIONNAIRE');
					break;
				case "CheckInNotification":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.CHECKIN');
					break;
				case "CheckInError":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.CHECKINERROR');
					break;
				case "AppointmentCancelled":
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.CANCELLED');
					break;
				default:
					entry.type = $filter('translate')('NOTIFICATIONS.ADD.NOT_TRANSLATED');
				}
			});


			// Assign the retrieved response
			$scope.notificationList = response.data;
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('NOTIFICATIONS.LIST.ERROR_LIST'));
		});
	}

	$scope.$watch('detailView', function (view) {
		if (view == 'list') {
			getNotificationsList();
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
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('NOTIFICATIONS.LIST.ERROR_LOGS'));
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
			text: $filter('translate')('NOTIFICATIONS.LIST.ALL_LOGS')
		},
		subtitle: {
			text: $filter('translate')('NOTIFICATIONS.LIST.HIGHLIGHT')
		},
		xAxis: {
			type: 'datetime',
			// dateTimeLabelFormats: { // don't display the dummy year
			//     month: '%e. %b %H:%M',
			//     year: '%b'
			// },
			title: {
				text: $filter('translate')('NOTIFICATIONS.LIST.DATETIME_SENT')
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
				text: $filter('translate')('NOTIFICATIONS.LIST.NOTIFICATIONS')
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
			{ field: 'control_serial', displayName: $filter('translate')('NOTIFICATIONS.LIST.CONTROLSER') },
			{ field: 'revision', displayName: $filter('translate')('NOTIFICATIONS.LIST.REVISION') },
			{ field: 'cron_serial', displayName: $filter('translate')('NOTIFICATIONS.LIST.CRONLOGSER') },
			{ field: 'patient_serial', displayName: $filter('translate')('NOTIFICATIONS.LIST.PATIENTSER') },
			{ field: 'type', displayName: $filter('translate')('NOTIFICATIONS.LIST.NOTIFICATION_TYPE') },
			{ field: 'ref_table_serial', displayName: $filter('translate')('NOTIFICATIONS.LIST.REF_TABLE_SER') },
			{ field: 'read_status', displayName: $filter('translate')('NOTIFICATIONS.LIST.READ_STATUS') },
			{ field: 'date_added', displayName: $filter('translate')('NOTIFICATIONS.LIST.DATETIME_SENT') },
			{ field: 'mod_action', displayName: $filter('translate')('NOTIFICATIONS.LIST.ACTION') }
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
			templateUrl: ($scope.writeAccess ? 'templates/notification/edit.notification.html' : 'templates/notification/view.notification.html'),
			controller: 'notification.edit',
			scope: $scope,
			windowClass: 'customModal',
			backdrop: 'static',
		});

		// After update, refresh the notification list
		modalInstance.result.then(function () {
			getNotificationsList();
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
			getNotificationsList();
		});
	};

});
