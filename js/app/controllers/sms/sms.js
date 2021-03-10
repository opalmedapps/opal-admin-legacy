angular.module('opalAdmin.controllers.sms', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms', function ($scope, $uibModal, $filter, $state, aliasCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
        $scope.test = "Welcome";
        console.log($scope.test);
        $scope.navMenu = Session.retrieveObject('menu');
    });