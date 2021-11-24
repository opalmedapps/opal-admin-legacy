angular.module('opalAdmin.controllers.patient.modification', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('patientReports', function ($scope, $rootScope, Session, ErrorHandler, MODULE, $uibModal, $filter) {

	$scope.navMenu = Session.retrieveObject('menu');

});