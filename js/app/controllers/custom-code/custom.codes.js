angular.module('opalAdmin.controllers.customCode', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('customCode', function ($sce, $scope, $state, $filter, $timeout, $uibModal, customCodeCollectionService, filterCollectionService, Session, uiGridConstants) {

		// get current user id
		var user = Session.retrieveObject('user');
		var OAUserId = user.id;

		$scope.goToAddPublication = function () {
			$state.go('custom-code-add');
		};

		// Banner
		$scope.bannerMessage = "";
		// Function to show page banner
		$scope.showBanner = function () {
			$(".bannerMessage").slideDown(function () {
				setTimeout(function () {
					$(".bannerMessage").slideUp();
				}, 3000);
			});
		};

		getCustomCodesList();

		// Function to set banner class
		$scope.setBannerClass = function (classname) {
			// Remove any classes starting with "alert-"
			$(".bannerMessage").removeClass(function (index, css) {
				return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
			});
			// Add class
			$(".bannerMessage").addClass('alert-' + classname);
		};

		// Filter
		// search text-box param
		$scope.filterOptions = function (renderableRows) {
			var matcher = new RegExp($scope.filterValue, 'i');
			renderableRows.forEach(function (row) {
				var match = false;
				['code', 'description'].forEach(function (field) {
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

		// Function to filter questionnaires
		$scope.filterQuestionnaire = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();

		};

		// Table
		// Templates
		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">' +
			'<strong><a href="" ng-click="grid.appScope.editCustomCode(row.entity)"<i title="'+$filter('translate')('CUSTOM_CODE.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong>' +
			'- <strong><a href="" ng-click="grid.appScope.deleteCustomCode(row.entity)"><i title="'+$filter('translate')('CUSTOM_CODE.LIST.DELETE')+'" class="fa fa-trash" aria-hidden="true"></i></a></strong></div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editCustomCode(row.entity)">' +
			'<strong><a href="">{{row.entity.description}}</a></strong></div>';
		var cellTemplatePublication = '<div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==1">'+$filter('translate')('CUSTOM_CODE.LIST.ALIAS')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==6">'+$filter('translate')('CUSTOM_CODE.LIST.DIAGNOSTIC')+'</div><div class="ui-grid-cell-contents" ng-if="row.entity.moduleId==9">'+$filter('translate')('CUSTOM_CODE.LIST.TEST')+'</div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'customCodesList',
			columnDefs: [
				{ field: 'description', enableColumnMenu: false, displayName: $filter('translate')('CUSTOM_CODE.LIST.DESCRIPTION'), cellTemplate: cellTemplateName, sort: {direction: uiGridConstants.ASC, priority: 0}},
				{ field: 'code', enableColumnMenu: false, displayName: $filter('translate')('CUSTOM_CODE.LIST.CODE'), width: '30%'},
				{
					field: 'module_'+Session.retrieveObject('user').language, displayName: $filter('translate')('CUSTOM_CODE.LIST.MODULE'), enableColumnMenu: false, width: '25%', filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: [{ value: $filter('translate')('CUSTOM_CODE.LIST.ALIAS'), label: $filter('translate')('CUSTOM_CODE.LIST.ALIAS') }, { value: $filter('translate')('CUSTOM_CODE.LIST.DIAGNOSTIC'), label: $filter('translate')('CUSTOM_CODE.LIST.DIAGNOSTIC') }, { value: $filter('translate')('CUSTOM_CODE.LIST.TEST'), label: $filter('translate')('CUSTOM_CODE.LIST.TEST') }]
					}
				},
				{ name: $filter('translate')('CUSTOM_CODE.LIST.OPERATIONS'), width: '10%', cellTemplate: cellTemplateOperations, enableColumnMenu: false, enableFiltering: false, sortable: false }
			],
			enableFiltering: true,
			enableSorting: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize object for storing questionnaires
		$scope.customCodesList = [];

		function getCustomCodesList() {
			customCodeCollectionService.getCustomCodes(OAUserId).then(function (response) {
				$scope.customCodesList = response.data;
				console.log(response.data);
			}).catch(function(err) {
				alert($filter('translate')('CUSTOM_CODE.LIST.ERROR_PUBLICATION') + "\r\n\r\n" + err.status + " - " + err.statusText + " - " + JSON.parse(err.data));
			});
		}

		// Function to edit questionnaire
		$scope.editCustomCode = function (publication) {
			$scope.currentPublication = publication;
			var modalInstance = $uibModal.open({ // open modal
				templateUrl: 'templates/publication/edit.publication.html',
				controller: 'publication.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				getCustomCodesList();
			});
		};

		// Function for when the custom code has been clicked for deletion
		// Open a modal
		$scope.deleteCustomCode = function (currentCustomCode) {

			// Assign selected custom code as the custom code to delete
			$scope.postToDelete = currentCustomCode;
			$scope.postToDelete.name_display = (Session.retrieveObject('user').language.toUpperCase() === "FR"?currentCustomCode.name_FR:currentCustomCode.name_EN);


			if(currentCustomCode.locked > 0) {
				var modalInstance = $uibModal.open({
					templateUrl: 'templates/custom-code/cannot.delete.custom.code.html',
					controller: 'customCode.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});
			}
			else {
				var modalInstance = $uibModal.open({
					templateUrl: 'templates/custom-code/delete.custom.code.html',
					controller: 'customCode.delete',
					windowClass: 'deleteModal',
					scope: $scope,
					backdrop: 'static',
				});
			}

			// After delete, refresh the custom code list
			modalInstance.result.then(function () {
				// Call our API to get the list of existing posts
				getPostsList();
			});
		};
	});

