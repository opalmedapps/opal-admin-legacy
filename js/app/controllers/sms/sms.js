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

		// Function to filter custom codes
		$scope.filterSms = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		getSmsAppointmentList();
		smsCollectionService.getSmsSpeciality().then(function (response) {
			var Speciality = [];
			response.data.forEach(function (row){
				Speciality.push({value:row.specialityName,label:row.specialityName});
			});
			$scope.gridOptions.columnDefs[3].filter.selectOptions = Speciality;
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_DETAILS'));
		});
		smsCollectionService.getSmsType().then(function (response) {
			var TypeList = [];
			response.data.forEach(function (row){
				TypeList.push({value:row,label:row});
			});
			TypeList.push({value:'-',label:'UNDEFINED'});
			$scope.gridOptions.columnDefs[2].filter.selectOptions = TypeList;
		}).catch(function(err) {
			ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_DETAILS'));
		});

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
		var cellTemplateResourceName = '<div class="ui-grid-cell-contents">' + '{{row.entity.resourceDescription}}&nbsp({{row.entity.resourceCode}})</div>';
		var cellTemplateAppointmentCode = '<div style="cursor:pointer;" class="ui-grid-cell-contents">' +  '<strong><a href=""  ng-click="grid.appScope.editAppointment(row.entity)">{{row.entity.appointmentCode}}</a></strong></div>';

		var checkboxCellTemplate;
		if($scope.writeAccess)
			checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents" ' +  'ng-style = "(row.entity.type != \'-\') ? {cursor:\'pointer\'}:{cursor:\'not-allowed\'}" >' + '<input style="margin: 4px;" type="checkbox" ng-checked="grid.appScope.updateVal(row.entity.active)" ' + 'ng-disabled="!(row.entity.type != \'-\')" ng-click="grid.appScope.checkSmsUpdate(row.entity)" ' + 'ng-model="row.entity.active"></div>';
		else
			checkboxCellTemplate = '<div style="text-align: center;" class="ui-grid-cell-contents">' +
				'<i ng-class="row.entity.active == 1 ? \'fa-check text-success\' : \'fa-times text-danger\'" class="fa"></i>' +
				'</div>';

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';
		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editAppointment(row.entity)"<i title="'+$filter('translate')('SMS.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong></div>';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editAppointment(row.entity)"<i title="'+$filter('translate')('SMS.LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong></div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'smsAppointments',
			columnDefs: [
				{field:'appointmentCode', displayName: $filter('translate')('SMS.LIST.APPOINTMENT_CODE'),width: '25%',enableColumnMenu: false, cellTemplate: cellTemplateAppointmentCode},
				{field:'resourceDescription', displayName:  $filter('translate')('SMS.LIST.RESOURCE_NAME'), width:'25%', enableColumnMenu: false,cellTemplate: cellTemplateResourceName},
				{
					field: 'type', displayName: $filter('translate')('SMS.LIST.TYPE'), width: '14%', enableColumnMenu: false, filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: []
					}
				},
				{
					field: 'speciality', displayName:  $filter('translate')('SMS.LIST.SPECIALITY'), width: '14%', enableColumnMenu: false, filter: {
						type: uiGridConstants.filter.SELECT,
						selectOptions: []
					}
				},
				{ field: 'active', displayName: $filter('translate')('SMS.LIST.ENABLE'), enableColumnMenu: false, width: '13%',
					cellTemplate: checkboxCellTemplate, enableFiltering: false
				},
				{ name: $filter('translate')('SMS.LIST.OPERATIONS'), cellTemplate: cellTemplateOperations, enableColumnMenu: false, enableFiltering: false, sortable: false }
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
		$scope.smsUpdates = [];

		var arrValidationInsert = [
			$filter('translate')('SMS.VALIDATION.APPOINTMENT_ID'),
			$filter('translate')('SMS.VALIDATION.ACTIVE'),
			$filter('translate')('SMS.VALIDATION.TYPE'),
		];

		//Functions to get sms appointment list from database.
		function getSmsAppointmentList() {
			smsCollectionService.getSmsAppointments().then(function (response) {
				response.data.forEach(function (row){
					switch (row.type){
					case null:
						row.type = '-';
					}
					row.modified = 0;

				});
				$scope.smsAppointments = response.data;
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('SMS.LIST.ERROR_LIST'));
			});
		}

		//Function to update checkbox value
		$scope.updateVal = function(value){
			return (parseInt(value) === 1);
		};

		//Function to change activation state of an sms appointment
		$scope.checkSmsUpdate = function (sms) {

			$scope.changesMade = true;
			sms.active = !sms.active;
			// flag parameter that changed
			sms.modified = 1;
		};

		// Function to submit changes
		$scope.submitUpdate = function () {
			if ($scope.changesMade && $scope.writeAccess) {
				angular.forEach($scope.smsAppointments, function (sms) {
					if (sms.modified) {
						if(sms.type !== "-")
							$scope.smsUpdates.push({
								id: sms.id,
								active: sms.active ? 1 : 0,
								type: sms.type
							});
					}
				});

				// Submit form
				$.ajax({
					type: "POST",
					url: "sms/update/activation",
					data: {"data": $scope.smsUpdates},
					success: function () {
						getSmsAppointmentList();
						$scope.setBannerClass('success');
						$scope.bannerMessage = $filter('translate')('SMS.LIST.SUCCESS');
						$scope.showBanner();
						$scope.changesMade = false;
						$scope.smsUpdates = [];
					},
					error: function(err) {
						err.responseText = JSON.parse(err.responseText);
						ErrorHandler.onError(err,$filter('translate')('SMS.LIST.ERROR'),arrValidationInsert);
					}
				});
			}
		};

		//Function to edit appointment
		$scope.editAppointment = function(appointment){
			$scope.currentAppointment = appointment;
			var modalInstance = $uibModal.open({
				templateUrl: ($scope.writeAccess ? 'templates/sms/edit.sms.html' : 'templates/sms/view.sms.html'),
				controller: 'sms.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			modalInstance.result.then(function () {
				getSmsAppointmentList();
			});
		};
	});

