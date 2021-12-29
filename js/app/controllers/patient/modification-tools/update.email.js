angular.module('opalAdmin.controllers.update.email', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.email', function ($scope, $filter, $uibModal, $uibModalInstance, patientCollectionService, $state, Session, ErrorHandler) {

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };

    $scope.new_email = {
        firstTime: null,
        secondTime: null,
        errorMessage: null,
    };

    var arrValidationInsert = [
        $filter('translate')('SMS.VALIDATION.APPOINTMENT_ID'),
        $filter('translate')('SMS.VALIDATION.ACTIVE'),
        $filter('translate')('SMS.VALIDATION.TYPE'),
        $filter('translate')('SMS.VALIDATION.UNDEFINED_ACTIVE'),
    ];

    $scope.updateEmail = function(){
        if($scope.new_email.firstTime !== null && $scope.new_email.secondTime !== null && $scope.new_email.errorMessage === null){
            $.ajax({
                type: "POST",
                url: "patient/update/email",
                data: {
                    uid: "b0tEHXqDqwN9s7qKQdX1SqdTIQm1",
                    email: $scope.new_email.firstTime,
                    PatientSerNum: $scope.psnum,
                },
                success: function () {},
                error: function (err) {
                    err.responseText = JSON.parse(err.responseText);
                    ErrorHandler.onError(err, $filter('translate')('SMS.EDIT.ERROR'), arrValidationInsert);
                },
                complete: function () {
                    $uibModalInstance.close();
                }
            });
        }
    }

});