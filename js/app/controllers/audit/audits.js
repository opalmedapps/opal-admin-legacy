angular.module('opalAdmin.controllers.audit', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).
controller('audit', function ($rootScope, $scope, $filter, $sce, $state, $uibModal, auditCollectionService, Session, ErrorHandler, MODULE) {
	$scope.navMenu = Session.retrieveObject('menu');
	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.audit]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.audit]) & (1 << 1)) !== 0);
	$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.audit]) & (1 << 2)) !== 0);

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

	// audit table search textbox param
	$scope.filterOptions = function (renderableRows) {
		var matcher = new RegExp($scope.filterValue, 'i');
		renderableRows.forEach(function (row) {
			var match = false;
			['createdBy', 'ipAddress', 'module', 'method', 'access', 'creationDate'].forEach(function (field) {
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

	$scope.filterAudit = function (filterValue) {
		$scope.filterValue = filterValue;
		$scope.gridApi.grid.refresh();
	};

	var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents"' +
		'ng-click="grid.appScope.viewAudit(row.entity)">' +
		'<strong><a href="">{{row.entity.createdBy}}</a></strong></div>';

	var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';
	cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.viewAudit(row.entity)"><i title="' + $filter('translate')('AUDIT.LIST.VIEW') + '" class="fa fa-eye"></i></a></strong> ';
	cellTemplateOperations += '</div>';

	// Table options for audit
	$scope.gridOptions = {
		data: 'auditList',
		columnDefs: [
			{ field: 'createdBy', displayName: $filter('translate')('AUDIT.LIST.USER'),  cellTemplate: cellTemplateName, width: '15%', enableColumnMenu: false },
			{ field: 'ipAddress', displayName: $filter('translate')('AUDIT.LIST.IP'), width: '15%', enableColumnMenu: false },
			{ field: 'module', displayName: $filter('translate')('AUDIT.LIST.MODULE'), width: '15%', enableColumnMenu: false },
			{ field: 'method', displayName: $filter('translate')('AUDIT.LIST.METHOD'), width: '15%', enableColumnMenu: false },
			{ field: 'access', displayName: $filter('translate')('AUDIT.LIST.ACCESS'), width: '15%', enableColumnMenu: false },
			{ field: 'creationDate', displayName: $filter('translate')('AUDIT.LIST.DATE'), width: '15%', enableColumnMenu: false },
			{ name: $filter('translate')('AUDIT.LIST.OPERATIONS'), enableColumnMenu: false, cellTemplate: cellTemplateOperations, sortable: false, enableFiltering: false, width: '10%' }
		],
		enableFiltering: true,
		//useExternalFiltering: true,
		enableColumnResizing: true,
		onRegisterApi: function (gridApi) {
			$scope.gridApi = gridApi;
			$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
		},

	};

	// Initialize list of existing audits
	$scope.auditList = [];

	auditCollectionService.getAudits().then(function (response) {
		$scope.auditList = response.data;
		console.log(response.data);
	}).catch(function(err) {
		ErrorHandler.onError(err, $filter('translate')('AUDIT.LIST.ERROR'));
	});
});
