// SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
//
// SPDX-License-Identifier: AGPL-3.0-or-later

angular.module('opalAdmin.controllers.study', ['ngAnimate', 'ngSanitize', 'ui.bootstrap', 'ui.grid', 'ui.grid.selection', 'ui.grid.resizeColumns', 'textAngular'])

	.controller('study', function ($scope, $state, $filter, $uibModal, studyCollectionService, Session, uiGridConstants, ErrorHandler, MODULE) {
		$scope.navMenu = Session.retrieveObject('menu');
		$scope.readAccess = ((parseInt(Session.retrieveObject('access')[MODULE.study]) & (1 << 0)) !== 0);
		$scope.writeAccess = ((parseInt(Session.retrieveObject('access')[MODULE.study]) & (1 << 1)) !== 0);
		$scope.deleteAccess = ((parseInt(Session.retrieveObject('access')[MODULE.study]) & (1 << 2)) !== 0);

		// get current user id
		var user = Session.retrieveObject('user');1
		var OAUserId = user.id;

		$scope.goToAddStudy = function () {
			$state.go('study-add');
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
		$scope.filterStudy = function (filterValue) {
			$scope.filterValue = filterValue;
			$scope.gridApi.grid.refresh();
		};

		getstudiesList();

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
				['code', 'title_'+Session.retrieveObject('user').language].forEach(function (field) {
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

		var cellTemplateOperations = '<div style="text-align:center; padding-top: 5px;">';

		if($scope.writeAccess)
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editStudy(row.entity)"<i title="'+$filter('translate')('STUDY.LIST.EDIT')+'" class="fa fa-pencil" aria-hidden="true"></i></a></strong>';
		else
			cellTemplateOperations += '<strong><a href="" ng-click="grid.appScope.editStudy(row.entity)"<i title="'+$filter('translate')('STUDY.LIST.VIEW')+'" class="fa fa-eye" aria-hidden="true"></i></a></strong>';
		cellTemplateOperations += '</div>';
		var cellTemplateName = '<div style="cursor:pointer;" class="ui-grid-cell-contents" ' +
			'ng-click="grid.appScope.editStudy(row.entity)">' +
			'<strong><a href="">{{row.entity.title_'+Session.retrieveObject('user').language+'}}</a></strong></div>';

		// Data binding for main table
		$scope.gridOptions = {
			data: 'studiesList',
			columnDefs: [
				{ field: 'title_'+Session.retrieveObject('user').language, enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.TITLE_2'), cellTemplate: cellTemplateName, sort: {direction: uiGridConstants.ASC, priority: 0}},
				{ field: 'code', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.STUDY_ID'), width: '10%'},
				{ field: 'investigator', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.INVESTIGATOR'), width: '10%'},
				{ field: 'combinedPhone', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.PHONE'), width: '20%'},
				{ field: 'email', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.EMAIL'), width: '15%'},
				{ field: 'startDate', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.START_DATE'), width: '10%'},
				{ field: 'endDate', enableColumnMenu: false, displayName: $filter('translate')('STUDY.LIST.END_DATE'), width: '10%'},
				{ name: $filter('translate')('STUDY.LIST.OPERATIONS'), width: '8%', cellTemplate: cellTemplateOperations, enableColumnMenu: false, enableFiltering: false, sortable: false }
			],
			enableFiltering: true,
			enableSorting: true,
			enableColumnResizing: true,
			onRegisterApi: function (gridApi) {
				$scope.gridApi = gridApi;
				$scope.gridApi.grid.registerRowsProcessor($scope.filterOptions, 300);
			},
		};

		// Initialize object for storing questionnaires
		$scope.studiesList = [];

		function getstudiesList() {
			studyCollectionService.getStudies(OAUserId).then(function (response) {
				$scope.studiesList = response.data;
				angular.forEach($scope.studiesList, function(value){
					if(value.phone && value.phoneExt){ //add dashes for readability
						if(value.phone.length === 10){
							value.phone = value.phone.substr(0,3) + "-" + value.phone.substr(3,3) + "-" + value.phone.substr(6,4);
						}else if(value.phone.length === 11){
							value.phone = "+" + value.phone.substr(0,1) + " " + value.phone.substr(1,3) + "-" + value.phone.substr(4,3) + "-" + value.phone.substr(7,4);
						}else if(value.phone.length === 12){
							value.phone = "+" + value.phone.substr(0,2) + " " + value.phone.substr(2,3) + "-" + value.phone.substr(5,3) + "-" + value.phone.substr(8,4);
						}else{
							value.phone = value.phone;
						}
						value.combinedPhone = value.phone + " ext. " + value.phoneExt;
					}else if(value.phone && !value.phoneExt){ //add dashes for readability
						if(value.phone.length === 10){
							value.phone = value.phone.substr(0,3) + "-" + value.phone.substr(3,3) + "-" + value.phone.substr(6,4);
						}else if(value.phone.length === 11){
							value.phone = "+" + value.phone.substr(0,1) + " " + value.phone.substr(1,3) + "-" + value.phone.substr(4,3) + "-" + value.phone.substr(7,4);
						}else if(value.phone.length === 12){
							value.phone = "+" + value.phone.substr(0,2) + " " + value.phone.substr(2,3) + "-" + value.phone.substr(5,3) + "-" + value.phone.substr(8,4);
						}else{
							value.phone = value.phone;
						}
						value.combinedPhone = value.phone;
					}else{
						value.combinedPhone = "N/A";
					}

				});
			}).catch(function(err) {
				ErrorHandler.onError(err, $filter('translate')('STUDY.LIST.ERROR_PUBLICATION'));
			});
		}

		// Function to edit questionnaire
		$scope.editStudy = function (study) {
			$scope.currentStudy = study;
			var modalInstance = $uibModal.open({ // open modal
				templateUrl: ($scope.writeAccess ? 'templates/study/edit.study.html' : 'templates/study/view.study.html'),
				controller: 'study.edit',
				scope: $scope,
				windowClass: 'customModal',
				backdrop: 'static',
			});

			// After update, refresh the questionnaire list
			modalInstance.result.then(function () {
				getstudiesList();
			});
		};
	});
