angular.module('opalAdmin.controllers.home', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


	/******************************************************************************
	 * Home Page controller
	 *******************************************************************************/
	controller('home', function ($scope, $uibModal, $filter, $state, aliasCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		angular.forEach($scope.navMenu, function(category) {
			angular.forEach(category.menu, function(menu) {
				menu.sub = $filter('translate')('NAVIGATION_MENU.MODULE_ID_' + menu.ID + '_SUB');
			});
		});
	});