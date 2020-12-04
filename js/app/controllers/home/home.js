angular.module('opalAdmin.controllers.home', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


	/******************************************************************************
	 * Home Page controller
	 *******************************************************************************/
	controller('home', function ($scope, $uibModal, $filter, $state, aliasCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
	});