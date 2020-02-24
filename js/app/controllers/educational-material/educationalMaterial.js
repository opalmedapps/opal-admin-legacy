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
			'<strong><a href="">{{row.entity.name_' + Session.retrieveObject('user').language.toUpperCase() + '}}</a></strong></div>';
		var checkboxCellTemplate = '<div style="text-align: center; cursor: pointer;" ' +
			'ng-click="grid.appScope.checkPublishFlag(row.entity)" ' +
			'class="ui-grid-cell-contents"><input style="margin: 4px;" type="checkbox" ' +
			'ng-checked="grid.appScope.updatePublishFlag(row.entity.publish)" ng-model="row.entity.publish"></div>';
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.showEduMatLog(row.entity)"><i title="' + $filter('translate')('EDUCATION.LIST.LOGS') + '" class="fa fa-area-chart" aria-hidden="true"></i></a></strong> - ' +
			'<strong><a href="" ng-click="grid.appScope.editEduMat(row.entity)"><i title="' + $filter('translate')('EDUCATION.LIST.EDIT') + '" class="fa fa-pencil" aria-hidden="true"></i></a></strong> ' +
			'- <strong><a href="" ng-click="grid.appScope.deleteEduMat(row.entity)"><i title="' + $filter('translate')('EDUCATION.LIST.DELETE') + '" class="fa fa-trash" aria-hidden="true"></i></a></strong></div>';
		var expandableRowTemplate = '<div ui-grid="row.entity.subGridOptions"></div>';
		var ratingCellTemplate = '<div class="ui-grid-cell-contents" ng-show="row.entity.rating == -1">' + $filter('translate')('EDUCATION.LIST.NO_RATING') + '</div>' +
			'<div class="ui-grid-cell-contents" ng-hide="row.entity.rating == -1"><stars number="{{row.entity.rating}}"></stars> </div>';
		// var cellTemplateLocked = '<div class="ui-grid-cell-contents" ng-show="row.entity.locked > 0"><div class="fa fa-lock text-danger"></div></div>' +
			'<div class="ui-grid-cell-contents" ng-show="row.entity.locked == 0"><div class="fa fa-unlock text-success"></div></div>';

		// Search engine for table
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['name_' + Session.retrieveObject('user').language.toUpperCase(), 'type_' + Session.retrieveObject('user').language.toUpperCase()].forEach(function (field) {
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
				// { field: 'locked', enableColumnMenu: false, displayName: '', cellTemplate: cellTemplateLocked, width: '2%', sortable: false, enableFiltering: false},
				{
					field: 'name_' + Session.retrieveObject('user').language.toUpperCase(),
					displayName: $filter('translate')('EDUCATION.LIST.TITLE_2'),
					cellTemplate: cellTemplateName,
					width: '35%',
					enableColumnMenu: false
				},
				{
					field: 'rating',
					enableColumnMenu: false,
					name: $filter('translate')('EDUCATION.LIST.RATING'),
					cellTemplate: ratingCellTemplate,
					width: '10%',
					enableFiltering: false
				},
				{
					field: 'type_' + Session.retrieveObject('user').language.toUpperCase(),
					enableColumnMenu: false,
					displayName: $filter('translate')('EDUCATION.LIST.TYPE'),
					width: '15%'
				},
				{
					field: 'phase_' + Session.retrieveObject('user').language.toUpperCase(),
					enableColumnMenu: false,
					displayName: $filter('translate')('EDUCATION.LIST.PHASE_IN_TREATMENT'),
					width: '20%',
					filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{
							value: $filter('translate')('EDUCATION.LIST.PRIOR'),
							label: $filter('translate')('EDUCATION.LIST.PRIOR')
						}, {
							value: $filter('translate')('EDUCATION.LIST.DURING'),
							label: $filter('translate')('EDUCATION.LIST.DURING')
						}, {
							value: $filter('translate')('EDUCATION.LIST.AFTER'),
							label: $filter('translate')('EDUCATION.LIST.AFTER')
						}]
					}
				},
				{
					field: 'lastupdated',
					enableColumnMenu: false,
					displayName: $filter('translate')('EDUCATION.LIST.LAST_UPDATED'),
					width: '10%'
				},
				{
					name: $filter('translate')('EDUCATION.LIST.OPERATIONS'),
					enableColumnMenu: false,
					cellTemplate: cellTemplateOperations,
					sortable: false,
					enableFiltering: false
				}
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
			return (parseInt(value) === 1);
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
				$scope.eduMatPublishes.user = Session.retrieveObject('user');
				// Submit form
				$.ajax({
					type: "POST",
					url: "educational-material/update/educational-material-publish-flags",
					data: $scope.eduMatPublishes,
					success: function (response) {
						getEducationalMaterialsList();
						response = JSON.parse(response);
						// Show success or failure depending on response
						if (response.value) {
							$scope.setBannerClass('success');
							$scope.bannerMessage = $filter('translate')('EDUCATION.LIST.SUCCESS_FLAGS');
							$scope.showBanner();
						} else {
							alert($filter('translate')('EDUCATION.LIST.ERROR_FLAGS') + "\r\n\r\n" + response.message);
						}
						$scope.changesMade = false;
						$scope.eduMatPublishes.publishList = [];
					},
					error: function (err) {
						alert($filter('translate')('EDUCATION.LIST.ERROR_FLAGS') + "\r\n\r\n" + err.status + " - " + err.statusText);
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
				getEducationalMaterialsList();
				if ($scope.educationalMaterialListLogs.length) {
					$scope.educationalMaterialListLogs = [];
					$scope.gridApiLog.grid.refresh();
				}
			} else if (view === 'chart') {
				// Call our API to get educational material logs
				educationalMaterialCollectionService.getEducationalMaterialChartLogs().then(function (response) {
					$scope.educationalMaterialChartLogs = $scope.chartConfig.series = response.data;
					angular.forEach($scope.educationalMaterialChartLogs, function (serie) {
						angular.forEach(serie.data, function (log) {
							log.x = new Date(log.x);
						});
					});
				}).catch(function (response) {
					alert($filter('translate')('EDUCATION.LIST.ERROR_FLAGS') + "\r\n\r\n" + response.status + " - " + response.data);
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
				text: $filter('translate')('EDUCATION.LIST.ALL_LOGS')
			},
			subtitle: {
				text: $filter('translate')('EDUCATION.LIST.HIGHLIGHT')
			},
			xAxis: {
				type: 'datetime',
				title: {
					text: $filter('translate')('EDUCATION.LIST.DATETIME')
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
							educationalMaterialCollectionService.getEducationalMaterialListLogs(cronSerials).then(function (response) {
								$scope.educationalMaterialListLogs = response.data;
							});
						} else {
							$scope.educationalMaterialListLogs = [];
							$scope.gridApiLog.grid.refresh();

						}
					}
				}
			},
			yAxis: {
				title: {
					text: $filter('translate')('EDUCATION.LIST.NUMBER')
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
							select: function (point) {
								var cronLogSerNum = [point.target.cron_serial];
								educationalMaterialCollectionService.getEducationalMaterialListLogs(cronLogSerNum).then(function (response) {
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
				{field: 'material_name', displayName: $filter('translate')('EDUCATION.LIST.NAME'), enableColumnMenu: false},
				{field: 'revision', displayName: $filter('translate')('EDUCATION.LIST.REVISION'), enableColumnMenu: false},
				{field: 'cron_serial', displayName: $filter('translate')('EDUCATION.LIST.CRONLOGSER'), enableColumnMenu: false},
				{field: 'patient_serial', displayName: $filter('translate')('EDUCATION.LIST.PATIENTSER'), enableColumnMenu: false},
				{field: 'read_status', displayName: $filter('translate')('EDUCATION.LIST.READ_STATUS'), enableColumnMenu: false},
				{field: 'date_added', displayName: $filter('translate')('EDUCATION.LIST.PATIENTSER'), enableColumnMenu: false},
				{field: 'mod_action', displayName: $filter('translate')('EDUCATION.LIST.ACTION'), enableColumnMenu: false}
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

			if(Session.retrieveObject('user').language.toUpperCase() === "FR")
				$scope.currentEduMat.type_display = $scope.currentEduMat.type_FR;
			else
				$scope.currentEduMat.type_display = $scope.currentEduMat.type_EN;

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
				getEducationalMaterialsList();
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
				getEducationalMaterialsList();
			});
		};

		function getEducationalMaterialsList() {
			educationalMaterialCollectionService.getEducationalMaterials().then(function (response) {
				$scope.eduMatList = [];
				var educationalMaterials = response.data;
				// Assign value
				for (var i = 0; i < educationalMaterials.length; i++) {
					if (parseInt(educationalMaterials[i].parentFlag) === 1) {
						educationalMaterials[i].subGridOptions = {
							columnDefs: [
								{
									field: 'name_' + Session.retrieveObject('user').language.toUpperCase(),
									displayName: 'Name (EN)',
									width: '355',
									enableColumnMenu: false
								},
								{
									field: 'type_' + Session.retrieveObject('user').language.toUpperCase(),
									displayName: 'Type (EN)',
									width: '145',
									enableColumnMenu: false
								}
							],
							data: educationalMaterials[i].tocs
						};
						$scope.eduMatList.push(educationalMaterials[i]);
					}
				}

			}).catch(function (response) {
				alert($filter('translate')('EDUCATION.LIST.ERROR_LIST') + "\r\n\r\n" + response.status + " - " + response.data);
			});
		}

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
						scope.rate.push({'Icon': 'glyphicon-star'});
					}
					for (var j = number; j < 5; j++) {
						scope.rate.push({'Icon': 'glyphicon-star-empty'});
					}
				}
			}
		};
	});


