angular.module('opalAdmin.controllers.sms', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
        $scope.navMenu = Session.retrieveObject('menu');
        $scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 0)) !== 0);
        $scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 1)) !== 0);
        $scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.alias]) & (1 << 2)) !== 0);

        getSmsAppointmentList();

        var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents">' +
            '<strong><a href="">{{row.entity.appcode}}</a></strong></div>';

        // Banner
        $scope.bannerMessage = "";
        // Function to show page banner
        $scope.showBanner = function () {
            $(".bannerMessage").slideDown(function () {
                setTimeout(function () {
                    $(".bannerMessage").slideUp();
                }, 3000);
            });
        };

        // Function to set banner class
        $scope.setBannerClass = function (classname) {
            // Remove any classes starting with "alert-"
            $(".bannerMessage").removeClass(function (index, css) {
                return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
            });
            // Add class
            $(".bannerMessage").addClass('alert-' + classname);
        };

        $scope.filterSms = function (filterValue) {
            $scope.filterValue = filterValue;
            $scope.gridApi.grid.refresh();

        };

        $scope.filterOptions = function (renderableRows) {
            var matcher = new RegExp($scope.filterValue, 'i');
            renderableRows.forEach(function (row) {
                var match = false;
                ['name_'+Session.retrieveObject('user').language.toUpperCase(), 'type_display'].forEach(function (field) {
                    if (row.entity[field].match(matcher)) {
                        match = true;
                    }
                });
                if (!match) {
                    row.visible = false;
                }
            });

            return renderableRows;
        };

        $scope.gridOptions = {
            data: 'smsAppointment',
            columnDefs: [
                {field:'appcode', displayName: 'Appointment Code',width: '50%',enableColumnMenu: false, cellTemplate: cellTemplateName},
                {field:'resname', displayName: 'Resource Name', width:'50%', enableColumnMenu: false,cellTemplate: cellTemplateName},
            ],
            enableFiltering: true,
            enableColumnResizing: true,
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
            },
        }

        $scope.smsAppoinments = [];
        $scope.smsUpdates = {
            updateList: []
        };

        function buildOperations() {
            $scope.updatedRole = JSON.parse(JSON.stringify($scope.toSubmit));
            var newSubmit = [];
            var noError = true;

            $scope.toSubmit.operations.forEach(function(entry) {

                sup = parseInt((+entry.delete + "" + +entry.write + "" + +entry.read), 2);
                if (sup !== 0 && sup !== 1 && sup !== 3 && sup !== 7)
                    noError = false;

                if(sup !== 0) {
                    newSubmit.push({"moduleId": entry.ID, "access": sup});
                }
            });
            $scope.updatedRole.operations = newSubmit;
            return noError;
        }

        $scope.updateSms = function() {
            if($scope.formReady && $scope.changesDetected) {
                var validResult = buildOperations();
                $.ajax({
                    type: "POST",
                    url: "sms/update/sms",
                    data: $scope.smsUpdates,
                    success: function () {},
                    error: function (err) {
                        ErrorHandler.onError(err, $filter('translate')('SMS.EDIT.ERROR_UPDATE'));
                    },
                    complete: function () {
                        $uibModalInstance.close();
                    }
                });
            }
        };

        function getSmsAppointmentList() {
            smsCollectionService.getsmsAppointments().then(function (response) {
                $scope.smsAppoinments = response.data;
                console.log($scope.smsAppoinments);
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_LIST'));
            });
        }
    });

