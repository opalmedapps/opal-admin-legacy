angular.module('opalAdmin.controllers.sms.edit', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns']).

controller('sms.edit', function ($scope, $filter, $uibModal, $uibModalInstance, smsCollectionService, $state, Session, ErrorHandler, MODULE) {

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };
    $scope.changesDetected = false;

    $scope.typeSelected = $scope.currentAppointment.apptype;
    getSmsTypeList();
    $scope.typeSearchField = "";

    $scope.searchType = function (field) {
        $scope.typeSearchField = field;
    };
    $scope.searchTypeFilter = function (Filter) {
        var keyword = new RegExp($scope.typeSearchField, 'i');
        return !$scope.typeSearchField || keyword.test(Filter);
    };
    $scope.updateType = function(type){
        $scope.typeSelected = null;
        $scope.changesDetected = (type.type != $scope.currentAppointment.apptype);
        $scope.typeSelected = type.type;
    }

    $scope.updateAppointment = function() {
        if($scope.changesDetected) {
            var update = {
                information:{type:$scope.typeSelected, appcode:$scope.currentAppointment.code, ressernum:$scope.currentAppointment.ressernum}
            }
            $.ajax({
                type: "POST",
                url: "sms/update/smsType",
                data: update,
                success: function () {},
                error: function (err) {
                    ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_LIST'));
                },
                complete: function () {
                    $uibModalInstance.close();
                }
            });
        }
    };

    function getSmsTypeList(){
        smsCollectionService.getSmsType($scope.currentAppointment.spec).then(function (response) {
            $scope.TypeList = response.data;
            $scope.TypeList.push({type:'UNDEFINED'})
        });
    }
});