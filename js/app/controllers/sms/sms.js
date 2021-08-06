angular.module('opalAdmin.controllers.sms', ['ngAnimate', 'ui.bootstrap', 'ui.grid', 'ui.grid.resizeColumns', 'ui.bootstrap.materialPicker']).


    /******************************************************************************
     * SMS Page controller
     *******************************************************************************/
    controller('sms', function ($scope, $uibModal, $filter, $state, smsCollectionService, uiGridConstants, Session, ErrorHandler, MODULE) {
        $scope.navMenu = Session.retrieveObject('menu');
        $scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 0)) !== 0);
        $scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 1)) !== 0);
        $scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.sms]) & (1 << 2)) !== 0);

        $scope.goToMessage = function(){
            $state.go('sms/message');
        }

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

        $scope.filterSms = function (filterValue) {
            $scope.filterValue = filterValue;
            $scope.gridApi.grid.refresh();
        };

        getSmsAppointmentList();
        getSmsSpecialityList();
        getSmsTypeList();

        // Function to set banner class
        $scope.setBannerClass = function (classname) {
            // Remove any classes starting with "alert-"
            $(".bannerMessage").removeClass(function (index, css) {
                return (css.match(/(^|\s)alert-\S+/g) || []).join(' ');
            });
            // Add class
            $(".bannerMessage").addClass('alert-' + classname);
        };

        // Filter
        // search text-box param
        $scope.filterOptions = function (renderableRows) {
            var matcher = new RegExp($scope.filterValue, 'i');
            renderableRows.forEach(function (row) {
                var match = false;
                ['appointmentCode','resourceDescription','type',"speciality"].forEach(function (field) {
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

        //Cell Templates
        var cellTemplateResourceName = '<div style="cursor:pointer;" class="ui-grid-cell-contents">' +
            '<a href=""  ng-click="grid.appScope.editAppointment(row.entity)"><strong>{{row.entity.resourceDescription}}</strong>&nbsp&nbsp&nbsp({{row.entity.resourceCode}})</a></div>';
        var cellTemplateAppointmentCode = '<div style="cursor:pointer;" class="ui-grid-cell-contents">' +
            '<strong><a href=""  ng-click="grid.appScope.editAppointment(row.entity)">{{row.entity.appointmentCode}}</a></strong></div>';

        var checkboxCellTemplate;
        if($scope.writeAccess)
            checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents" ' +
                'ng-style = "(row.entity.type != \'-\') ? {cursor:\'pointer\'}:{cursor:\'not-allowed\'}" >' +
                '<input style="margin: 4px;" type="checkbox" ng-checked="grid.appScope.updateVal(row.entity.active)" ' +
                'ng-disabled="!(row.entity.type != \'-\')" ng-click="grid.appScope.checkSmsUpdate(row.entity)" ' +
                'ng-model="row.entity.active"></div>';
        else
            checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents"><i ng-class="row.entity.active == 1 ? \'Active\' : \'Disabled\'" class="fa"></i></div>';

        $scope.gridOptions = {
            data: 'smsAppointments',
            columnDefs: [
                {field:'appointmentCode', displayName: $filter('translate')('SMS.LIST.APPOINTMENT_CODE'),width: '25%',enableColumnMenu: false, cellTemplate: cellTemplateAppointmentCode},
                {field:'resourceDescription', displayName:  $filter('translate')('SMS.LIST.RESOURCE_NAME'), width:'30%', enableColumnMenu: false,cellTemplate: cellTemplateResourceName},
                {
                    field: 'type', displayName: $filter('translate')('SMS.LIST.TYPE'), width: '15%', enableColumnMenu: false, filter: {
                        type: uiGridConstants.filter.SELECT,
                        selectOptions: []
                    }
                },
                {
                    field: 'speciality', displayName:  $filter('translate')('SMS.LIST.SPECIALITY'), width: '15%', enableColumnMenu: false, filter: {
                        type: uiGridConstants.filter.SELECT,
                        selectOptions: []
                    }
                },
                { field: 'active', displayName: $filter('translate')('SMS.LIST.DISABLE/ENABLE'), enableColumnMenu: false, width: '15%',
                    cellTemplate: checkboxCellTemplate, enableFiltering: false },
            ],
            enableFiltering: true,
            enableColumnResizing: true,
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                $scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
            },
        };

        $scope.changesMade = false;
        $scope.smsAppointments = [];
        $scope.smsUpdates = {
            updateList: []
        };

        var arrValidationInsert = [
            $filter('translate')('SMS.VALIDATION.APPOINTMENT_ID'),
            $filter('translate')('SMS.VALIDATION.ACTIVE'),
            $filter('translate')('SMS.VALIDATION.TYPE'),
        ];

        //Functions to get information from database.
        function getSmsTypeList(){
            smsCollectionService.getSmsType().then(function (response) {
                var TypeList = []
                response.data.forEach(function (row){
                    TypeList.push({value:row,label:row})
                });
                TypeList.push({value:'-',label:'UNDEFINED'});
                $scope.gridOptions.columnDefs[2].filter.selectOptions = TypeList;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_DETAILS'));
            });
        }

        function getSmsSpecialityList(){
            smsCollectionService.getSmsSpeciality().then(function (response) {
                var Speciality = []
                response.data.forEach(function (row){
                    Speciality.push({value:row.specialityName,label:row.specialityName})
                });
                $scope.gridOptions.columnDefs[3].filter.selectOptions = Speciality;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_DETAILS'));
            });
        }

        function getSmsAppointmentList() {
            smsCollectionService.getSmsAppointments().then(function (response) {
                response.data.forEach(function (row){
                    switch (row.type){
                        case null:
                            row.type = '-';
                    }
                    row.modified = 0;

                })
                $scope.smsAppointments = response.data;
            }).catch(function(err) {
                ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_LIST'));
            });
        }

        $scope.updateVal = function(value){
            return (parseInt(value) === 1);
        }

        $scope.checkSmsUpdate = function (sms) {

            $scope.changesMade = true;
            sms.active = parseInt(sms.active);
            // If the "Update" column has been checked
            if (sms.active) {
                sms.active = 0; // set update to "true"
            }

            // Else the "Update" column was unchecked
            else {
                sms.active = 1; // set update to "false"
            }
            // flag parameter that changed
            sms.modified = 1;
        };

        // Submit changes
        $scope.submitUpdate = function () {
            if ($scope.changesMade && $scope.writeAccess) {
                angular.forEach($scope.smsAppointments, function (sms) {
                    if (sms.modified) {
                        var type = sms.type;
                        if(sms.type == "-") type = 0;
                        $scope.smsUpdates.updateList.push({
                            id: sms.id,
                            active: sms.active,
                            type: type
                        });
                    }
                });

                // Submit form
                $.ajax({
                    type: "POST",
                    url: "sms/update/activation",
                    data: $scope.smsUpdates,
                    success: function (response) {
                        console.log(response);
                        getSmsAppointmentList();
                        // Show success or failure depending on response
                        $scope.setBannerClass('success');
                        $scope.bannerMessage = $filter('translate')('SMS.LIST.SUCCESS');
                        $scope.showBanner();
                        $scope.changesMade = false;
                        $scope.smsUpdates.updateList = [];
                    },
                    error: function(err) {
                        err.responseText = JSON.parse(err.responseText);
                        ErrorHandler.onError(err,$filter('translate')('SMS.LIST.ERROR'),arrValidationInsert);
                    }
                });
            }
        };

        //Open editor modal
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

    });

