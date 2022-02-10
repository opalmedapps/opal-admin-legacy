angular.module('opalAdmin.controllers.update.email', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.grid.autoResize']).controller('update.email', function ($scope, $filter, $uibModal, $uibModalInstance, patientCollectionService, $state, Session, ErrorHandler) {

    $scope.cancel = function () {
        $uibModalInstance.dismiss('cancel');
    };

    $scope.new_email = {
        firstTime: null,
        secondTime: null,
        errorMessage: null,
    };
    console.log($scope);

    $scope.validateEmail = function(){
        if($scope.validateInput($scope.new_email.firstTime) && !$scope.new_email.firstTime.match(/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/))
        {
            $scope.new_email.errorMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.EMAIL_NOT_VALID');
        }
        else if($scope.validateInput($scope.new_email.firstTime) && $scope.validateInput($scope.new_email.secondTime) && $scope.new_email.firstTime !== $scope.new_email.secondTime)
        {
            $scope.new_email.errorMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.EMAIL_NOT_SAME');
        }
        else
        {
            $scope.new_email.errorMessage = null;
        }
    }

    var arrValidationUpdateDatabase = [
        $filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.PATIENTSERNUM'),
        $filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.EMAIL'),
    ];

    var arrValidationUpdateFirebase = [
        $filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.USERID'),
        $filter('translate')('PATIENTS.MODIFICATION_TOOLS.VALIDATION.EMAIL'),
    ];

    $scope.updateEmail = function(){
        if($scope.new_email.firstTime !== null && $scope.new_email.secondTime !== null && $scope.new_email.errorMessage === null){
            $.ajax({
                type: "POST",
                url: "patient/update/external-email",
                data: {
                    uid: $scope.puid,
                    email: $scope.new_email.firstTime,
                },
                success: function () {
                    $scope.updateEmailInDatabase();
                },
                error: function (err) {
                    ErrorHandler.onError(err, $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.ERROR'), arrValidationUpdateFirebase);
                    $scope.setBannerClass('danger');
                    $scope.$parent.bannerMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.ERROR');
                },
                complete: function () {
                    $scope.showBanner();
                    $uibModalInstance.close();
                }
            });
        }
    }

    $scope.updateEmailInDatabase = function(){
        $.ajax({
            type: "POST",
            url: "patient/update/email",
            data: {
                email: $scope.new_email.firstTime,
                PatientSerNum: $scope.psnum,
            },
            success: function () {
                $scope.setBannerClass('success');
                $scope.$parent.bannerMessage = "Successfully update patient email！";
            },
            error: function (err) {
                ErrorHandler.onError(err, $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.ERROR'), arrValidationUpdateDatabase);
                $scope.setBannerClass('danger');
                $scope.$parent.bannerMessage = $filter('translate')('PATIENTS.MODIFICATION_TOOLS.EMAIL.ERROR');
            },
        });
    }

    $scope.validateInput = function(input){
        return (input !== undefined && input !== null && input !== "");
    }

});