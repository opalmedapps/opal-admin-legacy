// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.home', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


	/******************************************************************************
	 * Home Page controller
	 *******************************************************************************/
	controller('home', function ($scope, $uibModal, $filter, $state, aliasCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
	});
