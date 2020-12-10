angular.module('opalAdmin.controllers.patientReportHandler', ['ui.bootstrap', 'ui.grid']).


/******************************************************************************
 * Controller for the patient report handler page
 *******************************************************************************/
controller('patientReportHandler', function ($scope, $state, Session, ErrorHandler, MODULE) {
	$scope.navMenu = Session.retrieveObject('menu');
	$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.user]) & (1 << 0)) !== 0);
	$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.user]) & (1 << 1)) !== 0);
	$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.user]) & (1 << 2)) !== 0);
	console.log("Control passed to patient report handler...");
	// Function to go to individual report
	$scope.goToIndividual = function () {
        $state.go('patients/report/individual');
	};

	$scope.goToGroup = function() {
		$state.go('patients/report/group');
	}

	

	
});

