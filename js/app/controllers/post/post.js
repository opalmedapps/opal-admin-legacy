angular.module('opalAdmin.controllers.post', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'textAngular']).


	// Function to accept/trust html (styles, classes, etc.)
	filter('deliberatelyTrustAsHtml', function ($sce) {
		return function (text) {
			return $sce.trustAsHtml(text);
		};
	}).
	/******************************************************************************
	* Post Page controller 
	*******************************************************************************/
	controller('post', function ($scope, $filter, $sce, $state, $uibModal, postCollectionService, filterCollectionService, uiGridConstants, Session) {

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
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
		var cellTemplatePublishCheckbox = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updateFlag(row.entity.publish)" ng-model="row.entity.publish"></div>';
		var cellTemplateDisableCheckbox = '<div style="text-align:center; cursor:pointer;" ' +
			'ng-click="grid.appScope.checkDisabledFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin:4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updateFlag(row.entity.disabled)" ng-model="row.entity.disabled"></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.showPostLog(row.entity)">Logs</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.editPost(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deletePost(row.entity)">Delete</a></strong></div>';
		var rowTemplate = '<div ng-class="{\'grid-disabled-row\':row.entity.disabled==1}"> ' +
			'<div ng-repeat="(colRenderIndex, col) in colContainer.renderedColumns track by col.colDef.name" ' +
			'class="ui-grid-cell" ng-class="{ \'ui-grid-row-header-cell\': col.isRowHeader }" ui-grid-cell></div></div>';



		// post table search textbox param
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


		$scope.filterPost = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table options for post
		$scope.gridOptions = {
			data: 'postList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '30%' },
				{
					field: 'type', displayName: 'Type', width: '15%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: 'Announcement', label: 'Announcement' }, { value: 'Patients for Patients', label: 'Patients for Patients' }, { value: 'Treatment Team Message', label: 'Treatment Team Message' }]
					}
				},
				{ field: 'publish', displayName: 'Publish Flag', width: '10%', cellTemplate: cellTemplatePublishCheckbox, enableFiltering: false },
				{ field: 'publish_date', displayName: 'Publish Date', width: '15%' },
				{ field: 'disabled', displayName: 'Disabled Flag', width: '10%', cellTemplate: cellTemplateDisableCheckbox, filter: { term: 0 } },
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '20%' }
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

		// Function to submit changes when flags have been modified
		$scope.submitFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.postList, function (post) {
					if (post.changed) {
						$scope.postFlags.flagList.push({
							serial: post.serial,
							publish: post.publish,
							disabled: post.disabled
						});
					}
				});
				// Log who updated post flags
				var currentUser = Session.retrieveObject('user');
				$scope.postFlags.user = currentUser;
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/post/update.post_publish_flags.php",
					data: $scope.postFlags,
					success: function (response) {
						// Call our API to get the list of existing posts
						postCollectionService.getPosts().then(function (response) {
							// Assign value
							$scope.postList = response.data;
						}).catch(function(response) {
							console.error('Error occurred getting posts:', response.status, response.data);
						});
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.bannerMessage = "Flag(s) Successfully Saved!";
							$scope.postFlags = {
								flagList: []
							};
						}
						else {
							$scope.setBannerClass('danger');
							$scope.bannerMessage = response.message;
						}
						$scope.showBanner();
						$scope.changesMade = false;
						$scope.postFlags.flagList = [];

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
				// Call our API to get the list of existing posts
				postCollectionService.getPosts().then(function (response) {
					// Assign value
					$scope.postList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting posts:', response.status, response.data);
				});
				if ($scope.postListLogs.length) {
					$scope.postListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			}	
			else if (view == 'chart') {
				// Call our API to get post logs
				postCollectionService.getPostChartLogs().then(function (response) {
					$scope.postChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.postChartLogs, function(serie) {
						angular.forEach(serie.data, function(log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function(response) {
					console.error('Error occurred getting post logs:', response.status, response.data);
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
		        text: 'All post logs'
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
		     				postCollectionService.getPostListLogs(cronSerials, $scope.currentPost.type).then(function(response){ 
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
		            text: 'Number of posts published'
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
		        				postCollectionService.getPostListLogs(cronLogSerNum, $scope.currentPost.type).then(function(response){ 
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
				{ field: 'post_control_name', displayName: 'Post' },
				{ field: 'type', displayName: 'Type' },
				{ field: 'revision', displayName: 'Revision No.' },
				{ field: 'cron_serial', displayName: 'CronLogSer' },
				{ field: 'patient_serial', displayName: 'PatientSer' },
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
				templateUrl: 'templates/post/edit.post.html',
				controller: 'post.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the post list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing posts
				postCollectionService.getPosts().then(function (response) {

					// Assign the retrieved response
					$scope.postList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting posts:', response.status, response.data);
				});
			});

		};

		// Function for when the post has been clicked for deletion
		// Open a modal
		$scope.deletePost = function (currentPost) {

			// Assign selected post as the post to delete
			$scope.postToDelete = currentPost;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/post/delete.post.html',
				controller: 'post.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the post list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing posts
				postCollectionService.getPosts().then(function (response) {
					// Assign the retrieved response
					$scope.postList = response.data;
				}).catch(function(response) {
					console.error('Error occurred getting posts:', response.status, response.data);
				});
			});

		};

	});


