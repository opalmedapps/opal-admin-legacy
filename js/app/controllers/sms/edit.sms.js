angular.module('opalAdmin.controllers.sms.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('sms.edit', function ($scope, $filter, $uibModal, $uibModalInstance, smsCollectionService, $state, Session, ErrorHandler) {

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };

    $scope.changesDetected = false;
    if($scope.currentAppointment.apptype=='-')
        $scope.currentAppType = "UNDEFINED";
    else $scope.currentAppType = $scope.currentAppointment.apptype;
    $scope.typeSelected = $scope.currentAppType;

    getSmsTypeList();
    $scope.typeSearchField = "";

    //Function to get appointment type list.
    function getSmsTypeList(){
        smsCollectionService.getSmsType($scope.currentAppointment.spec).then(function (response) {
            $scope.TypeList = response.data;
            $scope.TypeList.push({type:'UNDEFINED'})
        }).catch(function(err) {
            ErrorHandler.onError(err, $filter('translate')('SMS.EDIT.ERROR_DETAILS'));
        });
    }

    //Function for searchbar
    $scope.searchType = function (field) {
        $scope.typeSearchField = field;
    };
    $scope.searchTypeFilter = function (Filter) {
        var keyword = new RegExp($scope.typeSearchField, 'i');
        return !$scope.typeSearchField || keyword.test(Filter);
    };
    $scope.updateType = function(type){
        $scope.typeSelected = null;
        $scope.changesDetected = (type.type != $scope.currentAppType);
        $scope.typeSelected = type.type;
    }

    var arrValidationInsert = [
        $filter('translate')('SMS.VALIDATION.TYPE'),
        $filter('translate')('SMS.VALIDATION.APPOINTMENT_ID'),
    ];

    // Submit changes
    $scope.updateAppointment = function() {
        if($scope.changesDetected) {
            var update = {
                type:$scope.typeSelected, id:$scope.currentAppointment.id
            }
            if(update.type == "UNDEFINED") update.type = 0;
            $.ajax({
                type: "POST",
                url: "sms/update/sms-type",
                data: update,
                success: function () {},
                error: function (err) {
                    err.responseText = JSON.parse(err.responseText);
                    ErrorHandler.onError(err, $filter('translate')('SMS.EDIT.ERROR'),arrValidationInsert);
                },
                complete: function () {
                    $uibModalInstance.close();
                }
            });
        }
    };

});