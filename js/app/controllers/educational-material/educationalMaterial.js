angular.module('opalAdmin.controllers.educationalMaterial', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns']).


	/******************************************************************************
	* Educational Material Page controller 
	*******************************************************************************/
	controller('educationalMaterial', function ($scope, $filter, $sce, $uibModal, $state, educationalMaterialCollectionService, filterCollectionService, uiGridConstants, Session) {


		// Function to go to add educational material page
		$scope.goToAddEducationalMaterial = function () {
			$state.go('educational-material-add');
		};

		// Function to control search engine model
		$scope.filterEduMat = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		$scope.detailView = "list";

		// Templates for the table
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
			'ng-click="grid.appScope.editEduMat(row.entity)"> ' +
			'<strong><a href="">{{row.entity.name_EN}} / {{row.entity.name_FR}}</a></strong></div>';
		var checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updatePublishFlag(row.entity.publish)" ng-model="row.entity.publish"></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.showEduMatLog(row.entity)">Logs</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.editEduMat(row.entity)">Edit</a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteEduMat(row.entity)">Delete</a></strong></div>';
		var expandableRowTemplate = '<div ui-grid="row.entity.subGridOptions"></div>';
		var ratingCellTemplate = '<div class="ui-grid-cell-contents" ng-show="row.entity.rating == -1">No rating</div>' +
			'<div class="ui-grid-cell-contents" ng-hide="row.entity.rating == -1"><stars number="{{row.entity.rating}}"></stars> </div>';

		// Search engine for table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_EN', 'type_EN'].forEach(function (field) {
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

		// Table options for education material
		$scope.gridOptions = {
			data: 'eduMatList',
			columnDefs: [
				{ field: 'name_EN', displayName: 'Title (EN / FR)', cellTemplate: cellTemplateName, width: '25%' },
				{ field: 'rating', name: 'Average Rating', cellTemplate: ratingCellTemplate, width: '10%', enableFiltering: false },
				{ field: 'type_EN', displayName: 'Type (EN)', width: '15%' },
				{ field: 'publish', displayName: 'Publish Flag', width: '10%', cellTemplate: checkboxCellTemplate, enableFiltering: false },
				{
					field: 'phase_EN', displayName: 'Phase In Treatment (EN)', width: '10%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: 'Prior To Treatment', label: 'Prior To Treatment' }, { value: 'During Treatment', label: 'During Treatment' }, { value: 'After Treatment', label: 'After Treatment' }]
					}
				},
				{ field: 'lastupdated', displayName: 'Last Updated', width: '10%' },
				{ name: 'Operations', cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false }
			],
			//useExternalFiltering: true,
			enableFiltering: true,
			enableColumnResizing: true,
			expandableRowTemplate: expandableRowTemplate,
			//expandableRowHeight: 200,
			expandableRowScope: {
				subGridVariable: 'subGridScopeVariable'
			},
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize list of existing material
		$scope.eduMatList = [];
		$scope.eduMatPublishes = {
			publishList: []
		};

		// Initialize an object for deleting material
		$scope.eduMatToDelete = {};

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
		$scope.checkPublishFlag = function (edumat) {

			$scope.changesMade = true;
			edumat.publish = parseInt(edumat.publish);
			// If the "publish" column has been checked
			if (edumat.publish) {
				edumat.publish = 0; // set publish to "false"
			}

			// Else the "publish" column was unchecked
			else {
				edumat.publish = 1; // set publish to "true"
			}
			edumat.changed = 1; // flag change to entity
		};

		// Function to submit changes when publish flags have been modified
		$scope.submitPublishFlags = function () {
			if ($scope.changesMade) {
				angular.forEach($scope.eduMatList, function (edumat) {
					if (edumat.changed) {
						$scope.eduMatPublishes.publishList.push({
							serial: edumat.serial,
							publish: edumat.publish
						});
					}
				});
				// Log who updated publish flags
				var currentUser = Session.retrieveObject('user');
				$scope.eduMatPublishes.user = currentUser;
				// Submit form
				$.ajax({
					type: "POST",
					url: "php/educational-material/update.educational_material_publish_flags.php",
					data: $scope.eduMatPublishes,
					success: function (response) {
						// Call our API to get the list of existing educational materials
						educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {
							// Assign value
							$scope.edumatList = response.data;
						}).catch(function(response) {
							console.error('Error occurred getting educational material list:', response.status, response.data);
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
						$scope.eduMatPublishes.publishList = [];
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
				// Call our API to get the list of existing material
				educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {

					var educationalMaterials = response.data;
					// Assign value
					for (var i = 0; i < educationalMaterials.length; i++) {
						if (educationalMaterials[i].parentFlag == 1) {
							educationalMaterials[i].subGridOptions = {
								columnDefs: [
									{ field: 'name_EN', displayName: 'Name (EN)', width: '355' },
									{ field: 'type_EN', displayName: 'Type (EN)', width: '145' }
								],
								data: educationalMaterials[i].tocs
							};
							$scope.eduMatList.push(educationalMaterials[i]);
						}
					}

				}).catch(function(response) {
					console.error('Error occurred getting educational material list:', response.status, response.data);
				});
				if ($scope.educationalMaterialListLogs.length) {
					$scope.educationalMaterialListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			}	
			else if (view == 'chart') {
				// Call our API to get educational material logs
				educationalMaterialCollectionService.getEducationalMaterialChartLogs().then(function (response) {
					$scope.educationalMaterialChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.educationalMaterialChartLogs, function(serie) {
						angular.forEach(serie.data, function(log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function(response) {
					console.error('Error occurred getting educational material logs:', response.status, response.data);
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
		        text: 'All educational material logs'
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
		     				educationalMaterialCollectionService.getEducationalMaterialListLogs(cronSerials).then(function(response){ 
	        					$scope.educationalMaterialListLogs = response.data;
	        				});
		        		}
		        		else {
		        			$scope.educationalMaterialListLogs = [];
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
		        				educationalMaterialCollectionService.getEducationalMaterialListLogs(cronLogSerNum).then(function(response){ 
		        					$scope.educationalMaterialListLogs = response.data;
		        				});
		        			},
		        			unselect: function (point) {
		        				$scope.educationalMaterialListLogs = [];
		        				$scope.gridApiLog.grid.refresh();

		        			}
		        		}
		        	}
		        }
			},
		    series: []
		};

		$scope.educationalMaterialListLogs = [];
		// Table options for educational material logs
		$scope.gridLogOptions = {
			data: 'educationalMaterialListLogs',
			columnDefs: [
				{ field: 'material_name', displayName: 'Name' },
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

		// Initialize a scope variable for a selected educational material
		$scope.currentEduMat = {};

		// Function for when the educational material has been clicked for viewing logs
		$scope.showEduMatLog = function (educationalMaterial) {

			$scope.currentEduMat = educationalMaterial;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/educational-material/log.educational-material.html',
				controller: 'educationalMaterial.log',
				scope: $scope,
				windowClass: 'logModal',
				backdrop: 'static',
			});
		};

		// Function for when the edu material has been clicked for editing
		// We open a modal
		$scope.editEduMat = function (edumat) {

			$scope.currentEduMat = edumat;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/educational-material/edit.educational-material.html',
				controller: 'educationalMaterial.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the edu mat list
			modalInstance.result.then(function () {
				$scope.eduMatList = [];
				// Call our API to get the list of existing educational material
				educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {

					var educationalMaterials = response.data;

					for (var i = 0; i < educationalMaterials.length; i++) {
						if (educationalMaterials[i].parentFlag == 1) {
							educationalMaterials[i].subGridOptions = {
								columnDefs: [
									{ field: 'name_EN', displayName: 'Name (EN / FR)', width: '355' },
									{ field: 'type_EN', displayName: 'Type (EN)', width: '145' }
								],
								data: educationalMaterials[i].tocs,
							};
							$scope.eduMatList.push(educationalMaterials[i]);
						}
					}

				}).catch(function(response) {
					console.error('Error occurred getting educational material list:', response.status, response.data);
				});

			});

		};

		// Function for when the edu material has been clicked for deletion
		// Open a modal
		$scope.deleteEduMat = function (currentEduMat) {

			// Assign selected educational material as the item to delete
			$scope.eduMatToDelete = currentEduMat;
			var modalInstance = $uibModal.open({
				templateUrl: 'templates/educational-material/delete.educational-material.html',
				controller: 'educationalMaterial.delete',
				windowClass: 'deleteModal',
				scope: $scope,
				backdrop: 'static',
			});

			// After delete, refresh the eduMat list
			modalInstance.result.then(function () {
				$scope.eduMatList = [];
				// Call our API to get the list of existing educational material
				educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {

					var educationalMaterials = response.data;
					for (var i = 0; i < educationalMaterials.length; i++) {
						if (educationalMaterials[i].parentFlag == 1) {
							educationalMaterials[i].subGridOptions = {
								columnDefs: [
									{ field: 'name_EN', displayName: 'Name (EN / FR)', width: '355' },
									{ field: 'type_EN', displayName: 'Type (EN)', width: '145' }
								],
								data: educationalMaterials[i].tocs
							};
							$scope.eduMatList.push(educationalMaterials[i]);
						}
					}

				}).catch(function(response) {
					console.error('Error occurred getting educational material list:', response.status, response.data);
				});
			});
		};

	})

	// Rating system
	.directive('stars', function () {
		return {
			restrict: 'E',
			template: '<span style="display:inline-block;opacity:0.5;" ng-repeat="star in rate">'
			+ '<i class="glyphicon" ng-class="star.Icon" style="font-size:18px;color:#DAA520"></i>'
			+ '</span>',
			link: function (scope, element, attrs) {
				scope.rate = [];
				initRater();
				function initRater() {
					var number = Math.round(Number(attrs.number));
					for (var i = 0; i < number; i++) {
						scope.rate.push({ 'Icon': 'glyphicon-star' });
					}
					for (var j = number; j < 5; j++) {
						scope.rate.push({ 'Icon': 'glyphicon-star-empty' });
					}
				}
			}
		};
	});


