// SPDX-FileCopyrightText: Copyright (C) 2021 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.patientReportHandler', ['ui.bootstrap', 'ui.grid']).


/******************************************************************************
 * Controller for the patient report handler page
 *******************************************************************************/
controller('patientReportHandler', function ($scope, $state, Session, ErrorHandler, MODULE) {
	//navigation
	$scope.navMenu = Session.retrieveObject('menu');
    $scope.navSubMenu = Session.retrieveObject('subMenu')[MODULE.patient];
    angular.forEach($scope.navSubMenu, function(menu) {
        menu.name_display = (Session.retrieveObject('user').language === "FR" ? menu.name_FR : menu.name_EN);
        menu.description_display = (Session.retrieveObject('user').language === "FR" ? menu.description_FR : menu.description_EN);
    });

    $scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 0)) !== 0);
    $scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 1)) !== 0);
    $scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.patient]) & (1 << 2)) !== 0);

	// Function to go to individual report
	$scope.goToIndividual = function () {
        $state.go('patients/report/individual');
	};

	$scope.goToGroup = function() {
		$state.go('patients/report/group');
	}

	

	
});

