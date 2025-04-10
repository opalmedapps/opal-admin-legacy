// SPDX-FileCopyrightText: Copyright (C) 2018 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.post', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).
filter('deliberatelyTrustAsHtml', function ($sce) {
	return function (text) {
		return $sce.trustAsHtml(text);
	};
}).
	/******************************************************************************
	 * Post Page controller
	 *******************************************************************************/
	controller('post', function ($scope, $filter, $sce, $state, $uibModal, postCollectionService, uiGridConstants, Session, ErrorHandler, MODULE)   {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.post]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.post]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.post]) & (1 << 2)) !== 0);

		// Function to go to add post page
		$scope.goToAddPost = function () {
			$state.go('post-add');
		};

		$scope.bannerMessage = "";
		// Function to show page banner
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 5000);
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

		$scope.detailView = "list";

		// Templates for post table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editPost(row.entity)">' +
			'<strong><a href="">{{row.entity.name_'+ Session.retrieveObject('user').language +'}}</a></strong></div>';


		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

		if($scope.readAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.showPostLog(row.entity)"><i title="'+$filter('translate')('POSTS.LIST.LOGS')+'" class="fa fa-area-chart" aria-hidden="true"></i></a></strong> ';

		if($scope.writeAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editPost(row.entity)"><i title="'+$filter('translate')('POSTS.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ';
		else
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editPost(row.entity)"><i title="'+$filter('translate')('POSTS.LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong> ';

		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deletePost(row.entity)"><i title="'+$filter('translate')('POSTS.LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong>';
		cellTemplateOperations += '</div>';

		var rowTemplate = '<div ng-class="{\'grid-disabled-row\':row.entity.disabled==1}"> ' +
			'<div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" ' +
			'class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader }" ui-grid-cell></div></div>';
		var cellTemplateLocked = '<div class="ui-grid-cell-contents" ng-show="row.entity.locked > 0"><div class="fa fa-lock text-danger"></div></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 0"><div class="fa fa-unlock text-success"></div></div>';


		// post table search textbox param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_'+ Session.retrieveObject('user').language, 'type_display'].forEach(function (field) {
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


		$scope.filterPost = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table options for post
		$scope.gridOptions = {
			data: 'postList',
			columnDefs: [
				{ field: 'locked', enableColumnMenu: false, displayName: '', cellTemplate: cellTemplateLocked, width: '2%', sortable: false, enableFiltering: false},
				{ field: 'name_'+ Session.retrieveObject('user').language, displayName: $filter('translate')('POSTS.LIST.TITLE_POST'), cellTemplate: cellTemplateName, width: '63%', enableColumnMenu: false },
				{
					field: 'type_display', enableColumnMenu: false, displayName: $filter('translate')('POSTS.LIST.TYPE'), width: '25%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: $filter('translate')('POSTS.LIST.ANNOUNCEMENT'), label: $filter('translate')('POSTS.LIST.ANNOUNCEMENT') }, { value: $filter('translate')('POSTS.LIST.PATIENTS_FOR_PATIENTS'), label: $filter('translate')('POSTS.LIST.PATIENTS_FOR_PATIENTS') }, { value: $filter('translate')('POSTS.LIST.TREATMENT_TEAM_MESSAGE'), label: $filter('translate')('POSTS.LIST.TREATMENT_TEAM_MESSAGE') }]
					}
				},
				// { field: 'publish', enableColumnMenu: false, displayName: $filter('translate')('POSTS.LIST.PUBLISH_FLAG'), width: '10%', cellTemplate: cellTemplatePublishCheckbox, enableFiltering: false },
				// { field: 'publish_date', enableColumnMenu: false, displayName: $filter('translate')('POSTS.LIST.PUBLISH_DATE'), width: '15%' },
				{ name: $filter('translate')('POSTS.LIST.OPERATIONS'), enableColumnMenu: false, cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '10%' }
			],
			//useExternalFiltering: true,
			enableFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
			rowTemplate: rowTemplate,


		};

		// Initialize list of existing post
		$scope.postList = [];
		$scope.postFlags = {
			flagList: []
		};

		// Initialize an object for deleting post
		$scope.postToDelete = {};

		// When this function is called, we set the post flags to checked
		// or unchecked based on value in the argument
		$scope.updateFlag = function (value) {
			value = parseInt(value);
			if (value == 1) {
				return 1;
			} else {
				return 0;
			}
		};


		// Function for when the post checkbox has been modified
		$scope.checkPublishFlag = function (post) {

			$scope.changesMade = true;
			post.publish = parseInt(post.publish);
			// If the "publish" column has been checked
			if (post.publish) {
				post.publish = 0; // set publish to "false"
			}

			// Else the "Publish" column was unchecked
			else {
				post.publish = 1; // set publish to "true"
			}

			post.changed = 1; // flag change to this post
		};

		// Function for when the post checkbox has been modified
		$scope.checkDisabledFlag = function (post) {

			$scope.changesMade = true;
			post.disabled = parseInt(post.disabled);
			// If the "publish" column has been checked
			if (post.disabled) {
				post.disabled = 0; // set disabled to "false"
			}

			// Else the "Disabled" column was unchecked
			else {
				post.disabled = 1; // set disabled to "true"
			}

			post.changed = 1; // flag change to this post
		};

		function getPostsList() {
			postCollectionService.getPosts(Session.retrieveObject('user').id).then(function (response) {
				response.data.forEach(function (row) {
					if (Session.retrieveObject('user').language.toUpperCase() === "FR") {
						switch(row.type) {
						case "Treatment Team Message":
							row.type_display = $filter('translate')('POSTS.LIST.TREATMENT_TEAM_MESSAGE');
							break;
						case "Announcement":
							row.type_display = $filter('translate')('POSTS.LIST.ANNOUNCEMENT');
							break;
						case "Patients for Patients":
							row.type_display = $filter('translate')('POSTS.LIST.PATIENTS_FOR_PATIENTS');
							break;
						default:
							row.type_display = $filter('translate')('POSTS.LIST.NOT_TRANSLATED');
						}
					}
					else
						row.type_display = row.type;
				});
				$scope.postList = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('POSTS.LIST.ERROR_POSTS'));
			});
		}

		$scope.switchDetailView = function (view) {
			// only switch when there's no changes that have been made
			if (!$scope.changesMade) {
				$scope.detailView = view;
			}
		};

		$scope.$watch('detailView', function (view) {
			if (view === 'list') {
				// Call our API to get the list of existing posts
				getPostsList();
				if ($scope.postListLogs.length) {
					$scope.postListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			}
			else if (view === 'chart') {
				// Call our API to get post logs
				postCollectionService.getPostChartLogs().then(function (response) {
					$scope.postChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.postChartLogs, function(series) {
						angular.forEach(series.data, function(log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function(err) {
					ErrorHandler.onError(err, $filter('translate')('POSTS.LIST.ERROR_POSTS_LOGS'));
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
				text: $filter('translate')('POSTS.LIST.ALL_POST_LOGS')
			},
			subtitle: {
				text: $filter('translate')('POSTS.LIST.HIGHLIGHT')
			},
			xAxis: {
				type: 'datetime',
				title: {
					text: $filter('translate')('POSTS.LIST.DATETIME_SENT')
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
							postCollectionService.getPostListLogs(cronSerials, $scope.currentPost.type, Session.retrieveObject('user').id).then(function(response){
								response.data.forEach(function (row) {
									if (Session.retrieveObject('user').language.toUpperCase() === "FR") {
										switch(row.type) {
										case "Treatment Team Message":
											row.type = $filter('translate')('POSTS.LIST.TREATMENT_TEAM_MESSAGE');
											break;
										case "Announcement":
											row.type = $filter('translate')('POSTS.LIST.ANNOUNCEMENT');
											break;
										case "Patients for Patients":
											row.type = $filter('translate')('POSTS.LIST.PATIENTS_FOR_PATIENTS');
											break;
											// default:
											// 	row.type = $filter('translate')('POSTS.LIST.NOT_TRANSLATED');
										}
									}
								});
								$scope.postListLogs = response.data;
							});
						}
						else {
							$scope.postListLogs = [];
							$scope.gridApiLog.grid.refresh();

						}
					}
				}
			},
			yAxis: {
				title: {
					text: $filter('translate')('POSTS.LIST.NUMBER_POSTS_PUBLISHED')
				},
				tickInterval: 1,
				min: 0
			},
			tooltip: {
				headerFormat: '<b>{series.name_FR}</b><br>',
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
								postCollectionService.getPostListLogs(cronLogSerNum, $scope.currentPost.type, Session.retrieveObject('user').id).then(function(response){
									response.data.forEach(function (row) {
										if (Session.retrieveObject('user').language.toUpperCase() === "FR") {
											switch(row.type) {
											case "Treatment Team Message":
												row.type = $filter('translate')('POSTS.LIST.TREATMENT_TEAM_MESSAGE');
												break;
											case "Announcement":
												row.type = $filter('translate')('POSTS.LIST.ANNOUNCEMENT');
												break;
											case "Patients for Patients":
												row.type = $filter('translate')('POSTS.LIST.PATIENTS_FOR_PATIENTS');
												break;
												// default:
												// 	row.type = $filter('translate')('POSTS.LIST.NOT_TRANSLATED');
											}
										}
									});

									$scope.postListLogs = response.data;
								});
							},
							unselect: function (point) {
								$scope.postListLogs = [];
								$scope.gridApiLog.grid.refresh();

							}
						}
					}
				}
			},
			series: []
		};

		$scope.postListLogs = [];
		// Table options for post logs
		$scope.gridLogOptions = {
			data: 'postListLogs',
			columnDefs: [
				{ field: 'post_control_name', displayName: $filter('translate')('POSTS.LIST.POST') },
				{ field: 'type', displayName: $filter('translate')('POSTS.LIST.TYPE') },
				{ field: 'revision', displayName: $filter('translate')('POSTS.LIST.REVISION') },
				{ field: 'cron_serial', displayName: $filter('translate')('POSTS.LIST.CRONLOGSER') },
				{ field: 'patient_serial', displayName: $filter('translate')('POSTS.LIST.PATIENTSER') },
				{ field: 'read_status', displayName: $filter('translate')('POSTS.LIST.READ_STATUS') },
				{ field: 'date_added', displayName: $filter('translate')('POSTS.LIST.DATETIME_SENT') },
				{ field: 'mod_action', displayName: $filter('translate')('POSTS.LIST.ACTION') }
			],
			rowHeight: 30,
			useExternalFiltering: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApiLog = gridApi;
			},
		};

		// Initialize a scope variable for a selected post
		$scope.currentPost = {};

		// Function for when the post has been clicked for viewing logs
		$scope.showPostLog = function (post) {

			$scope.currentPost = post;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/post/log.post.html',
				controller: 'post.log',
				scope: $scope,
				windowClass: 'logModal',
				backdrop: 'static',
			});
		};

		// Function for when the post has been clicked for editing
		// We open a modal
		$scope.editPost = function (post) {

			$scope.currentPost = post;
			var modalInstance = $uibModal.open({
				templateUrl: ($scope.writeAccess ? 'templates/post/edit.post.html' : 'templates/post/view.post.html'),
				controller: 'post.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the post list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing posts
				getPostsList();
			});

		};

		// Function for when the post has been clicked for deletion
		// Open a modal
		$scope.deletePost = function (currentPost) {
			if ($scope.deleteAccess) {
				// Assign selected post as the post to delete
				$scope.postToDelete = currentPost;
				$scope.postToDelete.name_display = (Session.retrieveObject('user').language.toUpperCase() === "FR" ? currentPost.name_FR : currentPost.name_EN);

				var modalInstance = $uibModal.open({
					templateUrl: (currentPost.locked > 0 ? 'templates/post/cannot.delete.post.html' : 'templates/post/delete.post.html'),
					controller: 'post.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});

				// After delete, refresh the post list
				modalInstance.result.then(function () {
					// Call our API to get the list of existing posts
					getPostsList();
				});
			}
		};
	});
