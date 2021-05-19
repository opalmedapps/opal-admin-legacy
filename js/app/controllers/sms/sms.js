angular.module('opalAdmin.controllers.sms', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
        $scope.navMenu = Session.retrieveObject('menu');
        $scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 0)) !== 0);
        $scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 1)) !== 0);
        $scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 2)) !== 0);

        getSmsAppointmentList();
        $scope.changesMade = false;
        var cellTemplateResourceName = '<div style="cursor:pointer;" class="ui-grid-cell-contents">' +
            '<strong><a href=""  ng-click="grid.appScope.editAppointment(row.entity)">{{row.entity.resname}}</a></strong></div>';
        var cellTemplateAppointmentCode = '<div style="cursor:pointer;" class="ui-grid-cell-contents">' +
            '<strong><a href=""  ng-click="grid.appScope.editAppointment(row.entity)">{{row.entity.appcode}}</a></strong></div>';

        var checkboxCellTemplate;
        if($scope.writeAccess)
            checkboxCellTemplate = '<div ng-if= "row.entity.apptype != \'UNDEFINED\'" style="text-align: center; cursor: pointer;" ' +
                'ng-click="grid.appScope.checkSmsUpdate(row.entity)" class="ui-grid-cell-contents">' +
                '<input style="margin: 4px;" type="checkbox" ng-checked="grid.appScope.updateVal(row.entity.state)" ' +
                'ng-model="row.entity.state"></div>' +
                '<div ng-if= "!(row.entity.apptype != \'UNDEFINED\')" style="text-align: center;" class="ui-grid-cell-contents">{{\'SMS.LIST.DISABLE\'|translate}}</div>';
        else
            checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents"><i ng-class="row.entity.state == 1 ? \'Active\' : \'Disabled\'" class="fa"></i></div>';

        $scope.updateVal = function(value){
            return (parseInt(value) === 1);
        }

        $scope.checkSmsUpdate = function (sms) {

            $scope.changesMade = true;
            sms.state = parseInt(sms.state);
            // If the "Update" column has been checked
            if (sms.state) {
                sms.state = 0; // set update to "true"
            }

            // Else the "Update" column was unchecked
            else {
                sms.state = 1; // set update to "false"
            }
            // flag parameter that changed
            sms.modified = 1;
        };

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
                ['appcode','resname','apptype',].forEach(function (field) {
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
            data: 'smsAppointments',
            columnDefs: [
                {field:'appcode', displayName: $filter('translate')('SMS.LIST.APPOINTMENT_CODE'),width: '30%',enableColumnMenu: false, cellTemplate: cellTemplateAppointmentCode},
                {
                    field: 'displayType', displayName: $filter('translate')('SMS.LIST.TYPE'), width: '15%', enableColumnMenu: false, filter: {
                        type: uiGridConstants.filter.SELECT,
                        selectOptions: [{ value: 'GENERAL', label: 'GENERAL'}, { value: 'RADONC', label: 'RADONC' },
                            { value: 'TELEMED', label: 'TELEMED' },{value:'TEST_CENTRE',label:'TEST_CENTRE'},{value:'UNDEFINED',label:'UNDEFINED'}]
                    }
                },
                {
                    field: 'displaySpeciality', displayName:  $filter('translate')('SMS.LIST.SPECIALITY'), width: '15%', enableColumnMenu: false, filter: {
                        type: uiGridConstants.filter.SELECT,
                        selectOptions: [{ value: 'Oncology', label: 'Oncology'}]
                    }
                },
                {field:'resname', displayName:  $filter('translate')('SMS.LIST.RESOURCE_NAME'), width:'25%', enableColumnMenu: false,cellTemplate: cellTemplateResourceName},
                { field: 'state', displayName: $filter('translate')('SMS.LIST.DISABLE/ENABLE'), enableColumnMenu: false, width: '15%',
                    cellTemplate: checkboxCellTemplate, enableFiltering: false },
            ],
            enableFiltering: true,
            enableColumnResizing: true,
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
            },
        };

        $scope.smsAppointments = [];
        $scope.smsUpdates = {
            updateList: []
        };

        $scope.goToMessage = function(){
            $state.go('sms/message');
        }

        $scope.submitUpdate = function () {
            if ($scope.changesMade && $scope.writeAccess) {
                angular.forEach($scope.smsAppointments, function (sms) {
                    if (sms.modified) {
                        $scope.smsUpdates.updateList.push({
                            ressernum: sms.ressernum,
                            appcode: sms.code,
                            state: sms.state
                        });
                    }
                });
                // Log who updated alias
                //var currentUser = Session.retrieveObject('user');
                //$scope.smsUpdates.user = currentUser;
                console.log($scope.smsUpdates);
                // Submit form
                $.ajax({
                    type: "POST",
                    url: "sms/update/activation",
                    data: $scope.smsUpdates,
                    success: function (response) {
                        getSmsAppointmentList();
                        response = JSON.parse(response);
                        // Show success or failure depending on response
                        if (response) {
                            $scope.setBannerClass('success');
                            $scope.bannerMessage = $filter('translate')('SMS.LIST.SUCCESS');
                            $scope.showBanner();
                        }
                        else {
                            ErrorHandler.onError(response, $filter('translate')('SMS.LIST.ERROR'));
                        }
                        $scope.changesMade = false;
                        $scope.smsUpdates.updateList = [];
                    },
                    error: function(err) {
                        ErrorHandler.onError(err,$filter('translate')('SMS.LIST.ERROR'));
                    }
                });
            }
        };

        $scope.editAppointment = function(appointment){
            if($scope.writeAccess){
                $scope.currentAppointment = appointment;
                var modalInstance = $uibModal.open({ // open modal
                    templateUrl: 'templates/sms/edit.sms.html',
                    controller: 'sms.edit',
                    scope: $scope,
                    windowClass: 'customModal',
                    backdrop: 'static',
                });

                modalInstance.result.then(function () {
                    getSmsAppointmentList();
                });
            }
        };

        function getSmsAppointmentList() {
            smsCollectionService.getSmsAppointments().then(function (response) {
                response.data.forEach(function (row){
                    switch (row.apptype){
                        case null:
                            row.apptype = 'UNDEFINED';
                    }
                    row.displayType = $filter('translate')('SMS.LIST.'+row.apptype);
                    row.displaySpeciality = $filter('translate')('SMS.LIST.'+row.spec);
                    row.modified = 0;
                })
                $scope.smsAppointments = response.data;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_LIST'));
            });
        }
    });

