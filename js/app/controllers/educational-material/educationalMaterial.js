angular.module('opalAdmin.controllers.educationalMaterial', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.expandable', 'ui.grid.resizeColumns']).


	/******************************************************************************
	 * Educational Material Page controller
	 *******************************************************************************/
	controller('educationalMaterial', function ($scope, $filter, $sce, $uibModal, $state, educationalMaterialCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.edu_mat]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.edu_mat]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.edu_mat]) & (1 << 2)) !== 0);

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
		
		
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';
		if($scope.readAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.showEduMatLog(row.entity)"><i title="' + $filter('translate')('EDUCATION.LIST.LOGS') + '" class="fa fa-area-chart" ></i></a></strong> ';
		if($scope.writeAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editEduMat(row.entity)"><i title="' + $filter('translate')('EDUCATION.LIST.EDIT') + '" class="fa fa-pencil" ></i></a></strong> ';
		else
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.editEduMat(row.entity)"><i title="' + $filter('translate')('EDUCATION.LIST.VIEW') + '" class="fa fa-eye" ></i></a></strong> ';
		if($scope.deleteAccess)
			cellTemplateOperations += '- <strong><a href="" ng-click="grid.appScope.deleteEduMat(row.entity)"><i title="' + $filter('translate')('EDUCATION.LIST.DELETE') + '" class="fa fa-trash" ></i></a></strong>';
		cellTemplateOperations += '</div>';
		
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
					field: 'purpose.title_' + Session.retrieveObject('user').language.toUpperCase(),
					enableColumnMenu: false,
					displayName: $filter('translate')('EDUCATION.LIST.PURPOSE'),
					width: '15%'
				},
				{
					field: 'lastupdated',
					enableColumnMenu: false,
					displayName: $filter('translate')('EDUCATION.LIST.LAST_UPDATED'),
					width: '15%'
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

		getEducationalMaterialsList()

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
				templateUrl: ($scope.writeAccess ? 'templates/educational-material/edit.educational-material.html' : 'templates/educational-material/view.educational-material.html'),
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
								},
								{
									field: 'purpose_' + Session.retrieveObject('user').language.toUpperCase(),
									displayName: 'Purpose (EN)',
									width: '145',
									enableColumnMenu: false
								}
							],
							data: educationalMaterials[i].tocs
						};
						$scope.eduMatList.push(educationalMaterials[i]);
					}
				}

			}).catch(function (err) {
				ErrorHandler.onError(err, $filter('translate')('EDUCATION.LIST.ERROR_LIST'));
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
					let number = Math.round(Number(attrs.number));
					number = number < 0 ? 0 : number;
					number = number > 5 ? 5 : number;
					for (let i = 0; i < number; i++) {
						scope.rate.push({'Icon': 'glyphicon-star'});
					}
					for (let j = number; j < 5; j++) {
						scope.rate.push({'Icon': 'glyphicon-star-empty'});
					}
				}
			}
		};
	});


